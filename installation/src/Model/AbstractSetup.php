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

use Akeeba\BRS\Framework\Database\DatabaseDriverInterface;
use Akeeba\BRS\Framework\Mvc\Model;
use Akeeba\BRS\Framework\Server\Handler;
use Akeeba\BRS\Framework\Session\Session;
use Psr\Container\ContainerInterface;

/**
 * Abstract Model for the Site Setup step.
 *
 * Each platform implements its own concrete class, taking into account platform-specific requirements for this step.
 *
 * @since  10.0
 */
abstract class AbstractSetup extends Model
{
	/**
	 * The site configuration variables model.
	 *
	 * @var   AbstractConfiguration
	 * @since 10.0
	 */
	protected $configModel;

	/**
	 * The cached copy of the state variables returned by getStateVariables.
	 *
	 * @var    null|object
	 * @since  10.0
	 */
	private $returnedStateVariables = null;

	/** @inheritdoc */
	public function __construct(?ContainerInterface $container = null, array $config = [])
	{
		parent::__construct($container, $config);

		$this->configModel = $this->getContainer()->get('mvcFactory')->tempModel('Configuration');
	}

	/**
	 * Return an object containing the configuration variables we read from the
	 * state or the request.
	 *
	 * @return  object
	 * @since   10.0
	 */
	public function getStateVariables(): object
	{
		return $this->returnedStateVariables = $this->returnedStateVariables
		                                       ?? (object) array_merge(
				$this->getSiteParamsVars(), $this->getSuperUsersVars()
			);
	}

	/**
	 * Apply the settings to the configuration file and the database
	 *
	 * @return  bool
	 * @since   10.0
	 */
	abstract public function applySettings(): bool;

	/**
	 * Are we restoring to a new host?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function isNewhost(): bool
	{
		$oldHost = $this->getContainer()->get('configuration')->extraInfo->host;
		$newHost = $this->getContainer()->get('uri')->instance()->getHost();

		return strtolower($oldHost ?? '') != strtolower($newHost ?? '');
	}

	/**
	 * Are we restoring to a different filesystem?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function isDifferentFilesystem(): bool
	{
		$oldRoot = rtrim($this->getContainer()->get('configuration')->extraInfo->root ?? '', '/\\');
		$newRoot = rtrim($this->getContainer()->get('paths')->get('root') ?? '', '/\\');

		return $oldRoot !== $newRoot;
	}

	/**
	 * Checks if current htaccess file contains an AddHandler rule
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function hasAddHandler(): bool
	{
		if (!$this->hasHtaccess())
		{
			return false;
		}

		$files = [
			'htaccess.bak',
			'.htaccess',
		];

		$publicFolder = $this->getPublicFolder();

		foreach ($files as $file)
		{
			if (!file_exists($publicFolder . '/' . $file))
			{
				continue;
			}

			$contents = file_get_contents($publicFolder . '/' . $file);

			if (stripos($contents, 'AddHandler') !== false || (stripos($contents, 'SetHandler') !== false))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has .htaccess files
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function hasHtaccess(): bool
	{
		$files = [
			'.htaccess',
			'htaccess.bak',
		];

		$publicFolder = $this->getPublicFolder();

		foreach ($files as $file)
		{
			if (file_exists($publicFolder . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has web.config files
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final public function hasWebconfig(): bool
	{
		$files = [
			'web.config',
			'web.config.bak',
		];

		$publicFolder = $this->getPublicFolder();

		foreach ($files as $file)
		{
			if (file_exists($publicFolder . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if current .htaccess file has Kickstart tags about PHP Handlers
	 *
	 * @return  false|string
	 * @since   10.0
	 */
	final protected function getKickstartTagContents()
	{
		$publicFolder = $this->getPublicFolder();

		if (!file_exists($publicFolder . '/.htaccess'))
		{
			return false;
		}

		$contents = file_get_contents($publicFolder . '/.htaccess');

		if (!$contents)
		{
			return false;
		}

		$startPos = stripos($contents, '### AKEEBA_KICKSTART_PHP_HANDLER_BEGIN ###');

		// No open marker? No need to continue then
		if ($startPos === false)
		{
			return false;
		}

		$endPos = stripos($contents, '### AKEEBA_KICKSTART_PHP_HANDLER_END ###', $startPos);

		// No ending marker??? Abort Abort!
		if ($endPos === false)
		{
			return false;
		}

		$handlerRules = substr($contents, $startPos + 42, $endPos - ($startPos + 42));
		$handlerRules = trim($handlerRules);

		// Sanity check on resulting value
		if (strlen($handlerRules) < 10)
		{
			return false;
		}

		return $handlerRules;
	}

	/**
	 * Return the basic site parameters.
	 *
	 * @return  array
	 * @since   10.0
	 */
	abstract protected function getSiteParamsVars(): array;

	/**
	 * Return information about the most privileged administrative users of the site.
	 *
	 * @return  array
	 * @since   10.0
	 */
	abstract protected function getSuperUsersVars(): array;

	/**
	 * Returns the database connection variables for the default database.
	 *
	 * @return  null|object
	 * @since   10.0
	 */
	final protected function getDbConnectionVars(): ?object
	{
		/** @var Database $model */
		$model      = $this->getContainer()->get('mvcFactory')->model('Database');
		$keys       = $model->getDatabaseNames();
		$firstDbKey = array_shift($keys);

		return $model->getDatabaseInfo($firstDbKey);
	}

