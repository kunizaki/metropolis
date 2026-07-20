<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Database\Metadata\Column;
use Akeeba\BRS\Framework\Database\Metadata\Database;
use Akeeba\BRS\Framework\Database\Metadata\Table;
use DirectoryIterator;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;
use stdClass;

/**
 * Abstract database driver class.
 *
 * @method  q(string $text, bool $escape = true)
 * @method  qn($name, $as = null)
 * @method  nq($name, $as = null)
 *
 * @since  10.0
 */
#[\AllowDynamicProperties]
abstract class AbstractDriver implements ContainerAwareInterface, DatabaseDriverInterface
{
	use ContainerAwareTrait;

	/**
	 * The database technology family supported, e.g. mysqli
	 *
	 * @var   string
	 * @since 10.0
	 */
	public static $dbtech = '';

	/**
	 * The minimum supported database version.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected static $dbMinimum;

	/**
	 * The name of the database driver.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $name;

	/**
	 * The name of the database.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $_database;

	/**
	 * The database connection resource.
	 *
	 * @var   mixed
	 * @since 10.0
	 */
	protected $connection;

	/**
	 * The database connection cursor from the last query.
	 *
	 * @var   mixed
	 * @since 10.0
	 */
	protected $cursor;

	/**
	 * The database error message.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $errorMsg;

	/**
	 * The database error number.
	 *
	 * @var   integer
	 * @since 10.0
	 */
	protected $errorNum = 0;

	/**
	 * The affected row limit for the current SQL statement.
	 *
	 * @var   integer
	 * @since 10.0
	 */
	protected $limit = 0;

	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string, the same character is used for both sides of the quoted name. Otherwise, the first
	 * character will be used for the opening quote, and the second for the closing quote.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $nameQuote;

	/**
	 * The null or zero representation of a timestamp for the database driver.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $nullDate;

	/**
	 * The affected row offset to apply for the current SQL statement.
	 *
	 * @var   integer
	 * @since 10.0
	 */
	protected $offset = 0;

	/**
	 * Connection options.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $options;

	/**
	 * The current SQL statement to execute.
	 *
	 * @var   mixed
	 * @since 10.0
	 */
	protected $sql;

	/**
	 * Does this server support UTF8MB4?
	 *
	 * @var   bool|null
	 * @since 10.0
	 */
	protected $supportsUTF8MB4 = null;

