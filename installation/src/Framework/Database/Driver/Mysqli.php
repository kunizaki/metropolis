<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Driver;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Database\AbstractDriver;
use Akeeba\BRS\Framework\Database\FixMySQLTrait;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * MySQL driver class using PHP's mysqli database driver.
 *
 * @since  10.0
 */
final class Mysqli extends AbstractDriver
{
	use FixMySQLTrait;
	use CommonMySQLTrait;

	/** @inheritdoc */
	public static $dbtech = 'mysql';

	/** @inheritdoc */
	protected static $dbMinimum = '5.0.4';

	/** @inheritdoc */
	public $name = 'mysqli';

	/** @inheritdoc */
	protected $nameQuote = '`';

	/** @inheritdoc */
	protected $nullDate = '0000-00-00 00:00:00';

	/**
	 * An internal flag for reconnection attempts.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private $isReconnecting = false;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container, array $options = [])
	{
		// Get some basic values from the options.
		$options['host']     = $options['host'] ?? 'localhost';
		$options['user']     = $options['user'] ?? 'root';
		$options['password'] = $options['password'] ?? '';
		$options['database'] = $options['database'] ?? '';
		$options['select']   = !isset($options['select']) || $options['select'];
		$options['port']     = isset($options['port']) ? (int) $options['port'] : null;
		$options['socket']   = $options['socket'] ?? null;
		$options['utf8mb4']  = $options['utf8mb4'] ?? true;

		$options['ssl'] = $options['ssl'] ?? [];
		$options['ssl'] = is_array($options['ssl']) ? $options['ssl'] : [];

		foreach (
			[
				['enable', 'dbencryption', false],
				['cipher', 'dbsslcipher', null],
				['ca', 'dbsslca', null],
				['key', 'dbsslkey', null],
				['cert', 'dbsslcert', null],
				['verify_server_cert', 'dbsslverifyservercert', false],
			] as $tuples
		)
		{
			[$sslKey, $optionsKey, $default] = $tuples;
			$sslValue                = $options['ssl'][$sslKey] ?? null;
			$optionsValue            = $options[$optionsKey] ?? null;
			$value                   = is_null($sslValue) ? $optionsValue : $sslValue;
			$options['ssl'][$sslKey] = is_null($value) ? $default : $value;;
		}

		$options['ssl']['enable']             = $options['ssl']['enable'] ?? false ?: false;
		$options['ssl']['cipher']             = $options['ssl']['cipher'] ?? null ?: null;
		$options['ssl']['ca']                 = $options['ssl']['ca'] ?? null ?: null;
		$options['ssl']['capath']             = $options['ssl']['capath'] ?? null ?: null;
		$options['ssl']['key']                = $options['ssl']['key'] ?? null ?: null;
		$options['ssl']['cert']               = $options['ssl']['cert'] ?? null ?: null;
		$options['ssl']['verify_server_cert'] = $options['ssl']['verify_server_cert'] ?? null ?: false;

		// Figure out if a port is included in the host name
		$this->fixHostnamePortSocket($options['host'], $options['port'], $options['socket'], $options['ssl']['enable']);

		// Finalize initialisation.
		parent::__construct($container, $options);
	}

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  boolean  True on success, false otherwise.
	 * @since   10.0
	 */
	public static function isSupported()
	{
		return function_exists('mysqli_connect');
	}

	/**
	 * Destructor.
	 *
	 * @since   10.0
	 */
	public function __destruct()
	{
		if (is_callable($this->connection, 'close'))
		{
			$this->connection->close();
		}
	}

