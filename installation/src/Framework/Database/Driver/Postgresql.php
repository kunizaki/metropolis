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
use Akeeba\BRS\Framework\Database\DatabaseDriverInterface;
use Exception;
use PDO;
use PDOException;
use PDOStatement;
use ReflectionClass;
use RuntimeException;

/**
 * PostgreSQL driver class using the PHP Data Objects (PDO) driver.
 *
 * @since  10.0
 */
final class Postgresql extends AbstractDriver
{
	/** @inheritdoc  */
	public static $dbtech = 'postgresql';

	/** @inheritdoc  */
	public $name = 'postgresql';

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
	protected $nameQuote = '"';

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
	 * Test to see if the PostgreSQL connector is available.
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

		return in_array('pgsql', PDO::getAvailableDrivers());
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
			throw new RuntimeException('PDO PostgreSQL is not supported on this server.');
		}

		if (!isset($this->charset))
		{
			$this->charset = 'UTF8';
		}

		$this->driverOptions = $this->options['driverOptions'] ?? [];

		$this->options['port'] = ($this->options['port'] ?? 5432) ?: 5432;

		$format = 'pgsql:host=#HOST#;port=#PORT#;dbname=#DBNAME#';

		if ($this->options['socket'] ?? null)
		{
			$format = 'pgsql:host=#SOCKET#;dbname=#DBNAME#';
		}

		$this->charset = $this->options['charset'] ?? $this->charset;

		$replace = ['#HOST#', '#PORT#', '#SOCKET#', '#DBNAME#'];
		$with    = [
			$this->options['host'],
			$this->options['port'],
			$this->options['socket'] ?? '',
			$this->options['database'],
		];

		// Create the connection string:
		$connectionString = str_replace($replace, $with, $format);

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
			throw new RuntimeException('Could not connect to PostgreSQL via PDO: ' . $e->getMessage(), 2);
		}

		$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

		// Set the client encoding
		try
		{
			$this->connection->exec("SET NAMES '{$this->charset}'");
		}
		catch (Exception $e)
		{
		}

		if ($this->options['select'] && !empty($this->options['database']))
		{
			$this->select($this->options['database']);
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
			$sql .= ' LIMIT ' . $this->limit . ' OFFSET ' . $this->offset;
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

		return $this->connection->getAttribute(PDO::ATTR_SERVER_VERSION);
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

	/** @inheritdoc */
	public function replacePrefix(string $sql, string $prefix = '#__'): string
	{
		$sql = trim($sql);

		if ($sql === '' || $prefix === '')
		{
			return $sql;
		}

		$prefixLen = strlen($prefix);
		$n         = strlen($sql);
		$out       = '';
		$i         = 0;
		$dollarTag = null;

		while ($i < $n)
		{
			if ($dollarTag !== null)
			{
				$pos = strpos($sql, $dollarTag, $i);
				if ($pos === false)
				{
					$out .= substr($sql, $i);
					break;
				}

				$out .= substr($sql, $i, $pos - $i + strlen($dollarTag));
				$i    = $pos + strlen($dollarTag);
				$dollarTag = null;
				continue;
			}

			$char = $sql[$i];

			if ($char === "'")
			{
				$out .= $char;
				$i++;

				while ($i < $n)
				{
					$out .= $sql[$i];

					if ($sql[$i] === "'")
					{
						if ($i + 1 < $n && $sql[$i + 1] === "'")
						{
							$out .= $sql[$i + 1];
							$i   += 2;
							continue;
						}

						$i++;
						break;
					}

					$i++;
				}

				continue;
			}

			if ($char === '$')
			{
				$tagEnd = strpos($sql, '$', $i + 1);

				if ($tagEnd !== false)
				{
					$tagInner = substr($sql, $i + 1, $tagEnd - $i - 1);

					if ($tagInner === '' || preg_match('/^[A-Za-z_][A-Za-z_0-9]*$/', $tagInner))
					{
						$dollarTag = substr($sql, $i, $tagEnd - $i + 1);
						$out      .= $dollarTag;
						$i         = $tagEnd + 1;
						continue;
					}
				}
			}

			$pos = strpos($sql, $prefix, $i);
			if ($pos === false)
			{
				$out .= substr($sql, $i);
				break;
			}

			if ($pos > $i)
			{
				$out .= substr($sql, $i, $pos - $i);
			}

			$out .= $this->tablePrefix;
			$i    = $pos + $prefixLen;
		}

		return $out;
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

	/** @inheritdoc */
	public function dropTable(string $table, bool $ifExists = true): AbstractDriver
	{
		$this->connect();

		$query = $this->getQuery(true);

		$this->setQuery('DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $query->quoteName($table));

		$this->execute();

		return $this;
	}

	/** @inheritdoc */
	public function getCollation(): ?string
	{
		$this->connect();

		$this->setQuery("SELECT datcollate FROM pg_database WHERE datname = current_database()");

		return $this->loadResult();
	}

	/** @inheritdoc */
	public function getTableColumns(string $table, bool $typeOnly = true): array
	{
		$this->connect();

		$result = [];

		// Set the query to get the table fields statement.
		$table = $this->replacePrefix($table);
		$this->setQuery(
			"SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
			FROM information_schema.columns
			WHERE table_name = " . $this->quote($table) . "
			ORDER BY ordinal_position"
		);

		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = $field->data_type;
			}
		}
		// If we want the whole field data object add that to the list.
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->column_name] = $field;
			}
		}

		return $result;
	}

	/** @inheritdoc */
	public function getTableCreate($tables): array
	{
		$this->connect();

		$result = [];

		// Sanitize input to an array and iterate over the list.
		settype($tables, 'array');
		foreach ($tables as $table)
		{
			$table = $this->replacePrefix($table);

			// PostgreSQL doesn't have SHOW CREATE TABLE, so we build a representation
			$createSql = "-- Table structure for table " . $this->quoteName($table) . "\n";
			$createSql .= "CREATE TABLE " . $this->quoteName($table) . " (\n";

			// Get columns
			$this->setQuery(
				"SELECT column_name, data_type, character_maximum_length, is_nullable, column_default
				FROM information_schema.columns
				WHERE table_name = " . $this->quote($table) . "
				ORDER BY ordinal_position"
			);
			$columns = $this->loadObjectList();

			$columnDefs = [];
			foreach ($columns as $column)
			{
				$def = "  " . $this->quoteName($column->column_name) . " " . $column->data_type;

				if ($column->character_maximum_length)
				{
					$def .= "(" . $column->character_maximum_length . ")";
				}

				if ($column->is_nullable === 'NO')
				{
					$def .= " NOT NULL";
				}

				if ($column->column_default !== null)
				{
					$def .= " DEFAULT " . $column->column_default;
				}

				$columnDefs[] = $def;
			}

			$createSql .= implode(",\n", $columnDefs);
			$createSql .= "\n);";

			// Populate the result array based on the CREATE statements.
			$result[$table] = $createSql;
		}

		return $result;
	}

	/** @inheritdoc */
	public function getTableKeys($tables): array
	{
		$this->connect();

		$table = $this->replacePrefix($tables);

		// Get the details columns information.
		$this->setQuery(
			"SELECT
				c.conname AS constraint_name,
				c.contype AS constraint_type,
				a.attname AS column_name,
				c.conkey AS key_sequence
			FROM pg_constraint c
			JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = ANY(c.conkey)
			JOIN pg_class t ON t.oid = c.conrelid
			WHERE t.relname = " . $this->quote($table)
		);

		return $this->loadObjectList();
	}

	/** @inheritdoc */
	public function getTableList(): array
	{
		$this->connect();

		// Set the query to get the tables statement.
		$this->setQuery(
			"SELECT tablename FROM pg_tables
			WHERE schemaname = 'public'
			ORDER BY tablename"
		);

		return $this->loadColumn();
	}

	/** @inheritdoc */
	public function lockTable(string $tableName): AbstractDriver
	{
		$this->setQuery('LOCK TABLE ' . $this->quoteName($tableName) . ' IN ACCESS EXCLUSIVE MODE')->execute();

		return $this;
	}

	/** @inheritdoc */
	public function renameTable(
		string $oldTable, string $newTable, ?string $backup = null, ?string $prefix = null
	): DatabaseDriverInterface
	{
		$this->setQuery('ALTER TABLE ' . $this->quoteName($oldTable) . ' RENAME TO ' . $this->quoteName($newTable))->execute();

		return $this;
	}

	/** @inheritdoc */
	public function unlockTables(): AbstractDriver
	{
		// PostgreSQL automatically releases locks at the end of transactions
		// There's no direct equivalent to MySQL's UNLOCK TABLES
		// We can commit the current transaction if one is active
		return $this;
	}

}
