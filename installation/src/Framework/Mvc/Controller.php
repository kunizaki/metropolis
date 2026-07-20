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
use Akeeba\BRS\Framework\Language\Language;
use Exception;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

defined('_AKEEBA') or die();

class Controller implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The mapped task that was performed.
	 *
	 * @var    string
	 */
	protected $doTask;

	/**
	 * Redirect message.
	 *
	 * @var    string|null
	 */
	protected $message = null;

	/**
	 * Redirect message type.
	 *
	 * @var    string
	 */
	protected $messageType = 'info';

	/**
	 * Array of class methods
	 *
	 * @var    array
	 */
	protected $methods = [];

	/**
	 * The name of the controller
	 *
	 * @var    array
	 */
	protected $name;

	/**
	 * URL for redirection.
	 *
	 * @var    string|null
	 */
	protected $redirect = null;

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string
	 */
	protected $task;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var    array
	 */
	protected $taskMap = [];

	/**
	 * The current view name; you can override it in the configuration
	 *
	 * @var string
	 */
	protected $view = '';

	/**
	 * The current layout; you can override it in the configuration
	 *
	 * @var string
	 */
	protected $layout = null;

	/**
	 * A cached copy of the class configuration parameter passed during initialisation
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Overrides the name of the default model
	 *
	 * @var string
	 */
	protected $modelName = null;

	/**
	 * Overrides the name of the default view controller
	 *
	 * @var string
	 */
	protected $viewName = null;

	/**
	 * The view controller object
	 *
	 * @var View|null
	 */
	private $viewObject = null;

	/**
	 * The model object
	 *
	 * @var Model|null
	 */
	private $modelObject = null;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  Application container
	 * @param   array               $config     Optional configuration parameters
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $config = [])
	{
		$this->setContainer($container);
		$this->populateTaskMak();

		$input        = $this->getContainer()->get('input');
		$this->view   = $config['view'] ?? $input->get('view', 'main', 'cmd');
		$this->layout = $config['layout'] ?? $input->get('layout', null, 'cmd');

		$this->registerDefaultTask($config['default_task'] ?? 'main');
		$this->setThisViewName($config['viewName'] ?? null);
		$this->setThisModelName($config['modelName'] ?? null);

		// Cache the config
		$this->config = $config;
	}

	/**
	 * Executes a given controller task.
	 *
	 * @param   string  $task  The task to execute, e.g. "browse"
	 *
	 * @return  bool|null False on execution failure
	 * @throws  Exception
	 * @since   10.0
	 */
	public function execute(string $task): ?bool
	{
		/** @var Language $lang */
		$lang = $this->getContainer()->get('language');

		$this->task    = $task ?: '__default';
		$displayedTask = $this->task === '__default' ? ($this->taskMap['__default'] ?? 'display') : $this->task;
		$doTask        = $this->taskMap[strtolower($task)] ?? null;

		if (empty($doTask))
		{
			throw new \RuntimeException($lang->sprintf('FRAMEWORK_CORE_ERR_TASK_NOT_FOUND', $displayedTask), 404);
		}

		$methodName = 'onBefore' . ucfirst($this->task);

		if (method_exists($this, $methodName) && !$this->$methodName())
		{
			return false;
		}

		$this->doTask = $doTask;
		$ret          = $this->$doTask();

		$methodName = 'onAfter' . ucfirst($this->task);

		if (method_exists($this, $methodName) && !$this->$methodName())
		{
			return false;
		}

		return $ret;
	}

	/**
	 * Default display method.
	 *
	 * @throws  Throwable
	 * @since   10.0
	 */
	public function display(): void
	{
		$view         = $this->getThisView();
		$view->task   = $this->task;
		$view->doTask = $this->doTask;

		$view->setModel($this->getThisModel(), true);
		$view->setLayout($this->layout ?? 'default');
		$view->display();
	}

	/**
	 * Default task.
	 *
	 * @since   10.0
	 */
	public function main()
	{
		$this->display();
	}

	/**
	 * Returns the default model associated with the current view
	 *
	 * @param   array  $config
	 *
	 * @return  Model The global instance of the model (singleton)
	 * @since   10.0
	 */
	public final function getThisModel(array $config = []): Model
	{
		if (!is_object($this->modelObject))
		{
			$this->modelObject = $this->getModel(ucfirst($this->modelName ?: $this->view), $config);
		}

		return $this->modelObject;
	}

	/**
	 * Returns current view object.
	 *
	 * @param   array  $config
	 *
	 * @return  View The global instance of the view object (singleton)
	 * @since   10.0
	 */
	public final function getThisView(array $config = []): View
	{
		if (!is_object($this->viewObject))
		{
			$this->viewObject = $this->getContainer()->get('mvcFactory')->view(
				ucfirst($this->viewName ?: $this->view),
				$this->getContainer()->get('input')->getCmd('format', 'html'),
				$config
			);
		}

		return $this->viewObject;
	}

	/**
	 * Set the name of the view to be used by this Controller
	 *
	 * @param   string|null  $viewName  The name of the view
	 *
	 * @since   10.0
	 */
	public function setThisViewName(?string $viewName): void
	{
		$this->viewName = $viewName;
	}

	/**
	 * Set the name of the model to be used by this Controller
	 *
	 * @param   string|null  $modelName  The name of the model
	 *
	 * @since   10.0
	 */
	public function setThisModelName(?string $modelName): void
	{
		$this->modelName = $modelName;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Model  The model.
	 * @since   10.0
	 */
	public function getModel(string $name = '', array $config = []): Model
	{
		return $this->getContainer()->get('mvcFactory')
			->model($name ?: $this->getName(), $config)
			->task($this->task);
	}

	/**
	 * Method to get the controller name
	 *
	 * The controller name is set by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the controller
	 * @since   10.0
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$parts      = explode('\\', get_class($this));
			$this->name = end($parts);
		}

		return $this->name;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return  string  The task that is being performed or was most recently performed.
	 * @since   10.0
	 */
	public function getTask(): string
	{
		return $this->task;
	}

	/**
	 * Gets the available tasks in the controller.
	 *
	 * @return  array  Array[i] of task names.
	 * @since   10.0
	 */
	public function getTasks(): array
	{
		return $this->methods;
	}

	/**
	 * Register the default task to perform if a mapping is not found.
	 *
	 * @param   string  $method  The name of the method in the derived class to perform if a named task is not found.
	 *
	 * @return  static  A controller object to support chaining.
	 * @since   10.0
	 */
	public function registerDefaultTask(string $method): Controller
	{
		$this->registerTask('__default', $method);

		return $this;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task.
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return  static  A controller object to support chaining.
	 * @since   10.0
	 */
	public function registerTask(string $task, string $method): Controller
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}

		return $this;
	}

	/**
	 * Unregister (unmap) a task in the class.
	 *
	 * @param   string  $task  The task.
	 *
	 * @return  static  This object to support chaining.
	 * @since   10.0
	 */
	public function unregisterTask(string $task): Controller
	{
		unset($this->taskMap[strtolower($task)]);

		return $this;
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  string  Previous message
	 * @since   10.0
	 */
	public function setMessage(string $text, string $type = 'message'): ?string
	{
		$previous          = $this->message;
		$this->message     = $text;
		$this->messageType = $type;

		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string       $url   URL to redirect to.
	 * @param   string|null  $msg   Message to display on redirection. Optional, defaults to value set internally by
	 *                              controller, if any.
	 * @param   string|null  $type  Message type. Optional, defaults to 'message', or the type set by a previous call to
	 *                              setMessage.
	 *
	 * @return  static   This object to support chaining.
	 * @since   10.0
	 */
	public function setRedirect(string $url, ?string $msg = null, ?string $type = null): Controller
	{
		$this->redirect = $url;

		if ($msg !== null)
		{
			$this->message = $msg;
		}

		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'info';
			}
		}
		else
		{
			$this->messageType = $type;
		}

		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  false  False if no redirect exists.
	 * @since   10.0
	 */
	public function redirect(): bool
	{
		if (!$this->redirect)
		{
			return false;
		}

		$this->getContainer()->get('application')->redirect($this->redirect, $this->message, $this->messageType);
	}

	/**
	 * Populate the task map from the controller's public methods.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function populateTaskMak(): void
	{
		// Determine the methods to exclude from the base class.
		$baseMethods = get_class_methods(self::class);

		// Get the public methods in this class using reflection.
		$refClass   = new ReflectionClass($this);
		$refMethods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);

		foreach ($refMethods as $refMethod)
		{
			$mName = $refMethod->getName();

			// Add default display method if not explicitly declared.
			if (!in_array($mName, $baseMethods) || $mName == 'display' || $mName == 'main')
			{
				$this->methods[] = strtolower($mName);

				// Auto register the methods as tasks.
				$this->taskMap[strtolower($mName)] = $mName;
			}
		}
	}
}