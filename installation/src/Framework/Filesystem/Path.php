<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Filesystem;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Exception;
use Psr\Container\ContainerInterface;

/**
 * Path validation service
 *
 * @since  10.0
 */
final class Path implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The container instance used to set up dependencies.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Function to strip additional path separators in a path name.
	 *
	 * @param   string  $path  The path to clean.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  The cleaned path.
	 * @since   10.0
	 */
	public function clean(string $path, string $ds = DIRECTORY_SEPARATOR): string
	{
		$path = trim($path);

		if (empty($path))
		{
			$path = $this->getContainer()->get('paths')->get('root');
		}
		else
		{
			// Remove double slashes and backslashes and convert all slashes and backslashes to DIRECTORY_SEPARATOR
			$path = preg_replace('#[/\\\\]+#', $ds, $path);
		}

		return $path;
	}

	/**
	 * Checks for snooping outside the file system root.
	 *
	 * @param   string  $path  A file system path to check.
	 * @param   string  $ds    Directory separator (optional).
	 *
	 * @return  string  A cleaned version of the path or exit on error.
	 * @throws  Exception
	 * @since   10.0
	 */
	public function check(string $path, string $ds = DIRECTORY_SEPARATOR): string
	{
		$rootPath = $this->getContainer()->get('paths')->get('root');

		if (strpos($path, '..') !== false)
		{
			// Don't translate
			throw new Exception(__CLASS__ . '::check Use of relative paths not permitted', 20);
		}

		$path = self::clean($path);

		if ($rootPath != '' && strpos($path, self::clean($rootPath)) !== 0)
		{
			// Don't translate
			throw new Exception(__CLASS__ . '::check Snooping out of bounds @ ' . $path, 20);
		}

		return $path;
	}

	/**
	 * Searches the directory paths for a given file.
	 *
	 * @param   string|array<string>  $paths  An path string or array of path strings to search in
	 * @param   string                $file   The file name to look for.
	 *
	 * @return  string|false   The full path and file name for the target file, or boolean false if the file is not
	 *                         found in any of the paths.
	 * @since   10.0
	 */
	public function find($paths, string $file)
	{
		//force to array
		settype($paths, 'array');

		// Start looping through the path set
		foreach ($paths as $path)
		{
			// Get the path to the file
			$fullname = $path . '/' . $file;

			// Is the path based on a stream?
			if (strpos($path, '://') === false)
			{
				// Not a stream, so do a realpath() to avoid directory traversal attempts on the local file system.
				$path     = realpath($path); // needed for substr() later
				$fullname = realpath($fullname);
			}

			/**
			 * The substr() check added to make sure that the realpath() results in a directory registered so that
			 * non-registered directories are not accessible via directory traversal attempts.
			 */
			if (file_exists($fullname) && substr($fullname, 0, strlen($path)) == $path)
			{
				return $fullname;
			}
		}

		// Could not find the file in the set of paths
		return false;
	}
}