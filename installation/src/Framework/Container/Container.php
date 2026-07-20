<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Container;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Application\AbstractApplication;
use Akeeba\BRS\Framework\Configuration\Configuration;
use Akeeba\BRS\Framework\Database\Factory as DatabaseFactory;
use Akeeba\BRS\Framework\Dispatcher\Dispatcher;
use Akeeba\BRS\Framework\Filesystem\Path;
use Akeeba\BRS\Framework\IniFiles\Parser;
use Akeeba\BRS\Framework\Input\Input;
use Akeeba\BRS\Framework\Ip\Ip;
use Akeeba\BRS\Framework\Language\Language;
use Akeeba\BRS\Framework\Mvc\Factory as MVCFactory;
use Akeeba\BRS\Framework\Provider as Provider;
use Akeeba\BRS\Framework\Registry\Registry;
use Akeeba\BRS\Framework\Session\Session;
use Akeeba\BRS\Framework\Steps\StepQueue;
use Akeeba\BRS\Framework\Template\Breadcrumbs;
use Akeeba\BRS\Framework\Uri\Factory as UriFactory;
use Akeeba\Replace\Logger\LoggerInterface;
use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface as Psr11ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * The restoration application's container.
 *
 * This is a Pimple container object, with some extra tricks to make it PSR-11 compatible, and to allow access to its
 * services via the PHP magic getter.
 *
 * @property-read  AbstractApplication $application   Application object
 * @property-read  Breadcrumbs         $breadcrumbs   Breadcrumbs
 * @property-read  Configuration       $configuration Application configuration object
 * @property-read  DatabaseFactory     $db            Database object factory service
 * @property-read  Dispatcher          $dispatcher    Application dispatcher
 * @property-read  Parser              $iniParser     INI parser service
 * @property-read  Input               $input         Application input
 * @property-read  Ip                  $ip            Visitor IP helper service
 * @property-read  Language            $language      Language service
 * @property-read  MVCFactory          $mvcFactory    MVC Factory service
 * @property-read  Registry            $paths         Absolute filesystem paths
 * @property-read  LoggerInterface     $log           Log facility
 * @property-read  Path                $path          Path validation service
 * @property-read  Session             $session       Session service
 * @property-read  StepQueue           $steps         Restoration steps
 * @property-read  UriFactory          $uri           URI parser factory service
 *
 * @since  10.0
 */
class Container extends PimpleContainer implements Psr11ContainerInterface
{
	private const DEFAULT_SERVICE_PROVIDERS = [
		'application'   => Provider\Application::class,
		'breadcrumbs'   => Provider\Breadcrumbs::class,
		'configuration' => Provider\Configuration::class,
		'db'            => Provider\DatabaseFactory::class,
		'dispatcher'    => Provider\Dispatcher::class,
		'iniParser'     => Provider\IniParser::class,
		'input'         => Provider\Input::class,
		'ip'            => Provider\Ip::class,
		'language'      => Provider\Language::class,
		'log'           => Provider\Log::class,
		'mvcFactory'    => Provider\MVCFactory::class,
		'path'          => Provider\Path::class,
		'paths'         => Provider\Paths::class,
		'session'       => Provider\Session::class,
		'steps'         => Provider\Steps::class,
		'uri'           => Provider\Uri::class,
	];

	/** @inheritDoc */
	public function __construct(array $values = [])
	{
		parent::__construct($values);

		// Register any missing services using the default providers
		foreach (self::DEFAULT_SERVICE_PROVIDERS as $key => $class)
		{
			if ($this->has($key))
			{
				continue;
			}

			$this->register(new $class);
		}
	}

	/** @inheritDoc */
	public function get(string $id)
	{
		if (!$this->has($id))
		{
			throw new NotFoundException($id);
		}

		return $this[$id];
	}

	/** @inheritDoc */
	public function has(string $id): bool
	{
		return isset($this[$id]);
	}

	/**
	 * Make services accessible as virtual properties.
	 *
	 * @param   string  $name  The service name to get
	 *
	 * @return  mixed
	 * @throws  NotFoundExceptionInterface
	 * @since   10.0
	 */
	public function __get($name)
	{
		return $this->get($name);
	}
}