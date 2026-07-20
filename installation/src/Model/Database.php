<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Configuration\Database as DatabaseConfiguration;
use Akeeba\BRS\Framework\Database\AbstractDriver;
use Akeeba\BRS\Framework\Database\DatabaseDriverInterface;
use Akeeba\BRS\Framework\Mvc\Model;

class Database extends Model
{
	/**
	 * Do we have database drivers for all database technologies in use across all databases?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasAllNecessaryConnectors(): bool
	{
		return array_reduce(
			$this->getContainer()->get('configuration')->databases,
			function ($carry, DatabaseConfiguration $db) {
				$connectors      = AbstractDriver::getConnectors($db->dbtech);
				$limitConnectors = $this->getContainer()->get('configuration')->limitDrivers;

				// If platform.json defines a non-empty list of limitDrivers, filter our connectors by it.
				if (!empty($limitConnectors))
				{
					$connectors = array_intersect($limitConnectors, $connectors);
				}

				return $carry && count($connectors) > 0;
			},
			true
		);
	}

	/**
	 * Get the DB driver connection options for the named database.
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getDbDriverOptions(string $key): array
	{
		$allDb          = $this->getContainer()->get('configuration')->databases;
		$connectionVars = $allDb[$key] ?? $allDb[$key . '.sql'] ?? null;

		if (empty($connectionVars))
		{
			return [];
		}

		return [
			'driver'   => $connectionVars->dbtype,
			'database' => $connectionVars->dbname,
			'select'   => 1,
			'host'     => $connectionVars->dbhost,
			'user'     => $connectionVars->dbuser,
			'password' => $connectionVars->dbpass,
			'prefix'   => $connectionVars->prefix,
			'ssl'      => [
				'enable'             => (bool) $connectionVars->dbencryption,
				'cipher'             => $connectionVars->dbsslcipher,
				'ca'                 => $connectionVars->dbsslca,
				'key'                => $connectionVars->dbsslkey,
				'cert'               => $connectionVars->dbsslcert,
				'verify_server_cert' => (bool) $connectionVars->dbsslverifyservercert,
			],
		];
	}

	/**
	 * Get a database driver object for the named database.
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  DatabaseDriverInterface|null
	 * @since   10.0
	 */
	public function getDb(string $key): ?DatabaseDriverInterface
	{
		$options = $this->getDbDriverOptions($key);

		return $this->getContainer()->get('db')->driver($options['driver'], $options);
	}

	/**
	 * Get the maximum packet size defined in the connection to the named database.
	 *
	 * This will only work if we can connect to the database server. This will not be the case before the user
	 * configures the database connection!
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getCurrentMaxPacketSize(string $key): int
	{
		try
		{
			return $this->getDb($key)->getMaxPacketSize();
		}
		catch (\Throwable $e)
		{
			return 1048576;
		}
	}

	/**
	 * Returns the database configuration object for the named database.
	 *
	 * @param   string  $key  The database's key (name of SQL file)
	 *
	 * @return  null|DatabaseConfiguration
	 * @since   10.0
	 */
	public function getDatabaseInfo($key): ?DatabaseConfiguration
	{
		$allDb = $this->getContainer()->get('configuration')->databases;

		return $allDb[$key] ?? $allDb[$key . '.sql'] ?? null;
	}

	/**
	 * Returns the keys of all available database definitions.
	 *
	 * @return  array<string>
	 * @since   10.0
	 */
	public function getDatabaseNames(): array
	{
		return array_keys($this->getContainer()->get('configuration')->databases);
	}

	/**
	 * Detects if we have a flag file for large columns; if so, it returns the max query length in bytes.
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getLargeTablesDetectedValue(): int
	{
		$file = $this->getContainer()->get('paths')->get('installation') . '/large_tables_detected';

		if (!file_exists($file))
		{
			return 0;
		}

		return (int) @file_get_contents($file);
	}

	/**
	 * Sets a database's configuration.
	 *
	 * @param   string                       $key   The database's key (name of SQL file)
	 * @param   array|DatabaseConfiguration  $data  The database's data
	 *
	 * @since   10.0
	 */
	public function setDatabaseInfo(string $key, $data): void
	{
		$config = $this->getContainer()->get('configuration');
		$allDb  = $config->databases;

		if (!isset($allDb[$key]) && isset($allDb[$key . '.sql']))
		{
			$key .= '.sql';
		}

		$allDb[$key] = is_array($data) ? new DatabaseConfiguration($data) : $data;

		$config->setDatabases($allDb);
		$config->saveToSession();
	}
}