	/**
	 * Shorthand method to get the connection to the current database
	 *
	 * @return  DatabaseDriverInterface
	 * @since   10.0
	 */
	final protected function getDatabase(): DatabaseDriverInterface
	{
		/** @var Database $model */
		$model      = $this->getContainer()->get('mvcFactory')->model('Database');
		$keys       = $model->getDatabaseNames();
		$firstDbKey = array_shift($keys);

		return $model->getDb($firstDbKey);
	}

	/**
	 * Removes password protection from a folder
	 *
	 * @param   string  $folder
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final protected function removeHtpasswd(string $folder): bool
	{
		if (!$this->hasHtpasswd())
		{
			return true;
		}

		$files = [
			'.htaccess',
			'.htpasswd',
		];

		foreach ($files as $file)
		{
			$absolutePath = $folder . '/' . $file;

			if (file_exists($absolutePath))
			{
				@unlink($absolutePath);
			}
		}

		return true;
	}

	/**
	 * Reads specified file and fetches *Handler rules
	 *
	 * @param   string  $targetFile
	 *
	 * @return  string
	 * @since   10.0
	 */
	final protected function getHandlerRules(string $targetFile): ?string
	{
		if (!file_exists($targetFile))
		{
			return '';
		}

		$contents = file_get_contents($targetFile);

		return Handler::extractHandler($contents);
	}

	/**
	 * Replace the .htaccess file with the default for this platform
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function replaceHtaccess(): void
	{
		// This method is meant to be implemented by each BRS platform.
	}

	/**
	 * Applies the .htaccess handling preferences
	 *
	 * @param   string  $htaccessHandling
	 *
	 * @return  void
	 * @since   10.0
	 */
	final protected function applyHtaccessHandling(string $htaccessHandling = 'none'): void
	{
		switch (strtolower($htaccessHandling))
		{
			// No change to the .htaccess
			case 'none':
			default:
				break;

			// Replace the .htaccess file with the Joomla default
			case 'default':
				$this->replaceHtaccess();
				break;

			// Remove PHP handlers
			case 'removehandler':
				$this->removeAddHandler();
				break;

			// Replace PHP handlers
			case 'replacehandler':
				$this->replaceAddHandler();

				break;
		}
	}

	/**
	 * Removes the Add/SetHandler block(s)
	 *
	 * @return  void
	 * @since   10.0
	 */
	final protected function removeAddHandler(): void
	{
		// Nothing to do? Let's stop here
		if (!$this->hasAddHandler())
		{
			return;
		}

		$files = [
			'htaccess.bak',
			'.htaccess',
		];

		$publicFolder = $this->getPublicFolder();

		foreach ($files as $file)
		{
			$this->updateHandlerRules('', $publicFolder . '/' . $file);
		}
	}

	/**
	 * Replaces the Add/SetHandler block(s)
	 *
	 * @return  void
	 * @since   10.0
	 */
	final protected function replaceAddHandler()
	{
		// Do I have to fetch any *Handler rules from Kickstart or current .htaccess?
		$newRules = $this->getKickstartTagContents();

		$publicFolder = $this->getPublicFolder();

		if (!$newRules)
		{
			$newRules = $this->getHandlerRules($publicFolder . '/.htaccess');
		}

		$newRules = $newRules ?: '';

		$files = [
			'htaccess.bak',
			'.htaccess',
		];

		foreach ($files as $file)
		{
			$this->updateHandlerRules($newRules, $publicFolder . '/' . $file);
		}
	}

	/**
	 * Replaces *Handler rules with new ones
	 *
	 * @param   string  $newValues   New values that should be placed
	 * @param   string  $targetFile  File to be updated
	 *
	 * @return  bool
	 * @since   10.0
	 */
	final protected function updateHandlerRules(string $newValues, string $targetFile): bool
	{
		if (!file_exists($targetFile))
		{
			return true;
		}

		$contents   = file_get_contents($targetFile);
		$old_values = Handler::extractHandler($contents);

		if (!$old_values)
		{
			if (empty($newValues))
			{
				return true;
			}

			$new_data = $contents . "\n\n" . $newValues;
		}
		else
		{
			$new_data = str_replace($old_values, $newValues, $contents);
		}

		return @file_put_contents($targetFile, $new_data);
	}

	/**
	 * Returns the session object.
	 *
	 * @return  Session
	 * @since   10.0
	 */
	final protected function getSession(): Session
	{
		return $this->getContainer()->get('session');
	}

	/**
	 * Returns the public folder of the site.
	 *
	 * For non-Joomla! sites, and for Joomla versions before 5.0 this is always the site's web root. For Joomla! 5.0
	 * and later versions it usually is the site's root, _unless_ a different folder has been explicitly defined in
	 * the Public Folder step of the restoration.
	 *
	 * @return  string
	 * @since   10.0
	 */
	final protected function getPublicFolder(): string
	{
		return $this->getContainer()->get('session')->get(
			'joomla.public_folder', $this->getContainer()->get('paths')->get('root')
		);
	}

}