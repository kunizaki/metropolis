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
 * A database table's metadata
 *
 * @package Akeeba\Replace\Database\Metadata
 */
class Table implements JsonSerializable
{
	/**
	 * Table name
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $name = '';

	/**
	 * Database engine
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $engine = '';

	/**
	 * Average row length in bytes
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $averageRowLength = 0;

	/**
	 * Table collation, if it's different than the database's
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $collation = '';

	/**
	 * TableDefinition constructor.
	 *
	 * @param   string  $name              The name of the table
	 * @param   string  $engine            The table engine
	 * @param   int     $averageRowLength  Average row length, in bytes
	 * @param   string  $collation         Table collation (if different to database's)
	 *
	 * @since   10.0
	 */
	public function __construct(string $name, string $engine, int $averageRowLength, string $collation)
	{
		$this->name             = $name;
		$this->engine           = $engine;
		$this->averageRowLength = $averageRowLength;
		$this->collation        = $collation;
	}

	/**
	 * Creates a table definition from the results of a MySQL query, either SHOW TABLE STATUS or by selecting from
	 * information_schema.TABLES.
	 *
	 * Example queries understood by this method:
	 *
	 * SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'replacetest' AND TABLE_NAME = 'akr_dbtest';
	 * SHOW TABLE STATUS WHERE Name = 'akr_dbtest';
	 *
	 * @param   array  $result  The MySQL result I will be processing
	 *
	 * @return  static
	 * @since   10.0
	 */
	public static function fromDatabaseResult(array $result): Table
	{
		$name             = array_key_exists('Name', $result) ? $result['Name'] : $result['TABLE_NAME'];
		$engine           = array_key_exists('Engine', $result) ? $result['Engine'] : $result['ENGINE'];
		$averageRowLength = array_key_exists('Avg_row_length', $result) ? $result['Avg_row_length']
			: $result['AVG_ROW_LENGTH'];
		$collation        = array_key_exists('Collation', $result) ? $result['Collation'] : $result['TABLE_COLLATION'];

		return new static($name, $engine, $averageRowLength, $collation);
	}

	/**
	 * The name of the table
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * The storage engine of the table
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getEngine(): string
	{
		return $this->engine;
	}

	/**
	 * The average row length, in bytes
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getAverageRowLength(): int
	{
		return $this->averageRowLength;
	}

	/**
	 * The collation of the table, if different to the database's default collation
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getCollation(): string
	{
		return $this->collation;
	}

	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'name' => $this->name,
			'engine' => $this->engine,
			'averageRowLength' => $this->averageRowLength,
			'collation' => $this->collation,
		];
	}

	public static function fromJson(string $json)
	{
		$object = @json_decode($json);

		if (!is_object($object))
		{
			throw new \RuntimeException('Cannot unserialise the table metadata.');
		}

		return new self($object->name ?? '', $object->engine ?? '', $object->averageRowLength ?? 0, $object->collation ?? '');
	}
}