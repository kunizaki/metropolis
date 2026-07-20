<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Restore;

use Akeeba\BRS\Framework\Database\AbstractDriver;
use Akeeba\BRS\Framework\Database\AbstractRestore;
use Akeeba\BRS\Framework\Database\Restore\Exception\Dbname;
use Akeeba\BRS\Framework\Database\Restore\Exception\Dbuser;
use Exception;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Database restoration class for the PostgreSQL driver.
 *
 * @since  10.0
 */
final class Postgresql extends AbstractRestore
{
	/** @inheritdoc  */
	public function __construct(ContainerInterface $container, string $dbkey, array $dbjsonValues)
	{
		parent::__construct($container, $dbkey, $dbjsonValues);

		// Set up allowed error codes for PostgreSQL
		$this->allowedErrorCodes = [
			// Data truncation warnings
			'01004', // String data, right truncation
			'22001', // String data, right truncation
			'22003', // Numeric value out of range
			'22007', // Invalid datetime format
			'22008', // Datetime field overflow
			'22012', // Division by zero
			'22P02', // Invalid text representation
		];

		// Set up allowed comment delimiters for PostgreSQL
		$this->comment = [
			'--',
			'/*!',
		];

		// Connect to the database
		$this->getDatabase();

		// Suppress foreign key checks by deferring constraints
		if ($this->dbjsonValues['foreignkey'] ?? false)
		{
			$this->executeQueryWithoutFailing('SET CONSTRAINTS ALL DEFERRED');
		}
	}

	/** @inheritdoc  */
	protected function getDatabase(bool $selectDatabase = true): AbstractDriver
	{
		// PostgreSQL requires the database to have already been created.
		return parent::getDatabase(true);
	}

	/** @inheritdoc  */
	protected function processQueryLine(string $query): bool
	{
		$query = trim($query);

		/**
		 * If there is a multiline comment at the beginning of the query remove it now.
		 */
		if (substr($query, 0, 2) == '/*')
		{
			$endCommentPos = strpos($query, '*/');

			if ($endCommentPos !== false)
			{
				$query = substr($query, $endCommentPos + 2);
			}
		}

		// CREATE TABLE query pre-processing
		if (substr($query, 0, 12) == 'CREATE TABLE')
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			// Get the table name and handle backup/drop
			$tableName = $this->getCreateTableName($query);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($tableName, 'table'))
			{
				return true;
			}