	/**
	 * The common database table prefix.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $tablePrefix;

	/**
	 * True if the database engine supports UTF-8 character encoding.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $utf = true;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container, array $options = [])
	{
		$this->setContainer($container);

		// Initialise object variables.
		$this->_database = $options['database'] ?? '';

		$this->tablePrefix = $options['prefix'] ?? 'jos_';
		$this->errorNum    = 0;
		$this->log         = [];
		$this->options     = $options;

		/**
		 * If the utf8mb4 option is set we turn on auto-detection by setting $this->supportsUTF8MB4 to NULL. If the
		 * utf8mb4 option is disabled we set $this->supportsUTF8MB4 to false, disabling the use of UTF8MB4.
		 */
		if (isset($options['utf8mb4']))
		{
			$this->setUtf8Mb4AutoDetection($options['utf8mb4']);
		}
	}

	/**
	 * List supported connectors.
	 *
	 * @param   string|null  $tech  The database technology we are interested in.
	 *
	 * @return  array  An array of available database connectors.
	 * @since   10.0
	 */
	public static function getConnectors(?string $tech = null): array
	{
		$connectors = [];

		// Get an iterator and loop through the driver classes.
		$iterator = new DirectoryIterator(__DIR__ . '/Driver');

		/** @var DirectoryIterator $file */
		foreach ($iterator as $file)
		{
			if (!$file->isFile() || $file->getExtension() !== 'php')
			{
				continue;
			}

			$className = __NAMESPACE__ . '\\Driver\\' . $file->getBasename('.php');

			if (!class_exists($className) || !is_subclass_of($className, AbstractDriver::class)
			    || !method_exists($className, 'isSupported'))
			{
				continue;
			}

			if ($className::isSupported())
			{
				if (!is_null($tech) && $tech != $className::$dbtech)
				{
					continue;
				}

				$connectors[] = $file->getBasename('.php');
			}
		}

		return $connectors;
	}

	/**
	 * Splits a string of multiple queries into an array of individual queries.
	 *
	 * @param   string  $sql  Input SQL string with which to split into individual queries.
	 *
	 * @return  array  The queries from the input string separated into an array.
	 * @since   10.0
	 */
	public static function splitSql(string $sql): array
	{
		$start   = 0;
		$open    = false;
		$char    = '';
		$end     = strlen($sql);
		$queries = [];

		for ($i = 0; $i < $end; $i++)
		{
			$current = substr($sql, $i, 1);

			if (($current == '"' || $current == '\''))
			{
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) == '\\' && $n < $i)
				{
					$n++;
				}

				if ($n % 2 == 0)
				{
					if ($open)
					{
						if ($current == $char)
						{
							$open = false;
							$char = '';
						}
					}
					else
					{
						$open = true;
						$char = $current;
					}
				}
			}

			if (($current == ';' && !$open) || $i == $end - 1)
			{
				$queries[] = substr($sql, $start, ($i - $start + 1));
				$start     = $i + 1;
			}
		}

		return $queries;
	}

	/**
	 * Magic method to provide method alias support for quote() and quoteName().
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  string|null  The aliased method's return value or null.
	 * @since   10.0
	 */
	public function __call(string $method, array $args)
	{
		if (empty($args))
		{
			return null;
		}

		switch ($method)
		{
			case 'q':
				return $this->quote($args[0], isset($args[1]) ? $args[1] : true);
				break;
			case 'qn':
			case 'nq':
				return $this->quoteName($args[0], isset($args[1]) ? $args[1] : null);
				break;
		}

		return null;
	}

	/** @inheritdoc */
	public function alterDbCharacterSet(?string $dbName): void
	{
		if (empty($dbName))
		{
			throw new RuntimeException('Database name must not be null.');
		}

		$this->setQuery($this->getAlterDbCharacterSet($dbName));

		$this->execute();
	}

	/** @inheritdoc */
	abstract public function connect(): void;

	/** @inheritdoc */
	abstract public function connected(): bool;

	/** @inheritdoc */
	public function createDatabase(?object $options, bool $utf = true): void
	{
		if (is_null($options))
		{
			throw new RuntimeException('$options object must not be null.');
		}

		if (empty($options->db_name))
		{
			throw new RuntimeException('$options object must have db_name set.');
		}

		if (empty($options->db_user))
		{
			throw new RuntimeException('$options object must have db_user set.');
		}

		$this->setQuery($this->getCreateDatabaseQuery($options, $utf));

		$this->execute();
	}

	/** @inheritdoc */
	abstract public function disconnect(): void;

	/** @inheritdoc */
	abstract public function dropTable(string $table, bool $ifExists = true): AbstractDriver;

	/** @inheritdoc */
	abstract public function escape($text, bool $extra = false): string;

	/** @inheritdoc */
	abstract public function execute();

	/** @inheritdoc */
	abstract public function getAffectedRows(): int;

	/** @inheritdoc */
	abstract public function getCollation(): ?string;

	/** @inheritdoc */
	public function getConnection()
	{
		return $this->connection;
	}

	/** @inheritdoc */
	public function getDateFormat(): string
	{
		return 'Y-m-d H:i:s';
	}

	/** @inheritdoc */
	public function getMaxPacketSize(): int
	{
		try
		{
			$query   = "SHOW VARIABLES LIKE 'max_allowed_packet'";
			$results = $this->setQuery($query)->loadRowList();
		}
		catch (Exception $e)
		{
			$results = null;
		}

		if (empty($results))
		{
			return 1048576;
		}

		foreach ($results as $result)
		{
			if ($result[0] === 'max_allowed_packet')
			{
				return (int) $result[1];
			}
		}

		return 1048576;
	}

	/** @inheritdoc */
	public function getMinimum(): string
	{
		return static::$dbMinimum;
	}

	/** @inheritdoc */
	public function getNullDate(): ?string
	{
		return $this->nullDate;
	}

	/** @inheritdoc */
	abstract public function getNumRows($cursor = null): int;

	/** @inheritdoc */
	public function getPrefix(): string
	{
		return $this->tablePrefix;
	}

	/** @inheritdoc */
	public function getQuery(bool $new = false)
	{
		if ($new)
		{
			// Derive the class name from the driver.
			$class = __NAMESPACE__ . '\\Query\\' . ucfirst($this->name);

			// Make sure we have a query class for this driver.
			if (!class_exists($class))
			{
				// If it doesn't exist we are at an impasse so throw an exception.
				throw new RuntimeException('Database Query Class not found.');
			}

			return new $class($this);
		}
		else
		{
			return $this->sql;
		}
	}

	/** @inheritdoc */
	abstract public function getTableColumns(string $table, bool $typeOnly = true): array;

	/** @inheritdoc */
	abstract public function getTableCreate($tables): array;

	/** @inheritdoc */
	abstract public function getTableKeys($tables): array;

	/** @inheritdoc */
	abstract public function getTableList(): array;

	/** @inheritdoc */
	abstract public function getVersion(): string;

	/** @inheritdoc */
	public function hasUTFSupport(): bool
	{
		return $this->utf;
	}

	/** @inheritdoc */
	public function insertObject(string $table, object &$object, ?string $key = null): bool
	{
		$fields = [];
		$values = [];

		// Iterate over the object variables to build the query fields and values.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process non-null scalars.
			if (is_array($v) or is_object($v) or $v === null)
			{
				continue;
			}

			// Ignore any internal fields.
			if ($k[0] == '_')
			{
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			$fields[] = $this->quoteName($k);
			$values[] = $this->quote($v);
		}

		// Create the base insert statement.
		$query = $this->getQuery(true);
		$query->insert($this->quoteName($table))
			->columns($fields)
			->values(implode(',', $values));

		// Set the query and execute the insert.
		$this->setQuery($query);

		if (!$this->execute())
		{
			return false;
		}

		// Update the primary key if it exists.
		$id = $this->insertid();

		if ($key && $id)
		{
			$object->$key = $id;
		}

		return true;
	}

	/** @inheritdoc */
	abstract public function insertid(): int;

	/** @inheritdoc */
	public function isMinimumVersion(): bool
	{
		return version_compare($this->getVersion(), static::$dbMinimum) >= 0;
	}

	/** @inheritdoc */
	public function loadAssoc(): ?array
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an associative array.
		if ($array = $this->fetchAssoc($cursor))
		{
			$ret = $array;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/** @inheritdoc */
	public function loadAssocList(?string $key = null, ?string $column = null): ?array
	{
		$this->connect();

		$array = [];

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all rows from the result set.
		while ($row = $this->fetchAssoc($cursor))
		{
			$value = $column ? ($row[$column] ?? $row) : $row;

			if ($key)
			{
				$array[$row[$key]] = $value;
			}
			else
			{
				$array[] = $value;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/** @inheritdoc */
	public function loadColumn(int $offset = 0): ?array
	{
		$this->connect();

		$array = [];

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all of the rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			$array[] = $row[$offset];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/** @inheritdoc */
	public function loadObject(string $class = 'stdClass'): ?object
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an object of type $class.
		if ($object = $this->fetchObject($cursor, $class))
		{
			$ret = $object;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/** @inheritdoc */
	public function loadObjectList(string $key = '', string $class = 'stdClass'): ?array
	{
		$this->connect();

		$array = [];

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all rows from the result set as objects of type $class.
		while ($row = $this->fetchObject($cursor, $class))
		{
			if ($key)
			{
				$array[$row->$key] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/** @inheritdoc */
	public function loadResult()
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row[0];
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/** @inheritdoc */
	public function loadRow(): ?array
	{
		$this->connect();

		$ret = null;

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get the first row from the result set as an array.
		if ($row = $this->fetchArray($cursor))
		{
			$ret = $row;
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $ret;
	}

	/** @inheritdoc */
	public function loadRowList(?string $key = null): ?array
	{
		$this->connect();

		$array = [];

		// Execute the query and get the result set cursor.
		if (!($cursor = $this->execute()))
		{
			return null;
		}

		// Get all rows from the result set as arrays.
		while ($row = $this->fetchArray($cursor))
		{
			if ($key !== null)
			{
				$array[$row[$key]] = $row;
			}
			else
			{
				$array[] = $row;
			}
		}

		// Free up system resources and return.
		$this->freeResult($cursor);

		return $array;
	}

	/** @inheritdoc */
	abstract public function lockTable(string $tableName): AbstractDriver;

	/** @inheritdoc */
	public function quote(string $text, bool $escape = true): string
	{
		return '\'' . ($escape ? $this->escape($text) : $text) . '\'';
	}

	/** @inheritdoc */
	public function quoteName($name, $as = null)
	{
		if (is_string($name))
		{
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';
			if (!is_null($as))
			{
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
			}

			return $quotedName . $quotedAs;
		}

		$fin = [];

		if (is_null($as))
		{
			foreach ($name as $str)
			{
				$fin[] = $this->quoteName($str);
			}
		}
		elseif (is_array($name) && (count($name) == count($as)))
		{
			$count = count($name);

			for ($i = 0; $i < $count; $i++)
			{
				$fin[] = $this->quoteName($name[$i], $as[$i]);
			}
		}

		return $fin;
	}

	/** @inheritdoc */
	abstract public function renameTable(
		string $oldTable, string $newTable, ?string $backup = null, ?string $prefix = null
	): DatabaseDriverInterface;

	/** @inheritdoc */
	public function replacePrefix(string $sql, string $prefix = '#__'): string
	{
		$escaped   = false;
		$startPos  = 0;
		$quoteChar = '';
		$literal   = '';

		$sql = trim($sql);
		$n   = strlen($sql);

		while ($startPos < $n)
		{
			$ip = strpos($sql, $prefix, $startPos);
			if ($ip === false)
			{
				break;
			}

			$j = strpos($sql, "'", $startPos);
			$k = strpos($sql, '"', $startPos);
			if (($k !== false) && (($k < $j) || ($j === false)))
			{
				$quoteChar = '"';
				$j         = $k;
			}
			else
			{
				$quoteChar = "'";
			}

			if ($j === false)
			{
				$j = $n;
			}

			$literal  .= str_replace($prefix, $this->tablePrefix, substr($sql, $startPos, $j - $startPos));
			$startPos = $j;

			$j = $startPos + 1;

			if ($j >= $n)
			{
				break;
			}

			// Quote comes first, find end of quote
			while (true)
			{
				$k       = strpos($sql, $quoteChar, $j);
				$escaped = false;
				if ($k === false)
				{
					break;
				}
				$l = $k - 1;
				while ($l >= 0 && $sql[$l] == '\\')
				{
					$l--;
					$escaped = !$escaped;
				}
				if ($escaped)
				{
					$j = $k + 1;
					continue;
				}
				break;
			}
			if ($k === false)
			{
				// Error in the query - no end quote; ignore it
				break;
			}
			$literal  .= substr($sql, $startPos, $k - $startPos + 1);
			$startPos = $k + 1;
		}
		if ($startPos < $n)
		{
			$literal .= substr($sql, $startPos, $n - $startPos);
		}

		return $literal;
	}

	/** @inheritdoc */
	abstract public function select(string $database): bool;

	/** @inheritdoc */
	public function setQuery($query, int $offset = 0, int $limit = 0)
	{
		$this->sql    = $query;
		$this->limit  = (int) max(0, $limit);
		$this->offset = (int) max(0, $offset);

		return $this;
	}

	/** @inheritdoc */
	abstract public function setUTF(): bool;

	/** @inheritdoc */
	public function setUtf8Mb4AutoDetection(?bool $preference)
	{
		$this->supportsUTF8MB4 = $preference ? null : false;
		$this->supportsUtf8mb4();
		$this->setUTF();
	}

	/** @inheritdoc */
	public function supportsUtf8mb4(): bool
	{
		return false;
	}

	/** @inheritdoc */
	abstract public function transactionCommit(): void;

	/** @inheritdoc */
	abstract public function transactionRollback(): void;

	/** @inheritdoc */
	abstract public function transactionStart(): void;

	/** @inheritdoc */
	public function truncateTable(string $table): void
	{
		$this->setQuery('TRUNCATE TABLE ' . $this->quoteName($table));
		$this->execute();
	}

	/** @inheritdoc */
	abstract public function unlockTables(): AbstractDriver;

	/** @inheritdoc */
	public function updateObject(string $table, object &$object, array $key, bool $nulls = false): bool
	{
		$fields = [];
		$where  = [];

		if (is_string($key))
		{
			$key = [$key];
		}

		if (is_object($key))
		{
			$key = (array) $key;
		}

		// Create the base update statement.
		$statement = 'UPDATE ' . $this->quoteName($table) . ' SET %s WHERE %s';

		// Iterate over the object variables to build the query fields/value pairs.
		foreach (get_object_vars($object) as $k => $v)
		{
			// Only process scalars that are not internal fields.
			if (is_array($v) or is_object($v) or $k[0] == '_')
			{
				continue;
			}

			// Set the primary key to the WHERE clause instead of a field to update.
			if (in_array($k, $key))
			{
				$where[] = $this->quoteName($k) . '=' . $this->quote($v);
				continue;
			}

			// Prepare and sanitize the fields and values for the database query.
			if ($v === null)
			{
				// If the value is null and we want to update nulls then set it.
				if ($nulls)
				{
					$val = 'NULL';
				}
				// If the value is null, and we do not want to update nulls then ignore this field.
				else
				{
					continue;
				}
			}
			// The field is not null, so we prep it for update.
			else
			{
				$val = $this->quote($v);
			}

			// Add the field to be updated.
			$fields[] = $this->quoteName($k) . '=' . $val;
		}

		// We don't have any fields to update.
		if (empty($fields))
		{
			return true;
		}

		// Set the query and execute the update.
		$this->setQuery(sprintf($statement, implode(",", $fields), implode(' AND ', $where)));

		return $this->execute();
	}

	/** @inheritdoc */
	public function getDatabaseMeta(string $database = ''): Database
	{
		// No DB given in input. Get from our internal property.
		if (empty($database))
		{
			$database = $this->getDatabase();
		}

		$query = $this->getQuery(true)
			->select('*')
			->from($this->qn('INFORMATION_SCHEMA.SCHEMATA'))
			->where($this->qn('SCHEMA_NAME') . ' = ' . $this->q($database));

		$result = $this->setQuery($query)->loadAssoc();

		if (is_null($result) || empty($result))
		{
			throw new RuntimeException(
				sprintf(
					"The current database user does not have access to INFORMATION_SCHEMA or cannot query the metadata for database %s",
					$database
				)
			);
		}

		return Database::fromDatabaseResult($result);
	}

	/** @inheritdoc */
	public function getTableMeta(string $tableName): Table
	{
		$database  = $this->getDatabase();
		$tableName = $this->replacePrefix($tableName);

		$query = $this->getQuery(true)
			->select('*')
			->from($this->qn('INFORMATION_SCHEMA.TABLES'))
			->where($this->qn('TABLE_SCHEMA') . ' = ' . $this->q($database))
			->where($this->qn('TABLE_NAME') . ' = ' . $this->q($tableName));

		try
		{
			$result = $this->setQuery($query)->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$result = null;
		}

		if (empty($result))
		{
			$query  = 'SHOW TABLE STATUS WHERE ' . $this->qn('Name') . ' = ' . $this->q($tableName);
			$result = $this->setQuery($query)->loadAssoc();
		}

		if (empty($result))
		{
			throw new RuntimeException(
				sprintf(
					"Table %s does not exist in database %s or the current database user does not have permissions to retrieve its metadata",
					$tableName, $database
				)
			);
		}

		return Table::fromDatabaseResult($result);
	}

	/** @inheritdoc */
	public function getColumnsMeta(string $tableName): array
	{
		$database  = $this->getDatabase();
		$tableName = $this->replacePrefix($tableName);

		$query = $this->getQuery(true)
			->select('*')
			->from($this->qn('INFORMATION_SCHEMA.COLUMNS'))
			->where($this->qn('TABLE_SCHEMA') . ' = ' . $this->q($database))
			->where($this->qn('TABLE_NAME') . ' = ' . $this->q($tableName));

		try
		{
			$result = $this->setQuery($query)->loadAssocList('COLUMN_NAME');
		}
		catch (RuntimeException $e)
		{
			$result = [];
		}

		if (empty($result))
		{
			try
			{
				$query  = 'SHOW FULL COLUMNS FROM ' . $this->qn($tableName);
				$result = $this->setQuery($query)->loadAssocList('Field');
			}
			catch (RuntimeException $e)
			{
				$result = null;
			}
		}

		if (empty($result))
		{
			throw new RuntimeException(
				sprintf(
					"Table %s does not exist in database %s or the current database user does not have permissions to retrieve its column metadata",
					$database, $tableName
				)
			);
		}

		$ret = [];

		foreach ($result as $fieldName => $columnResult)
		{
			$ret[$fieldName] = Column::fromDatabaseResult($columnResult);
		}

		return $ret;
	}

	/** @inheritdoc */
	public function getDatabase(): string
	{
		return $this->_database;
	}

	/**
	 * Fetch a row from the result set cursor as an array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  array|false  Either the next row from the result set or false if there are no more rows.
	 * @since   10.0
	 */
	abstract protected function fetchArray($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an associative array.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  array|false  Either the next row from the result set or false if there are no more rows.
	 * @since   10.0
	 */
	abstract protected function fetchAssoc($cursor = null);

	/**
	 * Method to fetch a row from the result set cursor as an object.
	 *
	 * @param   mixed   $cursor  The optional result set cursor from which to fetch the row.
	 * @param   string  $class   The class name to use for the returned row object.
	 *
	 * @return  object|false  Either the next row from the result set or false if there are no more rows.
	 * @since   10.0
	 */
	abstract protected function fetchObject($cursor = null, string $class = 'stdClass');

	/**
	 * Method to free up the memory used for the result set.
	 *
	 * @param   mixed  $cursor  The optional result set cursor from which to fetch the row.
	 *
	 * @return  void
	 * @since   10.0
	 */
	abstract protected function freeResult($cursor = null);

	/**
	 * Return the query string to alter the database character set.
	 *
	 * @param   string  $dbName  The database name
	 *
	 * @return  string  The query that alter the database query string
	 * @since   10.0
	 */
	protected function getAlterDbCharacterSet(string $dbName): string
	{
		$charset = $this->supportsUtf8mb4() ? 'utf8mb4' : 'utf8';
		$query   = 'ALTER DATABASE ' . $this->quoteName($dbName) . ' CHARACTER SET `' . $charset . '`';

		return $query;
	}

	/**
	 * Return the query string to create a new Database.
	 *
	 * @param   stdClass  $options  Object used to pass user and database name to database driver.
	 *                              This object must have "db_name" and "db_user" set.
	 * @param   bool      $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  string  The query that creates database
	 * @since   10.0
	 */
	protected function getCreateDatabaseQuery(stdClass $options, bool $utf): string
	{
		if ($utf)
		{
			$charset = $this->supportsUtf8mb4() ? 'utf8mb4' : 'utf8';

			return 'CREATE DATABASE ' . $this->quoteName($options->db_name) . ' CHARACTER SET `' . $charset . '`';
		}

		return 'CREATE DATABASE ' . $this->quoteName($options->db_name);
	}

	/**
	 * Quote strings coming from quoteName call.
	 *
	 * @param   array  $strArr  Array of strings coming from quoteName dot-explosion.
	 *
	 * @return  string  Dot-imploded string of quoted parts.
	 * @since   10.0
	 */
	protected function quoteNameStr(array $strArr): string
	{
		$parts = [];
		$q     = $this->nameQuote ?? '';

		foreach ($strArr as $part)
		{
			if (is_null($part))
			{
				continue;
			}

			if (strlen($q) == 1)
			{
				// MySQL: Escape a backtick as two backticks
				if (strpos($part, $q) !== false)
				{
					$part = str_replace($q, $q . $q, $part);
				}

				$parts[] = $q . $part . $q;
			}
			else
			{
				$parts[] = $q[0] . $part . $q[1];
			}
		}

		return implode('.', $parts);
	}
}