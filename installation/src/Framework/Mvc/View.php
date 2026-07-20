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
use Akeeba\BRS\Framework\Document\ScriptAwareDocumentInterface;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Throwable;

defined('_AKEEBA') or die();

/**
 * View controller.
 *
 * @since  10.0
 */
#[\AllowDynamicProperties]
class View implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The name of the view
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $name = null;

	/**
	 * Registered models
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $models = [];

	/**
	 * The base path of the view
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $basePath = null;

	/**
	 * The default model
	 *
	 * @var      string
	 * @since 10.0
	 */
	protected $defaultModel = null;

	/**
	 * Layout name
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $layout = 'default';

	/**
	 * Layout extension
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $layoutExt = 'php';

	/**
	 * The set of search directories for resources (templates)
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $path = ['template' => []];

	/**
	 * The name of the default template source file.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $template = null;

	/**
	 * The output of the template script.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $output = null;

	/**
	 * A cached copy of the configuration
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $config = [];

	/**
	 * Constructor.
	 *
	 * The config array can contain the following keys:
	 *
	 * - `name`: view name, defaults to class name
	 * - `escape`: function name to escape strings
	 * - `base_path`: parent path of ViewTemplates directory
	 * - `template_plath`: view templates directory
	 * - `layout`: the layout to display the view
	 *
	 * @param   ContainerInterface|null  $container  The application container
	 * @param   array                    $config     A named configuration array for object construction.
	 *
	 * @since   10.0
	 */
	public function __construct(?ContainerInterface $container = null, array $config = [])
	{
		$this->setContainer($container);

		$paths = $this->getContainer()->get('paths');
		$input = $this->container->get('input');

		// View name
		$parts = explode('\\', get_class($this));
		array_pop($parts);
		$defaultViewName = end($parts);
		$config['view']  = $config['view'] ?? $input->getCmd('view', $defaultViewName);
		$input->set('view', $config['view']);

		// Internal view name
		$this->name     = $config['name'] ?? $config['view'];
		$config['name'] = $this->name;

		// Input object
		$config['input'] = $input;

		// Set the base path
		$this->basePath = $config['base_path'] ?? $paths->get('installation');

		// Set the template paths: configured or default path (goes to bottom), platform (goes to top)
		$this->addTemplatePath(
			$config['template_path'] ?? ($this->basePath . '/ViewTemplates/' . $this->getName())
		);
		$this->addTemplatePath($this->basePath . '/platform/ViewTemplates/' . $this->getName());

		// Set the layout
		$this->setLayout($config['layout'] ?? 'default');

		// Cache the config
		$this->config = $config;

		// Set the base URL (non-configurable)
		$this->baseUrl = $this->getContainer()->get('uri')->base(true);
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  string  The escaped value.
	 * @since   10.0
	 */
	public function escape($var): string
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get data from a registered model, or a property of the view
	 *
	 * @param   string       $property  The name of the method to call on the model, or the property to get
	 * @param   string|null  $default   The name of the model to reference, or the default value [optional]
	 *
	 * @return  mixed  The return value of the method
	 * @since   10.0
	 */
	public function get(string $property, ?string $default = null)
	{
		$model = is_null($default) ? $this->defaultModel : strtolower($default);

		if (!isset($this->models[$model]))
		{
			return $default;
		}

		$method = 'get' . ucfirst($property);

		if (!method_exists($this->models[$model], $method))
		{
			return $default;
		}

		return $this->models[$model]->$method();
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string|null  $name  The name of the model (optional)
	 *
	 * @return  Model
	 * @since   10.0
	 */
	public function getModel(?string $name = null): Model
	{
		return $this->models[strtolower($name ?? $this->defaultModel)];
	}

	/**
	 * Get the layout.
	 *
	 * @return  string  The layout name
	 * @since   10.0
	 */
	public function getLayout(): string
	{
		return $this->layout;
	}

	/**
	 * Sets the layout name to use
	 *
	 * @param   string  $layout  The layout name
	 *
	 * @return  string  Previous value.
	 * @since   10.0
	 */
	public function setLayout(string $layout): string
	{
		$previous     = $this->layout;
		$this->layout = $layout;

		return $previous;
	}

	/**
	 * Get the view name.
	 *
	 * The model name by default parsed using the classname, or it can be set by passing a $config['name'] in the class
	 * constructor.
	 *
	 * @return  string  The name of the model
	 *
	 * @throws  Exception
	 * @since   10.0
	 */
	public function getName()
	{
		if (!empty($this->name))
		{
			return $this->name;
		}

		$parts = explode('\\', get_class($this));

		return $this->name = strtolower(end($parts));
	}

	/**
	 * Add a model to the view.
	 *
	 * @param   Model  $model    The model to add to the view.
	 * @param   bool   $default  Is this the default model?
	 *
	 * @return  Model The added model.
	 * @since   10.0
	 */
	public function setModel(Model $model, bool $default = false): Model
	{
		$name                = strtolower($model->getName());
		$this->models[$name] = $model;
		$this->defaultModel  = $default ? $name : $this->defaultModel;

		return $model;
	}

	/**
	 * Set a different extension for the layout files.
	 *
	 * @param   string  $value  The extension.
	 *
	 * @return  string   Previous value
	 * @since   10.0
	 */
	public function setLayoutExt(string $value): string
	{
		$previous = $this->layoutExt;

		if ($value = preg_replace('#[^A-Za-z0-9]#', '', trim($value)))
		{
			$this->layoutExt = $value;
		}

		return $previous;
	}

	/**
	 * Adds to the stack of view script paths in LIFO order.
	 *
	 * @param   mixed  $path  A directory path or an array of paths.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function addTemplatePath($path)
	{
		$this->addPath('template', $path);
	}

	/**
	 * Loads a template given any path.
	 *
	 * @param   string  $path         The template path in the format `viewname/templatename`.
	 * @param   array   $forceParams  A hash array of variables to be extracted in the local scope of the template file
	 *
	 * @return  RuntimeException|string
	 * @since   10.0
	 */
	public function loadAnyTemplate(string $path = '', array $forceParams = [])
	{
		$template = $this->getContainer()->get('application')->getTemplate();

		// Parse the path
		$templateParts = $this->parseTemplatePath($path);

		// Get the default paths
		$paths = array_merge(
			[
				$this->basePath . '/template/' . $template . '/override/' . $templateParts['view'],
				$this->basePath . '/platform/ViewTemplates/' . $templateParts['view'],
				$this->basePath . '/ViewTemplates/' . $templateParts['view'],
			],
			$this->path['template']
		);

		$filetofind          = $templateParts['template'] . '.php';
		$this->_tempFilePath = $this->getContainer()->get('path')->find($paths, $filetofind);

		if (!$this->_tempFilePath)
		{
			return new RuntimeException(
				$this->getContainer()->get('language')->sprintf(
					'FRAMEWORK_CORE_ERR_LAYOUTFILE_NOT_FOUND', $path
				),
				500
			);
		}

		// Unset from local scope
		unset($template);
		unset($paths);
		unset($path);
		unset($filetofind);

		// Never allow a 'this' property
		if (isset($this->this))
		{
			unset($this->this);
		}

		if (!empty($forceParams))
		{
			extract($forceParams);
		}

		ob_start();

		include $this->_tempFilePath;

		$this->output = ob_get_contents();

		ob_end_clean();

		return $this->output;
	}

	/**
	 * Overrides the default method to execute and display a template script.
	 * Instead of loadTemplate is uses loadAnyTemplate.
	 *
	 * @param   string|null  $tpl  The name of the template file to parse
	 *
	 * @return  bool|void
	 * @since   10.0
	 */
	public function display(?string $tpl = null)
	{
		$doc = $this->getContainer()->get('application')->getDocument();

		if ($doc instanceof ScriptAwareDocumentInterface)
		{
			$doc
				->addScriptOptions(
					'brs.system',
					[
						'base_url' => $this->getContainer()->get('uri')->base() . 'index.php',
						'current_url' => $this->getContainer()->get('uri')->current()
					]
				)
				->addScriptOptions(
					'akeeba.System.params.AjaxURL',
					$this->getContainer()->get('uri')->base() . 'index.php'
				);
		}

		$method = 'onBefore' . ucfirst($this->doTask);

		if (method_exists($this, $method) && !$this->$method())
		{
			return false;
		}

		$result = $this->loadTemplate($tpl);

		$method = 'onAfter' . ucfirst($this->doTask);

		if (method_exists($this, $method) && !$this->$method())
		{
			return false;
		}

		if ($result instanceof Exception)
		{
			throw $result;
		}

		echo $result;
	}

	/**
	 * Our function uses loadAnyTemplate to provide smarter view template loading.
	 *
	 * @param   string|null  $tpl     The name of the template file to parse
	 * @param   boolean      $strict  Should we use strict naming, i.e. force a non-empty $tpl?
	 *
	 * @return  string|Throwable  A string if successful, otherwise an Exception
	 * @since   10.0
	 */
	public function loadTemplate(?string $tpl = null, bool $strict = false)
	{
		$basePath = $this->config['view'] . '/';

		if ($strict)
		{
			$paths = [
				$basePath . $this->getLayout() . ($tpl ? "_$tpl" : ''),
				$basePath . 'default' . ($tpl ? "_$tpl" : ''),
			];
		}
		else
		{
			$paths = [
				$basePath . $this->getLayout() . ($tpl ? "_$tpl" : ''),
				$basePath . $this->getLayout(),
				$basePath . 'default' . ($tpl ? "_$tpl" : ''),
				$basePath . 'default',
			];
		}

		foreach ($paths as $path)
		{
			$result = $this->loadAnyTemplate($path);

			if (!$result instanceof Exception)
			{
				break;
			}
		}

		return $result;
	}

	/**
	 * Sets an entire array of search paths for templates or resources.
	 *
	 * @param   string        $type  The type of path to set, typically 'template'.
	 * @param   string|array  $path  The new search path, or an array of search paths.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function setPath(string $type, $path): void
	{
		$this->path[$type] = [];

		$this->addPath($type, $path);
	}

	/**
	 * Adds to the search path for templates.
	 *
	 * @param   string  $type  The type of path to add.
	 * @param   mixed   $path  The directory or stream, or an array of either, to search.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function addPath($type, $path)
	{
		settype($path, 'array');

		foreach ($path as $dir)
		{
			$dir = trim($dir);

			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				$dir .= DIRECTORY_SEPARATOR;
			}

			array_unshift($this->path[$type], $dir);
		}
	}

	private function parseTemplatePath(string $path = ''): ?array
	{
		$parts = [
			'view'     => $this->config['view'],
			'template' => 'default',
		];

		if (empty($path))
		{
			return null;
		}

		$pathparts = explode('/', $path, 2);

		switch (count($pathparts))
		{
			case 2:
				$parts['view'] = array_shift($pathparts);
			// DO NOT BREAK!

			case 1:
				$parts['template'] = array_shift($pathparts);
				break;
		}

		return $parts;
	}
}