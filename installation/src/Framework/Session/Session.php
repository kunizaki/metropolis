<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Session;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Akeeba\BRS\Framework\Registry\Registry;
use Psr\Container\ContainerInterface;
use function serialize;

defined('_AKEEBA') or die();

/**
 * A simple, fixed session service.
 *
 * While this is enough for the purposes of restoring a backup, it is not something you should use in any kind of
 * multi-user, production application!
 *
 * @since  10.0
 */
final class Session implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Where temporary data is stored when using file storage
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $storagefile;

	/**
	 * The session data, as an associative array
	 *
	 * @var   Registry
	 * @since 10.0
	 */
	private $data;

	/**
	 * The session storage key
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $sessionkey = null;

	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container  Application container
	 *
	 * @since  10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->data      = new Registry();

		// Calculate the session key
		$isCli = $this->getContainer()->get('input') instanceof Cli;

		if ($isCli)
		{
			$this->sessionkey = hash('md5', __DIR__ . ':' . getcwd() . ':' . filemtime(__FILE__));
		}
		else
		{
			$ip               = $this->getContainer()->get('ip')->getIp();
			$scheme           = $this->getContainer()->get('uri')->instance()->getScheme();
			$serverInput      = $this->getContainer()->get('input')->server;
			$ua               = $serverInput->getRaw('HTTP_USER_AGENT');
			$serverName       = $serverInput->getRaw('SERVER_NAME');
			$this->sessionkey = hash('md5', implode(':', [$ip, $ua, $scheme, $serverName]));
		}

		$storagefile       = $this->getContainer()->get('paths')->get('tempinstall') . '/storagedata-'
		                     . $this->sessionkey . '.dat';
		$this->storagefile = $storagefile;

		if ($isCli)
		{
			$this->reset();
			$this->saveData();
		}

		$this->loadData();
	}

	/**
	 * Destructor
	 *
	 * @since  10.0
	 */
	public function __destruct()
	{
		$this->saveData();
	}

	/**
	 * Is the storage class able to save the data between page loads?
	 *
	 * @return  bool  True if everything works properly
	 * @since   10.0
	 */
	public function isStorageWorking(): bool
	{
		if (file_exists($this->storagefile))
		{
			return @is_writable($this->storagefile);
		}

		$fp = @fopen($this->storagefile, 'w');

		if ($fp === false)
		{
			return false;
		}

		@fclose($fp);
		@unlink($this->storagefile);

		return true;
	}

	/**
	 * Resets the internal storage.
	 *
	 * @since  10.0
	 */
	public function reset(): void
	{
		$this->data = new Registry();
	}

	/**
	 * Loads session data from a file or a session variable (auto detect).
	 *
	 * @since  10.0
	 */
	public function loadData(): void
	{
		$this->data = new Registry();
		$rawData    = @file_get_contents($this->storagefile);

		if (!is_string($rawData) || empty($rawData))
		{
			return;
		}

		$data = unserialize($rawData);

		$this->data = new Registry($data);
	}

	/**
	 * Saves session data.
	 *
	 * @return  bool  True if the session storage filename is set
	 * @since   10.0
	 */
	public function saveData(): bool
	{
		if (empty($this->storagefile))
		{
			return false;
		}

		$data = serialize($this->data->toString());

		if (@file_put_contents($this->storagefile, $data) === false)
		{
			return false;
		}

		return true;
	}

	/**
	 * Get all session data as an array.
	 *
	 * This is only meant to be used as a debugging aid.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getData(): array
	{
		return $this->data->toArray();
	}

	/**
	 * Sets or updates the value of a session variable
	 *
	 * @param   $key    string  The variable's name
	 * @param   $value  mixed   The value to store
	 *
	 * @since   10.0
	 */
	public function set($key, $value)
	{
		$this->data->set($key, $value);
	}

	/**
	 * Returns the value of a temporary variable
	 *
	 * @param   $key      string  The variable's name
	 * @param   $default  mixed   The default value, null if not specified
	 *
	 * @return  mixed  The variable's value
	 * @since   10.0
	 */
	public function get($key, $default = null)
	{
		return $this->data->get($key, $default);
	}

	/**
	 * Removes a variable from the storage
	 *
	 * @param   $key  string  The name of the variable to remove
	 *
	 * @since   10.0
	 */
	public function remove($key)
	{
		$this->data->remove($key);
	}

	/**
	 * Do we have a storage file for the session? If not, it means that BRS has detected another active session, i.e.
	 * someone else is using it already to restore a site. This method is used by the Dispatcher to block the request
	 * and warn the user of the issue.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasStorageFile(): bool
	{
		return !empty($this->storagefile);
	}

	/**
	 * Returns the session key file. Used to display the message in view=session&layout=blocked which is displayed when
	 * the user is trying to access BRS while someone else is already using it.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getSessionKey(): string
	{
		return $this->sessionkey;
	}

	/**
	 * Disable saving the storage data.
	 *
	 * This is used by the password view to prevent starting a new session when a password has not been entered. This
	 * way, if the installer is password-protected, a random visitor getting to the installer before the site
	 * administrator will NOT cause the administrator to be locked out of the installer, therefore won't require the
	 * administrator to have to delete the session storage files from tmp to get access to their site's installer.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function disableSave(): void
	{
		$this->storagefile = '';
	}
}