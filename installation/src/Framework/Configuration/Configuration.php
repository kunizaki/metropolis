<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Application Configuration service.
 *
 * The application configuration is determined by all the configuration files generated at backup time (databases.json,
 * eff.json, extrainfo.json), the platform.json file of the current BRS platform, and any detected signals from the
 * environment. The configuration is automatically stored into and retrieved from the session.
 *
 * @property-read  string       $name          Translation key for this platform.
 * @property-read  string       $type          Restoration script type, e.g. `joomla`, `wordpress`, etc.
 * @property-read  string[]     $limitDrivers  List of database drivers to limit compatibility with.
 * @property-read  Database[]   $databases     Configured databases to restore.
 * @property-read  Folders[]    $folders       Configured off-site folders to restore.
 * @property-read  PublicFolder $publicFolder  Joomla! public folder handling.
 * @property-read  ExtraInfo    $extraInfo     Extra info stored at backup time.
 * @property-read  array        $steps         Ordered list of views to follow restoring a site.
 *
 * @since  10.0
 */
final class Configuration extends AbstractConfiguration implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Translation key for this platform. Read from platform.json.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $name = '';

	/**
	 * Restoration type. Read from platform.json.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $type = 'generic';

	/**
	 * List of database drivers to limit compatibility with.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $limitDrivers = [];

	/**
	 * Configured databases to restore
	 *
	 * @var   Database[]
	 * @since 10.0
	 */
	protected $databases = [];

	/**
	 * Configured off-site folders to restore
	 *
	 * @var   Folders[]
	 * @since 10.0
	 */
	protected $folders = [];

	/**
	 * Joomla! public folder handling (Joomla! only). Automatically populated from extrainfo.json.
	 *
	 * @var   PublicFolder
	 * @since 10.0
	 */
	protected $publicFolder = null;

	/**
	 * Extra Info. Populated from extrainfo.json.
	 *
	 * @var   ExtraInfo
	 * @since 10.0
	 */
	protected $extraInfo = null;

	/**
	 * Installation steps. Do not use directly. This is used by StepQueue to create StepItem objects.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $steps = [];

	/**
	 * Constructor for the class.
	 *
	 * @param   ContainerInterface  $container  The dependency injection container.
	 * @param   array               $data       Optional data provided for initialization.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $data = [])
	{
		$this->setContainer($container);

		// Try to load from the session, or from file data
		if (empty($data))
		{
			$data = $this->loadFromSession() ?: $this->loadFromFiles();
		}

		$data = $this->recursiveToArray($data);

		parent::__construct($data);
	}

	/**
	 * Set the list of drivers the installation script is compatible with.
	 *
	 * @param   array|null  $limitDrivers
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setLimitDrivers(?array $limitDrivers): void
	{
		$this->limitDrivers = $limitDrivers ?: [];
	}

	/**
	 * Recursively converts a given source into an array.
	 *
	 * @param   mixed  $source  The input to be converted, which can be a scalar, null, array, or object.
	 *
	 * @return  mixed  Returns the converted array or retains the original scalar/null value.
	 * @since   10.0
	 */
	private function recursiveToArray($source)
	{
		if (is_scalar($source) || is_null($source))
		{
			return $source;
		}

		if (is_object($source))
		{
			return $this->recursiveToArray((array) $source);
		}

		if (!is_array($source))
		{
			return null;
		}

		return array_map([$this, 'recursiveToArray'], $source);
	}

	/**
	 * Destructor method.
	 *
	 * Saves the configuration to session for faster retrieval.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __destruct()
	{
		$this->saveToSession();
	}

	/**
	 * Setter for the type property.
	 *
	 * @param   string  $type
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setType(string $type): void
	{
		$this->type = $type;
	}

	/**
	 * Setter for the folders property.
	 *
	 * @param   Folders[]  $folders
	 *
	 * @since   10.0
	 */
	public function setFolders(array $folders): void
	{
		$this->folders = [];

		foreach ($folders as $localPath => $virtual)
		{
			if ($virtual instanceof Folders)
			{
				$this->folders[] = $virtual;

				continue;
			}

			if (is_array($virtual))
			{
				$this->folders[] = new Folders($virtual);

				continue;
			}

			if (is_object($virtual))
			{
				$this->folders[] = new Folders((array) $virtual);

				continue;
			}

			if (is_string($localPath) && !empty($localPath) && !empty($virtual))
			{
				$this->folders[] = new Folders(
					[
						'name'    => $localPath,
						'virtual' => $virtual,
					]
				);

				continue;
			}

			throw new \DomainException('Invalid folder configuration');
		}
	}

	/**
	 * Setter for the database property.
	 *
	 * @param   Database[]  $databases
	 *
	 * @since   10.0
	 */
	public function setDatabases(array $databases): void
	{
		$this->databases = [];

		foreach ($databases as $database)
		{
			if (is_array($database))
			{
				$database = new Database($database);
			}
			elseif (is_object($database) && !$database instanceof Database)
			{
				$database = new Database((array) $database);
			}

			if (!$database instanceof Database)
			{
				throw new \DomainException('Invalid database configuration');
			}

			$this->databases[basename($database->sqlfile, '.sql')] = $database;
		}
	}

	/**
	 * Setter for the publicFolder property.
	 *
	 * @param   PublicFolder|array  $publicFolder
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setPublicFolder($publicFolder): void
	{
		if (is_array($publicFolder))
		{
			$publicFolder = new PublicFolder($publicFolder);
		}

		if (!$publicFolder instanceof PublicFolder)
		{
			throw new \DomainException(
				'Invalid public folder configuration. Must be an array or an instance of PublicFolder'
			);
		}

		$this->publicFolder = $publicFolder;
	}

	/**
	 * Setter for the name property.
	 *
	 * @param   string  $name
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * Setter for the `extrainfo` property.
	 *
	 * @param   array|ExtraInfo  $extraInfo
	 *
	 * @since   10.0
	 */
	public function setExtraInfo($extraInfo): void
	{
		if (is_array($extraInfo))
		{
			$extraInfo = new ExtraInfo($extraInfo);
		}

		if (!$extraInfo instanceof ExtraInfo)
		{
			throw new \DomainException('Invalid extra info configuration');
		}

		$this->extraInfo = $extraInfo;
	}

	/**
	 * Sets the steps property.
	 *
	 * @param   array  $steps  An array of steps to be assigned.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setSteps(array $steps): void
	{
		$this->steps = $steps;
	}

	/**
	 * Save the current configuration to the session.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function saveToSession(): void
	{
		$session = $this->container->get('session');

		$session->set('configuration', $this->toArray());
		$session->saveData();
	}

	/**
	 * Load configuration data from the session.
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function loadFromSession(): array
	{
		$configuration = $this->container->get('session')->get('configuration', []);
		$configuration = is_object($configuration) ? (array) $configuration : $configuration;

		return is_array($configuration) ? $configuration : [];
	}

	/**
	 * Load configuration from configuration files.
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function loadFromFiles(): array
	{
		$extraInfo = ExtraInfo::loadExtraInfoJson(
			$this->getContainer()->get('paths')->get('installation') . '/extrainfo.json'
		);

		return array_merge(
			$this->loadPlatformJson(),
			[
				'databases' => Database::loadDatabasesJson(
					$this->getContainer()->get('paths')->get('installation') . '/sql/databases.json'
				),
			],
			[
				'folders' => Folders::loadEffJson(
					$this->getContainer()->get('paths')->get('installation') . '/eff.json'
				),
			],
			[
				'extraInfo' => $extraInfo,
			],
			[
				'publicFolder' => PublicFolder::loadFromExtrainfo(
					$extraInfo, $this->getContainer()->get('paths')->get('root')
				),
			]
		);
	}

	/**
	 * Loads and decodes the platform.json file into an associative array.
	 *
	 * If the file is not found, cannot be read, or contains invalid JSON,
	 * an empty array is returned.
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function loadPlatformJson(): array
	{
		$filePath = $this->getContainer()->get('paths')->get('platform.base') . '/platform.json';

		if (!file_exists($filePath))
		{
			return [];
		}

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

		// This may not be defined for all restoration scripts.
		$data['limitDrivers'] = $data['limitDrivers'] ?? [];

		return $data;
	}
}