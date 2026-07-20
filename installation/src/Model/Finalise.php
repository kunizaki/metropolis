<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Filesystem\RecursiveDeleteTrait;
use Akeeba\BRS\Framework\Mvc\Model;

/**
 * Model for the final page of the restoration script.
 *
 * @since  10.0
 */
class Finalise extends Model
{
	use RecursiveDeleteTrait;

	/**
	 * Should I display the configuration file contents?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function getShowConfig(): bool
	{
		return $this->getState('showconfig', 0);
	}

	/**
	 * Get the name of the configuration file relative to the site's root.
	 *
	 * @return  string
	 * @since   10.0
	 */
	final public function getConfigFilename(): string
	{
		try
		{
			return $this->getContainer()->get('mvcFactory')->model('Configuration')->getConfigFilename();
		}
		catch (\Exception $e)
		{
			return '';
		}
	}

	/**
	 * Get the contents of the configuration file.
	 *
	 * @return  string
	 * @since   10.0
	 */
	final public function getConfigContents(): string
	{
		return (string) $this->getContainer()->get('mvcFactory')->model('Configuration');
	}

	/**
	 * Deletes the installation folder.
	 *
	 * Note: we use the domain `akeeba.dev` for development purposes. When we discover that the current host is within
	 * that domain we force a failure without actually deleting anything.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function cleanup(): bool
	{
		$host = $this->getContainer()->get('uri')->instance()->getHost();

		if (str_ends_with($host, '.akeeba.dev') && !str_starts_with($host, 'test'))
		{
			return false;
		}

		return $this->recursiveDeleteFolder($this->getContainer()->get('paths')->get('installation'));
	}
}