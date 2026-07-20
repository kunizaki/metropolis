<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

use Akeeba\BRS\Framework\Registry\Registry;

defined('_AKEEBA') or die();

/**
 * Configuration class to handle the extrainfo.json data.
 *
 * @property-read  string|null $host            Hostname of the site backed up.
 * @property-read  string|null $backup_date     Date the backup was taken on (YYYY-mm-dd HH:mm:ss, in GMT).
 * @property-read  string|null $akeeba_version  Akeeba Backup / Solo version used to take the backup.
 * @property-read  string|null $php_version     PHP version the backup was taken on.
 * @property-read  string|null $root            Path to the site's root at backup time.
 * @property-read  bool|null   $custom_public   Does the site have a custom public directory? (Joomla only)
 * @property-read  string|null $JPATH_ROOT      The original site's JPATH_ROOT. (Joomla only)
 * @property-read  string|null $JPATH_PUBLIC    The original site's JPATH_PUBLIC. (Joomla only)
 *
 * @since  10.0
 */
class ExtraInfo extends AbstractConfiguration
{
	/**
	 * Holds the extrainfo.json data
	 *
	 * @var   Registry
	 * @since 10.0
	 */
	private $_registry;

	public function __construct(array $data)
	{
		$this->_registry = new Registry($data);
	}

	/**
	 * Loads the extrainfo.json data.
	 *
	 * @param   string  $fileName
	 *
	 * @return  array
	 * @since   10.0
	 */
	public static function loadExtraInfoJson(string $fileName): array
	{
		$json = @file_get_contents($fileName);

		if ($json === false)
		{
			return [];
		}

		try
		{
			$data = @json_decode($json, true);
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!is_array($data))
		{
			return [];
		}

		return $data;
	}

	/** @inheritdoc  */
	public function __get($name)
	{
		return $this->_registry->get($name);
	}

	/** @inheritdoc  */
	public function __set($name, $value)
	{
		if ($this->_immutable)
		{
			throw new \RuntimeException(
				'Cannot set configuration option ' . $name . ' because this instance is immutable'
			);
		}

		$this->_registry->set($name, $value);
	}

	/** @inheritdoc  */
	public function toArray(): array
	{
		return $this->_registry->toArray();
	}
}