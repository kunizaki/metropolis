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

use Akeeba\BRS\Framework\Database\Metadata\Column;
use Akeeba\BRS\Framework\Database\Metadata\Database;
use Akeeba\BRS\Framework\Database\Metadata\Table;
use Psr\Container\ContainerInterface;
use RuntimeException;

/**
 * Interface defining the contract for database drivers.
 *
 * @since 10.0
 */
interface DatabaseDriverInterface
{
	/**
	 * Public constructor for initializing the database driver.
	 *
	 * @param   ContainerInterface  $container  The application container.
	 * @param   array               $options    Connection options.
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $options = []);

	/**
	 * Alter the character set of a database.
	 *
	 * @param   string|null  $dbName  The database name that will be altered
	 *
	 * @since   10.0
	 */
	public function alterDbCharacterSet(?string $dbName): void;

	/**
	 * Connects to the database if needed.
	 *
	 * @return  void
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function connect(): void;

	/**
	 * Determines if the connection to the server is active.
	 *
	 * @return  bool  True if connected to the database engine.
	 * @since   10.0
	 */
	public function connected(): bool;

	/**
	 * Create a new database using information from $options object.
	 *
	 * @param   object|null  $options  Passes the "db_name" and "db_user".
	 * @param   boolean      $utf      True if the database supports the UTF-8 character set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function createDatabase(?object $options, bool $utf = true): void;

	/**
	 * Disconnects the database.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function disconnect(): void;

	/**
	 * Drops a table from the database.
	 *
	 * @param   string   $table     The name of the database table to drop.
	 * @param   boolean  $ifExists  Optionally specify that the table must exist before it is dropped.
	 *
	 * @return  self
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function dropTable(string $table, bool $ifExists = true): AbstractDriver;

	/**
	 * Escape a string for usage in an SQL statement.
	 *
	 * @param   mixed  $text   The value to be escaped.
	 * @param   bool   $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string   The escaped string.
	 * @since   10.0
	 */
	public function escape($text, bool $extra = false): string;

	/**
	 * Execute the SQL statement.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function execute();

	/**
	 * Get the number of affected rows for the previous executed SQL statement.
	 *
	 * @return  int  The number of affected rows.
	 * @since   10.0
	 */
	public function getAffectedRows(): int;

	/**
	 * Method to get the database collation in use by sampling a text field of a table in the database.
	 *
	 * @return  string|null  The collation in use by the database, null if not supported.
	 * @since   10.0
	 */
	public function getCollation(): ?string;

	/**
	 * Method that provides access to the underlying database connection.
	 *
	 * @return  mixed  The underlying database connection resource.
	 * @since   10.0
	 */
	public function getConnection();

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * @return  string  The format string.
	 * @since   10.0
	 */
	public function getDateFormat(): string;

	/**
	 * Get the max_allowed_packet for the current database connection.
	 *
	 * If the session variable is unreadable we will return the default safe value of 1Mb.
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getMaxPacketSize(): int;

	/**
	 * Get the minimum supported database version.
	 *
	 * @return  string  The minimum version number for the database driver.
	 * @since   10.0
	 */
	public function getMinimum(): string;

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * @return  string|null  Null or zero representation of a timestamp.
	 * @since   10.0
	 */
	public function getNullDate(): ?string;

	/**
	 * Get the number of returned rows for the previous executed SQL statement.
	 *
	 * @param   resource  $cursor  An optional database cursor resource to extract the row count from.
	 *
	 * @return  int  The number of returned rows.
	 * @since   10.0
	 */
	public function getNumRows($cursor = null): int;

	/**
	 * Get the common table prefix for the database driver.
	 *
	 * @return  string  The common database table prefix.
	 * @since   10.0
	 */
	public function getPrefix(): string;

	/**
	 * Get the current query object, or a new query object.
	 *
	 * @param   bool  $new  False to return the current query object, True to return a new ADatabaseQuery object.
	 *
	 * @return  AbstractQuery  The current query object, or a new object extending the ADatabaseQuery class.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function getQuery(bool $new = false);

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   string   $table     The name of the database table.
	 * @param   boolean  $typeOnly  True (default) to only return field types.
	 *
	 * @return  array  An array of fields by table.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function getTableColumns(string $table, bool $typeOnly = true): array;

	/**
	 * Shows the table CREATE statement that creates the given tables.
	 *
	 * @param   string|array<string>  $tables  A table name, or a list of table names.
	 *
	 * @return  array  A list of the create SQL for the tables.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function getTableCreate($tables): array;

	/**
	 * Retrieves field information about the given tables.
	 *
	 * @param   string|array<string>  $tables  A table name, or a list of table names.
	 *
	 * @return  array  An array of keys for the table(s).
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function getTableKeys($tables): array;

	/**
	 * Method to get an array of all tables in the database.
	 *
	 * @return  array  An array of all the tables in the database.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function getTableList(): array;

	/**
	 * Get the version of the database connector.
	 *
	 * @return  string  The database connector version.
	 * @since   10.0
	 */
	public function getVersion(): string;

