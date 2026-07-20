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

/**
 * A database's metadata
 *
 * @package Akeeba\Replace\Database\Metadata
 */
class Database
{
	/**
	 * Database name
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $name;

	/**
	 * Default character set
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $characterSet;

	/**
	 * Default collation
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $collation;

	/**
	 * DatabaseDefinition constructor.
	 *
	 * @param   string  $name          The database name
	 * @param   string  $characterSet  The default character set of the database
	 * @param   string  $collation     The default collation of the database
	 *
	 * @since   10.0
	 */
	public function __construct(string $name, string $characterSet = 'utf8', string $collation = 'utf8_general_ci')
	{
		$this->name         = $name;
		$this->characterSet = $characterSet;
		$this->collation    = $collation;
	}

	/**
	 * Create a database definition from a query result against INFORMATION_SCHEMA.SCHEMATA
	 *
	 * @param   array  $result  A row of the INFORMATION_SCHEMA.SCHEMATA table
	 *
	 * @return  static
	 * @since   10.0
	 */
	public static function fromDatabaseResult(array $result): Database
	{
		$name         = $result['SCHEMA_NAME'];
		$characterSet = $result['DEFAULT_CHARACTER_SET_NAME'];
		$collation    = $result['DEFAULT_COLLATION_NAME'];

		return new static($name, $characterSet, $collation);
	}

	/**
	 * Returns the database name
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Returns the default character set of the database
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getCharacterSet(): string
	{
		return $this->characterSet;
	}

	/**
	 * Returns the default collation of the database
	 *
	 * @return string
	 * @since   10.0
	 */
	public function getCollation(): string
	{
		return $this->collation;
	}


}