<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

defined('_AKEEBA') or die();

/**
 * Configuration class for out-of-root folders.
 *
 * @property-read  string $name     The real name of the folder
 * @property-read  string $virtual  The virtual name of the folder
 *
 * @since  10.0
 */
class Folders extends AbstractConfiguration
{
	/**
	 * The rel name of the folder.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $name = '';

	/**
	 * The virtual name of the folder in the backup archive.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $virtual = '';

	/**
	 * Loads the eff.json file.
	 *
	 * @param   string  $filePath  The path to the eff.json file
	 *
	 * @return  array
	 * @since   10.0
	 */
	public static function loadEffJson(string $filePath): array
	{
		$json = @file_get_contents($filePath);

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

		return $data['eff'] ?? [];
	}

	public function setName(string $name): void
	{
		$this->name = $name;
	}

	public function setVirtual(string $virtual): void
	{
		$this->virtual = $virtual;
	}
}