	/**
	 * Determine whether the database engine supports UTF-8 character encoding.
	 *
	 * @return  bool  True if the database engine supports UTF-8 character encoding.
	 * @since   10.0
	 */
	public function hasUTFSupport(): bool;

	/**
	 * Inserts a row into a table based on an object's properties.
	 *
	 * @param   string       $table   The name of the database table to insert into.
	 * @param   object  &    $object  A reference to an object whose public properties match the table fields.
	 * @param   string|null  $key     The name of the primary key. If provided the object property is updated.
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function insertObject(string $table, object &$object, ?string $key = null): bool;

	/**
	 * Method to get the auto-incremented value from the last INSERT statement.
	 *
	 * @return  int  The value of the auto-increment field from the last inserted row.
	 * @since   10.0
	 */
	public function insertid(): int;

	/**
	 * Method to check whether the installed database version is supported by the database driver
	 *
	 * @return  bool  True if the database version is supported
	 * @since   10.0
	 */
	public function isMinimumVersion(): bool;

	/**
	 * Method to get the first row of the result set from the database query as an associative array.
	 *
	 * @return  array|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadAssoc(): ?array;

	/**
	 * Get the result set as an array of associative arrays.
	 *
	 * The array of rows can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in undefined behavior.
	 *
	 * @param   string|null  $key     The name of a field on which to key the result array.
	 * @param   string|null  $column  An optional column name. Instead of the whole row, only this column value will be
	 *                                in the result array.
	 *
	 * @return  array|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadAssocList(?string $key = null, ?string $column = null): ?array;

	/**
	 * Get an array of values from the cardinal `$offset` field of the result set.
	 *
	 * @param   int  $offset  The row offset to use to build the result array.
	 *
	 * @return  array|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function loadColumn(int $offset = 0): ?array;

	/**
	 * Get the first row of the result set as an object.
	 *
	 * @param   string  $class  The class name to use for the returned row object.
	 *
	 * @return  object|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function loadObject(string $class = 'stdClass'): ?object;

	/**
	 * Get the result set as an array of objects.
	 *
	 * The array of objects can optionally be keyed by a field name, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field name can result in undefined behavior.
	 *
	 * @param   string  $key    The name of a field on which to key the result array.
	 * @param   string  $class  The class name to use for the returned row objects.
	 *
	 * @return  array|null   The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 */
	public function loadObjectList(string $key = '', string $class = 'stdClass'): ?array;

	/**
	 * Get the first field of the first row of the result set.
	 *
	 * @return  mixed  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function loadResult();

	/**
	 * Get the first row of the result set as an array.
	 *
	 * Columns are indexed numerically, so the first column in the result set would be accessible via `$row[0]`.
	 *
	 * @return  array|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function loadRow(): ?array;

	/**
	 * Get the result set as an array of numerically indexed arrays.
	 *
	 * The array of objects can optionally be keyed by a field offset, but defaults to a sequential numeric array.
	 *
	 * NOTE: Choosing to key the result array by a non-unique field can result in undefined behavior.
	 *
	 * @param   string|null  $key  The name of a field on which to key the result array.
	 *
	 * @return  array|null  The return value or null if the query failed.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function loadRowList(?string $key = null): ?array;

	/**
	 * Locks a table in the database.
	 *
	 * @param   string  $tableName  The name of the table to unlock.
	 *
	 * @return  self  Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function lockTable(string $tableName): AbstractDriver;

	/**
	 * Quote, and optionally escape, a string to be fed into the database.
	 *
	 * @param   string  $text    The string to quote.
	 * @param   bool    $escape  True (default) to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 * @since   10.0
	 */
	public function quote(string $text, bool $escape = true): string;