	/** @inheritdoc */
	public function connect(): void
	{
		if ($this->connection)
		{
			return;
		}

		// Make sure the MySQLi extension for PHP is installed and enabled.
		if (!function_exists('mysqli_connect'))
		{
			throw new RuntimeException('The MySQL adapter mysqli is not available');
		}

		$this->connection = mysqli_init() ?: null;
		$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

		$connectionFlags = 0;

		// For SSL/TLS connection encryption.
		if ($this->options['ssl'] !== [] && $this->options['ssl']['enable'] === true)
		{
			$connectionFlags = $connectionFlags | MYSQLI_CLIENT_SSL;

			// Verify server certificate is only available in PHP 5.6.16+. See https://www.php.net/ChangeLog-5.php#5.6.16
			if (isset($this->options['ssl']['verify_server_cert']))
			{
				// New constants in PHP 5.6.16+. See https://www.php.net/ChangeLog-5.php#5.6.16
				if ($this->options['ssl']['verify_server_cert'] === true
				    && defined(
					    'MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT'
				    ))
				{
					$connectionFlags = $connectionFlags | MYSQLI_CLIENT_SSL_VERIFY_SERVER_CERT;
				}
				elseif ($this->options['ssl']['verify_server_cert'] === false
				        && defined(
					        'MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT'
				        ))
				{
					$connectionFlags = $connectionFlags | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
				}
				elseif (defined('MYSQLI_OPT_SSL_VERIFY_SERVER_CERT'))
				{
					$this->connection->options(
						MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, $this->options['ssl']['verify_server_cert']
					);
				}
			}

			// Add SSL/TLS options only if changed.
			$this->connection->ssl_set(
				$this->options['ssl']['key'] ?? null ?: null,
				$this->options['ssl']['cert'] ?? null ?: null,
				$this->options['ssl']['ca'] ?? null ?: null,
				$this->options['ssl']['capath'] ?? null ?: null,
				$this->options['ssl']['cipher'] ?? null ?: null
			);
		}

		// Attempt to connect to the server, use error suppression to silence warnings and allow us to throw an Exception separately.
		$connected = !empty($this->connection)
		             && @$this->connection->real_connect(
				$this->options['host'],
				$this->options['user'],
				$this->options['password'] ?: null,
				null,
				$this->options['port'] ?: 3306,
				$this->options['socket'] ?: null,
				$connectionFlags
			);

		// Attempt to connect to the server.
		if (!$connected)
		{
			throw new RuntimeException('Could not connect to MySQL.');
		}

		// Set sql_mode to non_strict mode
		mysqli_query($this->connection, "SET @@SESSION.sql_mode = '';");

		// If auto-select is enabled select the given database.
		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		// Set charactersets (needed for MySQL 4.1.2+).
		$this->setUTF();
	}

