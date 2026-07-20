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

use Akeeba\BRS\Framework\Mvc\Model;
use RuntimeException;

/**
 * The Model for restoring off-site directory contents.
 *
 * @since  10.0
 */
class Offsitedirs extends Model
{
	/**
	 * Get the off-site directory definitions.
	 *
	 * @param   bool  $associative
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getDirs(bool $associative = false): array
	{
		$offsiteDirs = [];

		foreach ($this->getContainer()->get('configuration')->folders as $folder)
		{
			$target  = $folder->name;
			$virtual = $folder->virtual;

			// This is Joomla-specific and handled in its own view.
			if ($virtual === 'external_files/JPATH_PUBLIC')
			{
				continue;
			}

			if (!$associative)
			{
				$offsiteDirs[] = $folder->virtual;

				continue;
			}

			$offsiteDirs[$folder->virtual] = [
				'target'  => $target,
				'virtual' => $virtual,
			];
		}

		return $offsiteDirs;
	}

	/**
	 * Move the files of a given off-site directory index.
	 *
	 * @param   string       $key        The off-site directory index.
	 * @param   string|null  $targetDir  The directory to move the content into.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function moveDir(string $key, ?string $targetDir): void
	{
		$lang  = $this->getContainer()->get('language');
		$paths = $this->getContainer()->get('paths');
		$dirs  = $this->getDirs(true);

		if (!isset($dirs[$key]))
		{
			throw new RuntimeException($lang->text('OFFSITEDIRS_VIRTUAL_DIR_NOT_FOUND'), 0);
		}

		$dir     = $dirs[$key];
		$virtual = $paths->get('root') . '/' . $dir['virtual'];
		$target  = str_replace(
			['[SITEROOT]', '[ROOTPARENT]'],
			[$paths->get('root'), realpath($paths->get('root') . '/..')],
			$targetDir ?? $dirs['target']
		);

		if (!file_exists($virtual))
		{
			throw new RuntimeException($lang->text('OFFSITEDIRS_VIRTUAL_DIR_NOT_FOUND'), 0);
		}

		if (!$this->recursiveCopy($virtual, $target))
		{
			throw new RuntimeException($lang->text('OFFSITEDIRS_VIRTUAL_COPY_ERROR'), 0);
		}
	}

	/**
	 * Recursively copies all files and directories from the source path to the destination path.
	 *
	 * @param   string  $from  The source directory path to copy files and folders from.
	 * @param   string  $to    The destination directory path to copy files and folders to.
	 *
	 * @return  bool  Returns true if the operation is successful, or false if any error occurs during the process.
	 * @since   10.0
	 */
	protected function recursiveCopy(string $from, string $to): bool
	{
		// Make sure the destination exists, and is a directory; otherwise, try to create it.
		if (!is_dir($to) && !@mkdir($to, 0755))
		{
			return false;
		}

		// Try to open the source directory.
		try
		{
			$di = new \DirectoryIterator($from);
		}
		catch (\Exception $e)
		{
			return false;
		}

		/** @var \DirectoryIterator $item */
		foreach ($di as $item)
		{
			if ($item->isDot())
			{
				continue;
			}

			if ($item->isDir())
			{
				if (!$this->recursiveCopy($item->getPathname(), $to . '/' . $item->getFilename()))
				{
					return false;
				}

				continue;
			}

			if (!copy($item->getPathname(), $to . '/' . $item->getFilename()))
			{
				return false;
			}
		}

		return true;
	}
}