			$this->dropOrRenameTable($tableName);
		}
		// CREATE VIEW query pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, ' VIEW ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			// Get the view name and drop it
			$viewName = $this->getViewName($query);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($viewName, 'view'))
			{
				return true;
			}

			$this->dropView($viewName);
		}
		// CREATE FUNCTION pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'FUNCTION ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entityName = $this->getEntityName($query, ' FUNCTION ');

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($entityName, 'function'))
			{
				return true;
			}

			$this->dropFunction($entityName);
		}
		// CREATE TRIGGER pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'TRIGGER ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entityName = $this->getEntityName($query, ' TRIGGER ');

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($entityName, 'trigger'))
			{
				return true;
			}

			// PostgreSQL triggers need to know the table they're on to be dropped
			// We'll use CASCADE to drop dependencies
			$this->dropTrigger($entityName);
		}
		// CREATE SEQUENCE pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'SEQUENCE ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entityName = $this->getEntityName($query, ' SEQUENCE ');
			$this->dropSequence($entityName);
		}
		// CREATE INDEX pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, ' INDEX ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entityName = $this->getEntityName($query, ' INDEX ');
			$this->dropIndex($entityName);
		}
		elseif (substr($query, 0, 6) == 'INSERT')
		{
			if (!$this->inTransaction)
			{
				try
				{
					$this->db->transactionStart();
					$this->inTransaction = true;
				}
				catch (Exception $e)
				{
				}
			}

			$tableName = $this->getInsertTableName($query);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($tableName, 'table'))
			{
				return true;
			}
		}
		else
		{
			// Maybe a DROP statement or other DDL? Close the current transaction.
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();
					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}
		}

		if (empty($query))
		{
			return true;
		}

		$this->execute($query);

		return true;
	}

	/**
	 * Extract the table name from a CREATE TABLE command
	 *
	 * @param   string  $query  The SQL query for the CREATE TABLE
	 *
	 * @return  string
	 */
	protected function getCreateTableName(string $query): string
	{
		// Rest of query, after CREATE TABLE
		$restOfQuery = trim(substr($query, 12, strlen($query) - 12));

		// Handle IF NOT EXISTS
		if (stripos($restOfQuery, 'IF NOT EXISTS ') === 0)
		{
			$restOfQuery = trim(substr($restOfQuery, 14));
		}

		return $this->extractIdentifier($restOfQuery);
	}

	/**
	 * Extract the table name from an INSERT INTO command
	 *
	 * @param   string  $query  The SQL query for the INSERT INTO
	 *
	 * @return  string
	 */
	protected function getInsertTableName(string $query): string
	{
		// Rest of query, after INSERT INTO
		$restOfQuery = trim(substr($query, 11, strlen($query) - 11));

		return $this->extractIdentifier($restOfQuery);
	}

	/**
	 * Extract the view name from a CREATE VIEW query
	 *
	 * @param   string  $query  The SQL query
	 *
	 * @return  string
	 */
	protected function getViewName(string $query): string
	{
		$viewPos     = stripos($query, ' VIEW ');
		$restOfQuery = trim(substr($query, $viewPos + 6));

		// Handle OR REPLACE
		if (stripos($restOfQuery, 'OR REPLACE ') === 0)
		{
			$restOfQuery = trim(substr($restOfQuery, 11));
		}

		return $this->extractIdentifier($restOfQuery);
	}

	/**
	 * Extracts the name of an entity from a CREATE query
	 *
	 * @param   string  $query    The SQL query
	 * @param   string  $keyword  The entity keyword (e.g., " FUNCTION ")
	 *
	 * @return  string
	 */
	protected function getEntityName(string $query, string $keyword): string
	{
		$entityPos   = stripos($query, $keyword);
		$restOfQuery = trim(substr($query, $entityPos + strlen($keyword)));

		// Handle IF NOT EXISTS / OR REPLACE
		if (stripos($restOfQuery, 'IF NOT EXISTS ') === 0)
		{
			$restOfQuery = trim(substr($restOfQuery, 14));
		}
		elseif (stripos($restOfQuery, 'OR REPLACE ') === 0)
		{
			$restOfQuery = trim(substr($restOfQuery, 11));
		}

		return $this->extractIdentifier($restOfQuery);
	}

	/**
	 * Extract an identifier (table name, view name, etc.) from the beginning of a string.
	 *
	 * PostgreSQL uses double quotes for quoted identifiers.
	 *
	 * @param   string  $restOfQuery  The string to extract from
	 *
	 * @return  string  The extracted identifier
	 */
	protected function extractIdentifier(string $restOfQuery): string
	{
		// Is there a double quote (PostgreSQL quoted identifier)?
		if (substr($restOfQuery, 0, 1) == '"')
		{
			// There is a double quote. Find the closing quote.
			$pos = 0;

			while (true)
			{
				$pos++;

				$thisChar = substr($restOfQuery, $pos, 1);
				$nextChar = substr($restOfQuery, $pos + 1, 1);

				// Did we reach the end of the string?
				if ($thisChar === false || $thisChar === '')
				{
					break;
				}

				// Two double quotes side-by-side is an escaped quote; skip over it
				if ($thisChar === '"' && $nextChar === '"')
				{
					$pos++;
					continue;
				}

				// Current char is a double quote, the next one is not, we found the ending quote.
				if ($thisChar === '"' && $nextChar !== '"')
				{
					break;
				}
			}

			$entityName = substr($restOfQuery, 1, $pos - 1);
			// Unescape double quotes
			$entityName = str_replace('""', '"', $entityName);
		}
		else
		{
			// No quote. The identifier ends at the next space, parenthesis, or end of string.
			$pos = strcspn($restOfQuery, ' (');
			$entityName = substr($restOfQuery, 0, $pos);
		}

		return $entityName;
	}

	/**
	 * Drop or rename a table (with a bak_ prefix), depending on the user options
	 *
	 * @param   string  $tableName  The table name to drop or rename
	 *
	 * @return  void
	 */
	protected function dropOrRenameTable(string $tableName): void
	{
		$db = $this->getDatabase();

		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		// Should I back the table up?
		if (($prefix != '') && ($existing == 'backup') && (strpos($tableName, '#__') === 0))
		{
			// It's a table with a prefix, a prefix IS specified and we are asked to back it up.
			// Start by dropping any existing backup tables
			$backupTable = str_replace('#__', 'bak_', $tableName);
			try
			{
				$db->dropTable($backupTable);
				$db->renameTable($tableName, $backupTable);
			}
			catch (Exception $exc)
			{
				// We can't rename the table. Fall-through to the final line to delete it.
			}
		}

		// Try to drop the table anyway
		$db->dropTable($tableName);
	}

	/**
	 * Drops a View
	 *
	 * @param   string  $viewName  The view name to drop
	 *
	 * @return  void
	 */
	protected function dropView(string $viewName): void
	{
		$db = $this->getDatabase();
		$dropQuery = 'DROP VIEW IF EXISTS ' . $db->quoteName($viewName) . ' CASCADE';
		$db->setQuery($dropQuery);
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Drops a Function
	 *
	 * @param   string  $functionName  The function name to drop
	 *
	 * @return  void
	 */
	protected function dropFunction(string $functionName): void
	{
		$db = $this->getDatabase();
		// PostgreSQL requires knowing the function signature to drop it
		// Using CASCADE to handle dependencies
		$dropQuery = 'DROP FUNCTION IF EXISTS ' . $db->quoteName($functionName) . ' CASCADE';
		$db->setQuery($dropQuery);
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Drops a Trigger
	 *
	 * @param   string  $triggerName  The trigger name to drop
	 *
	 * @return  void
	 */
	protected function dropTrigger(string $triggerName): void
	{
		$db = $this->getDatabase();

		// In PostgreSQL, we need to know the table to drop a trigger
		// Try to find the trigger and its table
		try
		{
			$query = "SELECT tgname, relname FROM pg_trigger t JOIN pg_class c ON t.tgrelid = c.oid WHERE tgname = " . $db->quote($triggerName);
			$db->setQuery($query);
			$result = $db->loadObject();

			if ($result)
			{
				$dropQuery = 'DROP TRIGGER IF EXISTS ' . $db->quoteName($triggerName) . ' ON ' . $db->quoteName($result->relname) . ' CASCADE';
				$db->setQuery($dropQuery);
				$db->execute();
			}
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Drops a Sequence
	 *
	 * @param   string  $sequenceName  The sequence name to drop
	 *
	 * @return  void
	 */
	protected function dropSequence(string $sequenceName): void
	{
		$db = $this->getDatabase();
		$dropQuery = 'DROP SEQUENCE IF EXISTS ' . $db->quoteName($sequenceName) . ' CASCADE';
		$db->setQuery($dropQuery);
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Drops an Index
	 *
	 * @param   string  $indexName  The index name to drop
	 *
	 * @return  void
	 */
	protected function dropIndex(string $indexName): void
	{
		$db = $this->getDatabase();
		$dropQuery = 'DROP INDEX IF EXISTS ' . $db->quoteName($indexName) . ' CASCADE';
		$db->setQuery($dropQuery);
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Execute a database query, ignoring any failures
	 *
	 * @param   string  $sql  The SQL query to execute
	 *
	 * @return  void
	 */
	protected function executeQueryWithoutFailing(string $sql): void
	{
		$this->db->setQuery($sql);

		try
		{
			$this->db->execute();
		}
		catch (Exception $exc)
		{
			// Do nothing if that fails. Maybe we can continue with the restoration.
		}
	}

	/** @inheritdoc  */
	protected function conditionallyDropTables(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = "SELECT tablename FROM pg_tables WHERE schemaname = 'public'";

		if ($existing == 'dropprefix' && !empty($prefix))
		{
			$query .= " AND tablename LIKE " . $db->quote($prefix . '%');
		}

		$db->setQuery($query);
		$tables = $db->loadColumn();

		foreach ($tables as $table)
		{
			try
			{
				$db->dropTable($table, true);
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}
	}

	/** @inheritdoc  */
	protected function conditionallyDropViews(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = "SELECT viewname FROM pg_views WHERE schemaname = 'public'";

		if ($existing == 'dropprefix' && !empty($prefix))
		{
			$query .= " AND viewname LIKE " . $db->quote($prefix . '%');
		}

		$db->setQuery($query);
		$views = $db->loadColumn();

		foreach ($views as $view)
		{
			try
			{
				$sql = 'DROP VIEW IF EXISTS ' . $db->quoteName($view) . ' CASCADE';
				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}
	}

	/** @inheritdoc  */
	protected function conditionallyDropTriggers(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		// PostgreSQL triggers need table name to be dropped
		$query = "SELECT t.tgname, c.relname
			FROM pg_trigger t
			JOIN pg_class c ON t.tgrelid = c.oid
			JOIN pg_namespace n ON c.relnamespace = n.oid
			WHERE NOT t.tgisinternal AND n.nspname = 'public'";

		if ($existing == 'dropprefix' && !empty($prefix))
		{
			$query .= " AND t.tgname LIKE " . $db->quote($prefix . '%');
		}

		$db->setQuery($query);
		$triggers = $db->loadObjectList();

		foreach ($triggers as $trigger)
		{
			try
			{
				$sql = 'DROP TRIGGER IF EXISTS ' . $db->quoteName($trigger->tgname) . ' ON ' . $db->quoteName($trigger->relname) . ' CASCADE';
				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}
	}

	/** @inheritdoc  */
	protected function conditionallyDropFunctions(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		// Get functions with their argument types for proper dropping
		$query = "SELECT p.proname, pg_get_function_identity_arguments(p.oid) AS args
			FROM pg_proc p
			JOIN pg_namespace n ON p.pronamespace = n.oid
			WHERE n.nspname = 'public' AND p.prokind = 'f'";

		if ($existing == 'dropprefix' && !empty($prefix))
		{
			$query .= " AND p.proname LIKE " . $db->quote($prefix . '%');
		}

		$db->setQuery($query);
		$functions = $db->loadObjectList();

		foreach ($functions as $function)
		{
			try
			{
				$sql = 'DROP FUNCTION IF EXISTS ' . $db->quoteName($function->proname) . '(' . $function->args . ') CASCADE';
				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}
	}

	/** @inheritdoc  */
	protected function conditionallyDropProcedures(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		// Get procedures with their argument types for proper dropping
		// Note: PostgreSQL 11+ has procedures (prokind = 'p')
		$query = "SELECT p.proname, pg_get_function_identity_arguments(p.oid) AS args
			FROM pg_proc p
			JOIN pg_namespace n ON p.pronamespace = n.oid
			WHERE n.nspname = 'public' AND p.prokind = 'p'";

		if ($existing == 'dropprefix' && !empty($prefix))
		{
			$query .= " AND p.proname LIKE " . $db->quote($prefix . '%');
		}

		try
		{
			$db->setQuery($query);
			$procedures = $db->loadObjectList();

			foreach ($procedures as $procedure)
			{
				try
				{
					$sql = 'DROP PROCEDURE IF EXISTS ' . $db->quoteName($procedure->proname) . '(' . $procedure->args . ') CASCADE';
					$db->setQuery($sql)->execute();
				}
				catch (Exception $e)
				{
					// We can safely ignore errors at this stage
				}
			}
		}
		catch (Exception $e)
		{
			// prokind column may not exist in older PostgreSQL versions
		}
	}
}
