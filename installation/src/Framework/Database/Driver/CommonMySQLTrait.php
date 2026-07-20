<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Driver;

use Akeeba\BRS\Framework\Database\AbstractDriver;
use Akeeba\BRS\Framework\Database\DatabaseDriverInterface;

defined('_AKEEBA') or die();

/**
 * Code shared across the MySQL drivers.
 *
 * @since  10.0
 */
trait CommonMySQLTrait
{
	/** @inheritdoc */
	public function unlockTables(): AbstractDriver
	{
		$this->setQuery('UNLOCK TABLES')->execute();

		return $this;
	}

	/** @inheritdoc */
	public function getCollation(): ?string
	{
		$this->connect();

		$this->setQuery('SHOW FULL COLUMNS FROM #__users');
		$array = $this->loadAssocList();

		return $array['2']['Collation'];
	}

	/** @inheritdoc */
	public function getTableColumns(string $table, bool $typeOnly = true): array
	{
		$this->connect();

		$result = [];

		// Set the query to get the table fields statement.
		$this->setQuery('SHOW FULL COLUMNS FROM ' . $this->quoteName($this->escape($table)));
		$fields = $this->loadObjectList();

		// If we only want the type as the value add just that to the list.
		if ($typeOnly)
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = preg_replace("/[(0-9)]/", '', $field->Type);
			}
		}
		// If we want the whole field data object add that to the list.
		else
		{
			foreach ($fields as $field)
			{
				$result[$field->Field] = $field;
			}
		}

		return $result;
	}

	/** @inheritdoc */
	public function getTableList(): array
	{
		$this->connect();

		// Set the query to get the tables statement.
		$this->setQuery('SHOW TABLES');

		return $this->loadColumn();
	}

	/** @inheritdoc */
	public function renameTable(
		string $oldTable, string $newTable, ?string $backup = null, ?string $prefix = null
	): DatabaseDriverInterface
	{
		$this->setQuery('RENAME TABLE ' . $oldTable . ' TO ' . $newTable)->execute();

		return $this;
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
			// Set the query to get the table CREATE statement.
			$this->setQuery('SHOW CREATE table ' . $this->quoteName($this->escape($table)));
			$row = $this->loadRow();

			// Populate the result array based on the CREATE statements.
			$result[$table] = $row[1];
		}

		return $result;
	}

	/** @inheritdoc */
	public function lockTable(string $tableName): AbstractDriver
	{
		$this->setQuery('LOCK TABLES ' . $this->quoteName($tableName) . ' WRITE')->execute();

		return $this;
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
	public function getTableKeys($tables): array
	{
		$this->connect();

		// Get the details columns information.
		$this->setQuery('SHOW KEYS FROM ' . $this->quoteName($tables));

		return $this->loadObjectList();
	}
}