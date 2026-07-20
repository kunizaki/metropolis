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
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use RuntimeException;

/**
 * MySQL driver class using the PHP Data Objects (PDO) driver.
 *
 * @since  10.0
 */
final class Pdomysql extends AbstractDriver
{
	use FixMySQLTrait;
	use CommonMySQLTrait;

	/** @inheritdoc  */
	public static $dbtech = 'mysql';

	/** @inheritdoc  */
	public $name = 'pdomysql';

	/**
	 * Connection character set
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $charset = 'UTF8';

	/**
	 * The db connection resource
	 *
	 * @var   PDO
	 * @since 10.0
	 * */
	protected $connection = null;

	/**
	 * The database connection cursor from the last query.
	 *
	 * @var   PDOStatement
	 * @since 10.0
	 */
	protected $cursor;

	/** @inheritdoc  */
	protected $nameQuote = '`';

	/**
	 * Driver options for PDO
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $driverOptions = [];

	/**
	 * An internal flag for reconnection attempts.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private $isReconnecting = false;

	/**
	 * The default cipher suite for TLS connections.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $defaultCipherSuite = [
		'AES128-GCM-SHA256',
		'AES256-GCM-SHA384',
		'AES128-CBC-SHA256',
		'AES256-CBC-SHA384',
		'DES-CBC3-SHA',
	];

	/**
	 * Test to see if the MySQL connector is available.
	 *
	 * @return  bool  True on success, false otherwise.
	 * @since   10.0
	 */
	public static function isSupported(): bool
	{
		if (!defined('PDO::ATTR_DRIVER_NAME'))
		{
			return false;
		}

		return in_array('mysql', PDO::getAvailableDrivers());
	}

	/**
	 * Destructor.
	 *
	 * @since 10.0
	 */
	public function __destruct()
	{
		if (is_object($this->connection))
		{
			$this->disconnect();
		}
	}

	/**
	 * PDO does not support serialize
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function __sleep()
	{
		$serializedProperties = [];

		$reflect = new ReflectionClass($this);

		// Get properties of the current class
		$properties = $reflect->getProperties();

		foreach ($properties as $property)
		{
			// Do not serialize properties that are PDO
			if (!$property->isStatic() && !($this->{$property->name} instanceof PDO))
			{
				$serializedProperties[] = $property->name;
			}
		}

		return $serializedProperties;
	}

	/**
	 * Wake up after serialization
	 *
	 * @since   10.0
	 */
	public function __wakeup()
	{
		// Get connection back
		$this->__construct($this->getContainer(), $this->options);
	}

	/** @inheritdoc */
	public function connect(): void
	{
		if ($this->connected())
		{
			return;
		}
		else
		{
			$this->disconnect();
		}

		// Make sure the server is compatible
		if (!$this->isSupported())
		{
			throw new RuntimeException('PDO MySQL is not supported on this server.');
		}

		if (!isset($this->charset))
		{
			$this->charset = 'UTF8';
		}

		$this->driverOptions = $this->options['driverOptions'] ?? [];

		$this->options['port'] = ($this->options['port'] ?? 3306) ?: 3306;

		$format = 'mysql:host=#HOST#;port=#PORT#;dbname=#DBNAME#;charset=#CHARSET#';

		if ($this->options['socket'] ?? null)
		{
			$format = 'mysql:socket=#SOCKET#;dbname=#DBNAME#;charset=#CHARSET#';
		}

		$this->charset = $this->options['charset'] ?? $this->charset;

		$replace = ['#HOST#', '#PORT#', '#SOCKET#', '#DBNAME#', '#CHARSET#'];
		$with    = [
			$this->options['host'],
			$this->options['port'],
			$this->options['socket'] ?? '',
			$this->options['database'],
			$this->charset,
		];

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);
		$connectionString = str_replace(';dbname=#DBNAME#', '', $connectionString);

		// For SSL/TLS connection encryption.
		if ($this->options['ssl'] !== [] && $this->options['ssl']['enable'] === true)
		{
			$sslContextIsNull = true;

			// If customised, add cipher suite, ca file path, ca path, private key file path and certificate file path to PDO driver options.
			foreach (['cipher', 'ca', 'capath', 'key', 'cert'] as $value)
			{
				if ($this->options['ssl'][$value] !== null)
				{
					$this->driverOptions[constant(
						'\PDO::MYSQL_ATTR_SSL_' . strtoupper($value)
					)] = $this->options['ssl'][$value];

					$sslContextIsNull = false;
				}
			}

			// PDO, if no cipher, ca, capath, cert and key are set, can't start TLS one-way connection, set a common ciphers suite to force it.
			if ($sslContextIsNull === true)
			{
				$this->driverOptions[PDO::MYSQL_ATTR_SSL_CIPHER] = implode(':', self::$defaultCipherSuite);
			}

			// If customised, for capable systems (PHP 7.0.14+ and 7.1.4+) verify certificate chain and Common Name to driver options.
			if ($this->options['ssl']['verify_server_cert'] !== null
			    && defined(
				    '\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT'
			    ))
			{
				$this->driverOptions[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = $this->options['ssl']['verify_server_cert'];
			}
		}

		// connect to the server
		try
		{
			$this->driverOptions[PDO::ATTR_TIMEOUT] = 5;

			$this->connection = new PDO(
				$connectionString,
				$this->options['user'],
				$this->options['password'],
				$this->driverOptions
			);
		}
		catch (PDOException $e)
		{
			throw new RuntimeException('Could not connect to MySQL via PDO: ' . $e->getMessage(), 2);
		}

		// Reset the SQL mode of the connection
		try
		{
			$this->connection->exec("SET @@SESSION.sql_mode = '';");
		}
			// Ignore any exceptions (incompatible MySQL versions)
		catch (Exception $e)
		{
		}

		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
		}

