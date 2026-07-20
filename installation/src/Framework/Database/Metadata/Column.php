<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Metadata;

defined('_AKEEBA') or die();

use JsonSerializable;

/**
 * A table column's metadata
 *
 * @package Akeeba\Replace\Database\Metadata
 */
class Column implements JsonSerializable
{
	/**
	 * The name of the column
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $columnName = '';

	/**
	 * The full type definition of the column
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $type = '';

	/**
	 * The column collation
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	private $collation = '';

	/**
	 * The name of the key this column belongs to
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $keyName = '';

	/**
	 * Is this column an auto-incrementing one?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private $autoIncrement = false;

	/**
	 * The default value for this column
	 *
	 * @var   mixed
	 * @since 10.0
	 */
	private $default = null;

	/**
	 * ColumnDefinition constructor.
	 *
	 * @param   string       $columnName     Name of the column
	 * @param   string       $type           Full type, e.g. "varchar(255)" or "int(10) unsigned"
	 * @param   string|null  $collation      The collation for this column
	 * @param   string       $keyName        The key name it belongs to. Key "PRI" means "part of primary key"
	 * @param   bool         $autoIncrement  Is it an auto-increment column? If it is it's also considered a primary key
	 * @param   mixed        $default        The default value for this column
	 *
	 * @since 10.0
	 */
	public function __construct(
		string $columnName, string $type, ?string $collation, string $keyName, bool $autoIncrement, $default = null
	)
	{
		$this->columnName    = $columnName;
		$this->type          = $type;
		$this->collation     = $collation;
		$this->keyName       = $keyName;
		$this->autoIncrement = $autoIncrement;
		$this->default       = $default;
	}

	/**
	 * Creates a column definition from a MySQL result describing the column, either from SHOW FULL COLUMNS or from a
	 * query to information_schema.COLUMNS.
	 *
	 * Example queries whose results I understand:
	 *
	 * SHOW FULL COLUMNS FROM `example`
	 * SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'yourDB' AND TABLE_NAME = 'example';
	 *
	 * @param   array  $result  The MySQL result I will be processing
	 *
	 * @return  static
	 * @since   10.0
	 */
	public static function fromDatabaseResult(array $result): Column
	{
		$columnName    = array_key_exists('Field', $result) ? $result['Field'] : $result['COLUMN_NAME'];
		$type          = array_key_exists('Type', $result) ? $result['Type'] : $result['COLUMN_TYPE'];
		$collation     = array_key_exists('Collation', $result) ? $result['Collation'] : $result['COLLATION_NAME'];
		$keyName       = array_key_exists('Key', $result) ? $result['Key'] : $result['COLUMN_KEY'];
		$autoIncrement = (array_key_exists('Extra', $result) ? $result['Extra'] : $result['EXTRA']) == 'auto_increment';
		$default       = array_key_exists('Default', $result) ? $result['Default'] : $result['COLUMN_DEFAULT'];

		return new static($columnName, $type, $collation, $keyName, $autoIncrement);
	}

	/**
	 * Get the name of the column
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getColumnName(): string
	{
		return $this->columnName;
	}

	/**
	 * Get the full type definition for the column
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * Get the column's collation, if different to the table's collation.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getCollation(): string
	{
		return $this->collation;
	}

	/**
	 * Get the name of the key the table belongs to (if any)
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getKeyName(): ?string
	{
		return $this->keyName;
	}

	/**
	 * Is this an auto-increment field?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isAutoIncrement(): bool
	{
		return $this->autoIncrement;
	}

	/**
	 * Is this field a primary key to the table?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isPK(): bool
	{
		return $this->autoIncrement || ($this->keyName == 'PRI');
	}

	/**
	 * Is this field of a text type?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isText(): bool
	{
		$type = $this->type;

		if (empty($type))
		{
			return false;
		}

		// Remove parentheses, indicating field options / size (they don't matter in type detection)
		if (strpos($type, '(') === false)
		{
			$type .= '()';
		}

		[$type, $parameters] = explode('(', $type);

		// If we have options after a space, remove them
		if (strpos($type, ' ') !== false)
		{
			[$type, $otherOptions] = explode(' ', $type);
		}

		$type = strtolower($type);

		$textTypes = [
			'varchar',
			'text',
			'char',
			'character varying',
			'nvarchar',
			'nchar',
			'tinytext',
			'smalltext',
			'longtext',
			'mediumtext',
		];

		return in_array($type, $textTypes);
	}

	/**
	 * The default value for this column
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function getDefault()
	{
		return $this->default;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'columnName'    => $this->columnName,
			'type'          => $this->type,
			'collation'     => $this->collation,
			'keyName'       => $this->keyName,
			'autoIncrement' => $this->autoIncrement,
			'default'       => $this->default,
		];
	}

	public static function fromJson(string $json): self
	{
		$object = json_decode($json);

		if (!is_object($object))
		{
			throw new \RuntimeException('Cannot unserialise the table column metadata.');
		}

		return new self($object->columnName ?? '', $object->type ?? '', $object->collation ?? '', $object->keyName ?? '', $object->autoIncrement ?? false, $object->default ?? null);
	}
}