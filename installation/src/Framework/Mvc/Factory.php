<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Mvc;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class Factory implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The application's namespace.
	 *
	 * @var   null|string
	 * @since 10.0
	 */
	private $namespace = null;

	/**
	 * Cache of created Singleton models.
	 *
	 * @var   array
	 * @since 10.0
	 */
	private $models = [];

	/**
	 * Cache of created Singleton controllers.
	 *
	 * @var   array
	 * @since 10.0
	 */
	private $controllers = [];

	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Return a Singleton instance of a Controller
	 *
	 * @param   string  $name    The controller name
	 * @param   array   $config  Optional configuration for the model
	 *
	 * @return  Controller
	 * @since   10.0
	 */
	public function controller(string $name, array $config = []): Controller
	{
		if (isset($this->controllers[strtolower($name)]))
		{
			return $this->controllers[strtolower($name)];
		}

		$platformClass = $this->getNamespace() . '\\Platform\\Controller\\' . ucfirst($name);
		$mainClass     = $this->getNamespace() . '\\Controller\\' . ucfirst($name);

		if (class_exists($platformClass))
		{
			return $this->controllers[strtolower($name)] = new $platformClass($this->getContainer(), $config);
		}

		if (class_exists($mainClass))
		{
			return $this->controllers[strtolower($name)] = new $mainClass($this->getContainer(), $config);
		}

		throw new \RuntimeException("Controller $name does not exist");
	}

	/**
	 * Return a Singleton instance of a Model
	 *
	 * @param   string  $name    The model name
	 * @param   array   $config  Optional configuration for the model
	 *
	 * @return  Model
	 * @since   10.0
	 */
	public function model(string $name, array $config = []): Model
	{
		if (isset($this->models[strtolower($name)]))
		{
			return $this->models[strtolower($name)];
		}

		$platformClass = $this->getNamespace() . '\\Platform\\Model\\' . ucfirst($name);
		$mainClass     = $this->getNamespace() . '\\Model\\' . ucfirst($name);

		if (class_exists($platformClass))
		{
			return $this->models[strtolower($name)] = new $platformClass($this->getContainer(), $config);
		}

		if (class_exists($mainClass))
		{
			return $this->models[strtolower($name)] = new $mainClass($this->getContainer(), $config);
		}

		throw new \RuntimeException("Model $name does not exist");
	}

	/**
	 * Temporary model instance.
	 *
	 * Temporary model instances are not Singleton, have no state, and save no state to the session.
	 *
	 * @param   string  $name
	 * @param   array   $config
	 *
	 * @return  Model
	 * @since   10.0
	 */
	public function tempModel(string $name, array $config = []): Model
	{
		$platformClass = $this->getNamespace() . '\\Platform\\Model\\' . ucfirst($name);
		$mainClass     = $this->getNamespace() . '\\Model\\' . ucfirst($name);

		$config['ignore_request'] = true;

		if (class_exists($platformClass))
		{
			return (new $platformClass($this->getContainer(), $config))->savestate(0);
		}

		if (class_exists($mainClass))
		{
			return (new $mainClass($this->getContainer(), $config))->savestate(0);
		}

		throw new \RuntimeException("Model $name does not exist");
	}

	/**
	 * Make a new View instance.
	 *
	 * @param   string  $name    The view name.
	 * @param   string  $type    The view type e.g. html, json, raw, …
	 * @param   array   $config  Optional view configuration.
	 *
	 * @return  View
	 * @since   10.0
	 */
	public function view(string $name, string $type = 'html', array $config = []): View
	{
		$type          = ucfirst(strtolower($type ?: 'html'));
		$platformClass = $this->getNamespace() . '\\Platform\\View\\' . ucfirst($name) . '\\' . $type;
		$mainClass     = $this->getNamespace() . '\\View\\' . ucfirst($name) . '\\' . $type;

		if (class_exists($platformClass))
		{
			return new $platformClass($this->getContainer(), $config);
		}

		if (class_exists($mainClass))
		{
			return new $mainClass($this->getContainer(), $config);
		}

		throw new \RuntimeException("View $name does not exist");
	}

	/**
	 * Get the namespace for the application in our container.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function getNamespace(): string
	{
		if ($this->namespace !== null)
		{
			return $this->namespace;
		}

		$parts = explode('\\', get_class($this));

		array_pop($parts);
		array_pop($parts);
		array_pop($parts);

		$this->namespace = implode('\\', $parts);

		return $this->namespace;
	}

	/**
	 * Get the namespace for the application in our container.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function getNamespaceFromApplication(): string
	{
		if ($this->namespace !== null)
		{
			return $this->namespace;
		}

		$parts = explode('\\', get_class($this->getContainer()->get('application')));

		array_pop($parts);
		array_pop($parts);

		$this->namespace = implode('\\', $parts);

		return $this->namespace;
	}
}