		if ($this->hasUTFSupport() && $this->charset !== 'utf8mb4')
		{
			$this->charset = 'utf8mb4';
			$this->disconnect();
			$this->connect();
		}

		$this->freeResult();
	}

	/** @inheritdoc */
	public function connected(): bool
	{
		if (!is_object($this->connection))
		{
			return false;
		}

		// Do not try doing a SELECT 1 query here. It seems that the query fails when we are connected to a database
		// which has no tables?!

		return true;
	}

	/** @inheritdoc */
	public function disconnect(): void
	{
		if (is_object($this->cursor))
		{
			$this->cursor->closeCursor();
		}

		$this->connection = null;
	}

	/** @inheritdoc */
	public function escape($text, bool $extra = false): string
	{
		$this->connect();

		if (is_int($text) || is_float($text))
		{
			return $text;
		}

		if (is_null($text))
		{
			return 'NULL';
		}

		$result = substr($this->connection->quote($text), 1, -1);

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

		$this->freeResult();

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
		try
		{
			$this->cursor = $this->connection->query($sql);
		}
		catch (Exception $e)
		{
		}

		// If an error occurred handle it.
		if (!$this->cursor)
		{
			$errorInfo      = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2] . ' SQL=' . $sql;

			unset($sql);

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
					// Throw the normal query exception.
					throw new RuntimeException($this->errorMsg, $this->errorNum);
				}

				// Since we were able to reconnect, run the query again.
				$result               = $this->execute();
				$this->isReconnecting = false;

				return $result;
			}
			// The server was not disconnected.
			else
			{
				// Throw the normal query exception.
				throw new RuntimeException($this->errorMsg, $this->errorNum);
			}
		}

		return $this->cursor;
	}

	/** @inheritdoc */
	public function getAffectedRows(): int
	{
		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/** @inheritdoc */
	public function getNumRows($cursor = null): int
	{
		if ($cursor instanceof PDOStatement)
		{
			return $cursor->rowCount();
		}

		if ($this->cursor instanceof PDOStatement)
		{
			return $this->cursor->rowCount();
		}

		return 0;
	}

	/** @inheritdoc */
	public function getVersion(): string
	{
		if (!is_object($this->connection))
		{
			$this->connect();
		}

		$version = $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);

		if (stripos($version, 'mariadb') !== false)
		{
			// MariaDB: Strip off any leading '5.5.5-', if present
			return preg_replace('/^5\.5\.5-/', '', $version);
		}

		return $version;
	}

	/** @inheritdoc */
	public function hasUTFSupport(): bool
	{
		if (empty($this->connection))
		{
			return true;
		}

		$serverVersion = $this->getVersion();
		$mariadb       = stripos($serverVersion, 'mariadb') !== false;

		// At this point we know the client supports utf8mb4.  Now we must check if the server supports utf8mb4 as well.
		$utf8mb4 = version_compare($serverVersion, '5.5.3', '>=');

		if ($mariadb && version_compare($serverVersion, '10.0.0', '<'))
		{
			$utf8mb4 = false;
		}

		return $utf8mb4;
	}

	/** @inheritdoc */
	public function insertid(): int
	{
		$this->connect();

		// Error suppress this to prevent PDO warning us that the driver doesn't support this operation.
		return @$this->connection->lastInsertId();
	}

	/** @inheritdoc */
	public function select(string $database): bool
	{
		$this->connect();

		try
		{
			$this->connection->exec('USE ' . $this->quoteName($database));
		}
		catch (Exception $e)
		{
			$errorInfo      = $this->connection->errorInfo();
			$this->errorNum = $errorInfo[1];
			$this->errorMsg = $errorInfo[2];

			throw new RuntimeException('Could not connect to database: ' . $this->errorMsg, $this->errorNum);
		}

		return true;
	}

	/** @inheritdoc */
	public function setUTF(): bool
	{
		return true;
	}

	/** @inheritdoc */
	public function transactionCommit(): void
	{
		$this->connection->commit();
	}

	/** @inheritdoc */
	public function transactionRollback(): void
	{
		$this->connection->rollBack();
	}

	/** @inheritdoc */
	public function transactionStart(): void
	{
		$this->connection->beginTransaction();
	}

	/**
	 * Is this a MariaDB database?
	 *
	 * @return bool
	 * @since  10.0
	 */
	public function isMariaDB(): bool
	{
		return stripos($this->getVersion(), 'mariadb') !== false;
	}

	/** @inheritdoc */
	protected function fetchArray($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_NUM);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_NUM);
		}

		return $ret;
	}

	/** @inheritdoc */
	protected function fetchAssoc($cursor = null)
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetch(PDO::FETCH_ASSOC);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetch(PDO::FETCH_ASSOC);
		}

		return $ret;
	}

	/** @inheritdoc */
	protected function fetchObject($cursor = null, $class = 'stdClass')
	{
		$ret = null;

		if (!empty($cursor) && $cursor instanceof PDOStatement)
		{
			$ret = $cursor->fetchObject($class);
		}
		elseif ($this->cursor instanceof PDOStatement)
		{
			$ret = $this->cursor->fetchObject($class);
		}

		return $ret;
	}

	/** @inheritdoc */
	protected function freeResult($cursor = null)
	{
		if ($cursor instanceof PDOStatement)
		{
			$cursor->closeCursor();
		}

		if ($this->cursor instanceof PDOStatement)
		{
			$this->cursor->closeCursor();
			$this->cursor = null;
		}
	}

	// TODO Implement supportsUtf8mb4()

}