	/** @inheritdoc */
	public function connected(): bool
	{
		if (!is_object($this->connection))
		{
			return false;
		}

		try
		{
			$cursor = @mysqli_query($this->connection, 'SELECT 1');

			if (!$cursor)
			{
				return false;
			}

			mysqli_free_result($cursor);

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/** @inheritdoc */
	public function disconnect(): void
	{
		// Close the connection.
		if (is_callable($this->connection, 'close'))
		{
			mysqli_close($this->connection);
		}

		$this->connection = null;
	}

	/** @inheritdoc */
	public function escape($text, bool $extra = false): string
	{
		$this->connect();

		if (is_null($text))
		{
			return 'NULL';
		}

		$result = mysqli_real_escape_string($this->getConnection(), $text);

		if ($extra)
		{
			$result = addcslashes($result, '%_');
		}

		return $result;
	}

	/** @inheritdoc */
	public function execute()
	{
		$this->connect();

		if (!is_object($this->connection))
		{
			throw new RuntimeException($this->errorMsg, $this->errorNum);
		}

		// Take a local copy so that we don't modify the original query and cause issues later
		$sql = $this->replacePrefix((string) $this->sql);
		if ($this->limit > 0 || $this->offset > 0)
		{
			$sql .= ' LIMIT ' . $this->offset . ', ' . $this->limit;
		}

		// Reset the error values.
		$this->errorNum = 0;
		$this->errorMsg = '';

		// Execute the query. Error suppression is used here to prevent warnings/notices that the connection has been lost.
		$this->cursor = @mysqli_query($this->connection, $sql);

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$this->errorNum = mysqli_errno($this->connection);
			$this->errorMsg = mysqli_error($this->connection) . "\n SQL=" . $sql;

			// Check if the server was disconnected.
			if (!$this->connected() && !$this->isReconnecting)
			{
				$this->isReconnecting = true;

				try
				{
					// Attempt to reconnect.
					$this->connection = null;
					$this->connect();
				}
					// If connect fails, ignore that exception and throw the normal exception.
				catch (RuntimeException $e)
				{
					$this->errorNum = mysqli_errno($this->connection);
					$this->errorMsg = mysqli_error($this->connection) . ' SQL=' . $sql;

					unset($sql);

					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				unset($sql);

				$result               = $this->execute();
				$this->isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				unset($sql);
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		unset($sql);

		return $this->cursor;
	}

	/** @inheritdoc */
	public function getAffectedRows(): int
	{
		$this->connect();

		return mysqli_affected_rows($this->connection);
	}

	/** @inheritdoc */
	public function getNumRows($cursor = null): int
	{
		return mysqli_num_rows($cursor ?: $this->cursor);
	}

	/** @inheritdoc */
	public function getVersion(): string
	{
		$this->connect();

		return mysqli_get_server_info($this->connection);
	}

	/** @inheritdoc */
	public function insertid(): int
	{
		$this->connect();

		return mysqli_insert_id($this->connection);
	}

	/** @inheritdoc */
	public function select(string $database): bool
	{
		$this->connect();

		if (!$database)
		{
			return false;
		}

		if (!mysqli_select_db($this->connection, $database))
		{
			throw new RuntimeException('Could not connect to database.');
		}

		return true;
	}

	/** @inheritdoc */
	public function setUTF(): bool
	{
		$this->connect();

		$allowUtf8Mb4 = $this->options['utf8mb4'] ?? false;
		$charset      = ($allowUtf8Mb4 && $this->supportsUtf8mb4()) ? 'utf8mb4' : 'utf8';

		$result = @$this->connection->set_charset($charset);

		if (!$result)
		{
			$this->supportsUTF8MB4 = false;
			$result                = @$this->connection->set_charset('utf8');
		}

		return $result;
	}

	/** @inheritdoc */
	public function supportsUtf8mb4(): bool
	{
		if (is_null($this->supportsUTF8MB4))
		{
			$this->supportsUTF8MB4 = $this->serverClaimsUtf8();
		}

		return $this->supportsUTF8MB4;
	}

	/** @inheritdoc */
	public function transactionCommit(): void
	{
		$this->connect();

		$this->setQuery('COMMIT');
		$this->execute();
	}

	/** @inheritdoc */
	public function transactionRollback(): void
	{
		$this->connect();

		$this->setQuery('ROLLBACK');
		$this->execute();
	}

	/** @inheritdoc */
	public function transactionStart(): void
	{
		$this->connect();

		$this->setQuery('START TRANSACTION');
		$this->execute();
	}

	/**
	 * Is this a MariaDB database?
	 *
	 * @return bool
	 * @since  10.0
	 */
	public function isMariaDB(): bool
	{
		return stripos($this->connection->server_info, 'mariadb') !== false;
	}

	/** @inheritdoc */
	protected function fetchArray($cursor = null)
	{
		return mysqli_fetch_row($cursor ?: $this->cursor);
	}

	/** @inheritdoc */
	protected function fetchAssoc($cursor = null)
	{
		return mysqli_fetch_assoc($cursor ?: $this->cursor);
	}

	/** @inheritdoc */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		return mysqli_fetch_object($cursor ?: $this->cursor, $class);
	}

	/** @inheritdoc */
	protected function freeResult($cursor = null)
	{
		mysqli_free_result($cursor ?: $this->cursor);
	}

	/**
	 * Does the server claim to be UTF-8 compatible?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function serverClaimsUtf8(): bool
	{
		if (is_null($this->connection))
		{
			return true;
		}

		$mariadb        = stripos($this->connection->server_info, 'mariadb') !== false;
		$client_version = $this->connection->client_info;
		$server_version = $this->getVersion();

		if (version_compare($server_version, '5.5.3', '<'))
		{
			return false;
		}

		if ($mariadb && version_compare($server_version, '10.0.0', '<'))
		{
			return false;
		}

		if (strpos($client_version, 'mysqlnd') !== false)
		{
			$client_version = preg_replace('/^\D+([\d.]+).*/', '$1', $client_version);

			return version_compare($client_version, '5.0.9', '>=');
		}

		return version_compare($client_version, '5.5.3', '>=');
	}
}