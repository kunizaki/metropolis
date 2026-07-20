<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Language\Language;
use Psr\Container\ContainerInterface;
use RuntimeException;

defined('_AKEEBA') or die();

/**
 * A factory for database-related objects
 *
 * @since  10.0
 */
class Factory implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Return a database driver using the provided configuration options
	 *
	 * @param   string  $name     Database driver name
	 * @param   array   $options  Connection options
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function driver(string $name = 'mysqli', array $options = []): DatabaseDriverInterface
	{
		// Sanitize the database connector options.
		$options['driver']   = preg_replace('/[^A-Z0-9_.-]/i', '', $name);
		$options['database'] = $options['database'] ?? null;
		$options['select']   = $options['select'] ?? true;

		// Derive the class name from the driver.
		$class = __NAMESPACE__ . '\\Driver\\' . ucfirst(strtolower($options['driver']));

		// If the class still doesn't exist we have nothing left to do but throw an exception.  We did our best.
		if (!class_exists($class))
		{
			throw new RuntimeException(sprintf('Unable to load Database Driver: %s', $options['driver']));
		}

		// Create our new ADatabaseDriver connector based on the options given.
		try
		{
			return new $class($this->getContainer(), $options);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
		}
	}

	/**
	 * Return a new restoration object using the provided configuration values
	 *
	 * @param   string      $dbkey         The database key in databases.json
	 * @param   array|null  $dbjsonValues  The config values for this database key
	 *
	 * @return  AbstractRestore
	 * @since   10.0
	 */
	public function restore(string $dbkey, ?array $dbjsonValues = null): AbstractRestore
	{
		/** @var Language $lang */
		$lang = $this->getContainer()->get('language');

		if (empty($dbjsonValues))
		{
			throw new RuntimeException($lang->sprintf('FRAMEWORK_DBRESTORE_ERR_UNKNOWNKEY', $dbkey));
		}

		if (is_object($dbjsonValues))
		{
			$dbjsonValues = (array) $dbjsonValues;
		}

		$class = __NAMESPACE__ . '\\Restore\\' . ucfirst($dbjsonValues['dbtype']);

		return new $class($this->getContainer(), $dbkey, $dbjsonValues);
	}
}