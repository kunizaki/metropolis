<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Filesystem;

defined('_AKEEBA') or die();

use DirectoryIterator;

/**
 * Trait to add a recursive directory deletion feature.
 *
 * @since  10.0
 */
trait RecursiveDeleteTrait
{
	/**
	 * Recursively delete a folder and all of its contained files and folders.
	 *
	 * @param   string  $source        The absolute filesystem path of the folder to delete
	 * @param   bool    $ignoreErrors  Should I ignore all deletion errors?
	 *
	 * @return void True if the deletion worked
	 * @since   9.8.1
	 */
	private function recursiveDeleteFolder(string $source, bool $ignoreErrors = true): bool
	{
		$di = new DirectoryIterator($source);

		/** @var DirectoryIterator $item */
		foreach ($di as $item)
		{
			if ($item->isDot())
			{
				continue;
			}

			$status = true;

			if ($item->isLink())
			{
				$status = @unlink($item->getPathname()) || @rmdir($item->getPathname());
			}
			elseif ($item->isFile())
			{
				$status = @unlink($item->getPathname());
			}
			elseif ($item->isDir())
			{
				$status = $this->recursiveDeleteFolder($item->getPathname());
			}

			if (!$ignoreErrors && !$status)
			{
				return false;
			}
		}

		$di = null;

		return @rmdir($source);
	}
}