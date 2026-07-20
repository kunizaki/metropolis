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

/**
 * Abstract Model for handling the site's configuration parameters.
 *
 * @since  10.0
 */
abstract class AbstractConfiguration extends Model
{
	/**
	 * The site configuration variables
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $configvars = [];

	/**
	 * The path of the configuration filename, relative to the site's root.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $configFilename = '';

	/**
	 * Destructor. Automatically saves the configuration variables to the session.
	 */
	final public function __destruct()
	{
		if (empty($this->configvars))
		{
			return;
		}

		$this->getContainer()->get('session')->set('cms.config', $this->configvars);
	}

	/**
	 * Gets the configuration variables as an array
	 *
	 * @return  array
	 * @since   10.0
	 */
	final public function getConfigvars(): array
	{
		return $this->configvars;
	}

	/**
	 * Saves the configuration variables to the session.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function saveToSession(): void
	{
		$this->getContainer()->get('session')->set('cms.config', $this->configvars);
	}

	/**
	 * Resets the configuration variables, and removes them from the session.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function reset(): void
	{
		$this->configvars = [];
		$this->getContainer()->get('session')->remove('cms.config');
	}

	/**
	 * Gets a named configuration value.
	 *
	 * @param   string  $key      The name of the configuration variable to retrieve.
	 * @param   mixed   $default  The default value to return, if the key doesn't exist.
	 *
	 * @return  mixed  The variable's value
	 * @since   10.0
	 */
	public function get(string $key, $default = null)
	{
		if (array_key_exists($key, $this->configvars))
		{
			return $this->configvars[$key];
		}

		// The key was not found. Set it to the default value, store it, and return it.
		$this->configvars[$key] = $default;
		$this->saveToSession();

		return $default;
	}

	/**
	 * Sets a variable's value and stores the configuration array in the session.
	 *
	 * @param   string  $key    The name of the configuration variable to set.
	 * @param   mixed   $value  The value to set it to.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function set(string $key, $value): void
	{
		$this->configvars[$key] = $value;
		$this->saveToSession();
	}

	/**
	 * Remove a configuration variable from the internal storage.
	 *
	 * This method DOES NOT save to the session automatically.
	 *
	 * @param   string  $key  The name of the configuration variable to set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function remove(string $key): void
	{
		if (!array_key_exists($key, $this->configvars))
		{
			return;
		}

		unset($this->configvars[$key]);
	}

	/**
	 * Get the name of the configuration file.
	 *
	 * @return  string
	 * @since   10.0
	 */
	final public function getConfigFilename(): string
	{
		return $this->configFilename;
	}

	/**
	 * Converts a Windows path to its UNIX representation.
	 *
	 * Generally speaking, this turns backslashes into forward slashes, and collapses multiple consecutive forward
	 * slashes into a single slash. It can recognise when the path is a UNC path (it is prefixed by an SMB hostname in
	 * the form of `//hostname`), in which case the prefix will be two forward slashes.
	 *
	 * @param   string|null  $path  The path to transform
	 *
	 * @return  string
	 * @since   10.0
	 */
	final protected function TranslateWinPath(?string $path): ?string
	{
		if (is_null($path))
		{
			return $path;
		}

		$isUNC = false;

		if (DIRECTORY_SEPARATOR == '\\')
		{
			if (strpos($path, '\\') !== false)
			{
				$path = strtr($path, '\\', '/');
			}

			$isUNC = (substr($path, 0, 2) == '//');
		}

		while (strpos($path, '//') !== false)
		{
			$path = str_replace('//', '/', $path);
		}

		return ($isUNC ? '/' : '') . $path;
	}

	/**
	 * Return the contents of the configuration file as a string.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	abstract public function __toString();
}