	/**
	 * Quote an identifier to prevent injection risks and reserved word conflicts.
	 *
	 * @param   array|string  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in
	 *                               quotes. Each type supports dot-notation name.
	 * @param   array|string  $as    The AS query part associated to $name. It can be string or array, in latter case
	 *                               it has to be same length of $name; if is null there will not be any AS part for
	 *                               string or array element.
	 *
	 * @return  array|string  The quote wrapped name, same type of $name.
	 * @since   10.0
	 */
	public function quoteName($name, $as = null);

	/**
	 * Renames a table in the database.
	 *
	 * @param   string       $oldTable  The name of the table to be renamed
	 * @param   string       $newTable  The new name for the table.
	 * @param   string|null  $backup    Table prefix
	 * @param   string|null  $prefix    For the table - used to rename constraints in non-mysql databases
	 *
	 * @return  self  Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function renameTable(
		string $oldTable, string $newTable, ?string $backup = null, ?string $prefix = null
	): self;

	/**
	 * Replace the `#__` meta-prefix with the actual table name prefix.
	 *
	 * @param   string  $sql     The SQL statement to prepare.
	 * @param   string  $prefix  The common table prefix.
	 *
	 * @return  string  The processed SQL statement.
	 * @since   10.0
	 */
	public function replacePrefix(string $sql, string $prefix = '#__'): string;

	/**
	 * Select a database for use.
	 *
	 * @param   string  $database  The name of the database to select for use.
	 *
	 * @return  bool  True if the database was successfully selected.
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function select(string $database): bool;

	/**
	 * Sets the SQL statement string for later execution.
	 *
	 * @param   AbstractQuery|string  $query   The SQL statement to set either as an AbstractQuery object or a string.
	 * @param   int                   $offset  The affected row offset to set.
	 * @param   int                   $limit   The maximum affected rows to set.
	 *
	 * @return  static  This object to support method chaining.
	 * @since   10.0
	 */
	public function setQuery($query, int $offset = 0, int $limit = 0);

	/**
	 * Set the connection to use UTF-8 character encoding.
	 *
	 * @return  bool  True on success.
	 * @since   10.0
	 */
	public function setUTF(): bool;

	/**
	 * Applies teh UTF8MB4 auto-detection status.
	 *
	 * @param   bool|null  $preference
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setUtf8Mb4AutoDetection(?bool $preference);

	/**
	 * Does this database server support UTF-8 four byte (utf8mb4) collation?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function supportsUtf8mb4(): bool;

	/**
	 * Commit a transaction.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function transactionCommit(): void;

	/**
	 * Roll back a transaction.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function transactionRollback(): void;

	/**
	 * Initialize a transaction.
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function transactionStart(): void;

	/**
	 * Truncate a table.
	 *
	 * @param   string  $table  The table to truncate
	 *
	 * @return  void
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function truncateTable(string $table): void;

	/**
	 * Unlocks tables in the database.
	 *
	 * @return  self  Returns this object to support chaining.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function unlockTables(): AbstractDriver;

	/**
	 * Updates a row in a table based on an object's properties.
	 *
	 * @param   string    $table   The name of the database table to update.
	 * @param   object   &$object  A reference to an object whose public properties match the table fields.
	 * @param   array     $key     The name of the primary key.
	 * @param   bool      $nulls   True to update null fields or false to ignore them.
	 *
	 * @return  bool  True on success.
	 *
	 * @throws  RuntimeException
	 * @since   10.0
	 */
	public function updateObject(string $table, object &$object, array $key, bool $nulls = false): bool;

	/**
	 * Get the metadata for the currently connected database.
	 *
	 * @param   string  $database  The database to query. Leave blank to query the current database.
	 *
	 * @return  Database
	 * @throws  RuntimeException  If we cannot retrieve the database metadata
	 * @since   10.0
	 */
	public function getDatabaseMeta(string $database = ''): Database;

	/**
	 * Get the metadata for a table in the currently connected database.
	 *
	 * @param   string  $tableName  The table name to retrieve meta for
	 *
	 * @return  Table
	 * @since   10.0
	 */
	public function getTableMeta(string $tableName): Table;

	/**
	 * Returns an array with column metadata. The array key is the column name.
	 *
	 * @param   string  $tableName  The table name to retrieve columns for
	 *
	 * @return  Column[]
	 * @since   10.0
	 */
	public function getColumnsMeta(string $tableName): array;

	/**
	 * Gets the name of the database used by this connection.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getDatabase(): string;
}
