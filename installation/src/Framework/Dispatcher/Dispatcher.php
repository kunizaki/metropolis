<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Dispatcher;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Steps\StepItem;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class Dispatcher implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Default view, if none specified in the input object provided by the container
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $defaultView = 'main';

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Dispatch the application
	 *
	 * @return  void
	 * @throws  \Throwable
	 * @since   10.0
	 */
	public function dispatch(): void
	{
		$input = $this->container->get('input');
		$lang  = $this->container->get('language');

		$view   = $input->getCmd('view', $this->defaultView);
		$task   = $input->getCmd('task', 'main');
		$format = $input->get('format', 'html');

		try
		{
			if (!$this->onBeforeDispatch())
			{
				throw new \RuntimeException($lang->text('FRAMEWORK_CORE_ERR_ACCESS_FORBIDDEN'), 403);
			}

			$controller = $this->container->get('mvcFactory')->controller($view);

			$controller->execute($task);

			if (!$this->onAfterDispatch())
			{
				throw new \RuntimeException($lang->text('FRAMEWORK_CORE_ERR_ACCESS_FORBIDDEN'), 403);
			}

			if ($format !== 'json')
			{
				$controller->redirect();
			}
		}
		catch (\Throwable $e)
		{
			if ($format === 'json')
			{
				@ob_clean();
				echo json_encode(['code' => '403', 'error' => $e->getMessage()]);
				exit();
			}

			throw $e;
		}
	}

	/**
	 * Returns the default view.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getDefaultView(): string
	{
		return $this->defaultView;
	}

	/**
	 * Sets the default view.
	 *
	 * @param   string  $defaultView  The default view to set.
	 *
	 * @return  self
	 * @since   10.0
	 */
	public function setDefaultView(string $defaultView): self
	{
		$this->defaultView = $defaultView;

		return $this;
	}

	/**
	 * Executes before the dispatcher tries to instantiate and run the controller.
	 *
	 * @return  bool  Return false to abort
	 * @since   10.0
	 */
	protected function onBeforeDispatch(): bool
	{
		if (!$this->checkSession())
		{
			return false;
		}

		if (!$this->passwordProtection())
		{
			return false;
		}

		if (!$this->checkCLIConfig())
		{
			$this->getContainer()->get('input')->set('view', 'clionly');

			return true;
		}

		// If there is no view, we will use the view from the current step.
		$view = $this->getContainer()->get('input')->getCmd('view', null);

		if (empty($view))
		{
			$currentStep = $this->getContainer()->get('steps')->current();

			$this->getContainer()->get('input')->set(
				'view',
				$currentStep instanceof StepItem ? $currentStep->getView() : $this->defaultView
			);

			return true;
		}

		// If there is an active view, try to set the current step from the request data.
		$this->getContainer()->get('steps')->setCurrentStepFromRequest();

		return true;
	}

	/**
	 * Executes right after the dispatcher runs the controller.
	 *
	 * @return  bool  Return false to abort
	 * @since   10.0
	 */
	protected function onAfterDispatch(): bool
	{
		return true;
	}

	/**
	 * Check if the installer is password protected.
	 *
	 * If it is, and the user has not yet entered a password, forward them to the password entry page.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function passwordProtection(): bool
	{
		// These views can be accessed without a password
		$allowedViews = ['password', 'session'];

		// Initialise
		$filePath = $this->getContainer()->get('paths')->get('installation') . '/password.php';
		$view     = $this->getContainer()->get('input')->get('view', $this->defaultView);
		$unlocked = (bool) $this->container->get('session')->get('brs.unlocked', false);

		// No password file, or already unlocked? Allow access to everything except for the `password` view.
		if (!file_exists($filePath) || $unlocked)
		{
			return $view !== 'password';
		}

		// Include the password file
		include_once $filePath;

		// If there's no password set up allow access to everything except for the `password` view.
		if (!defined('AKEEBA_PASSHASH') || trim(constant('AKEEBA_PASSHASH') ?? '') === '')
		{
			return $view !== 'password';
		}

		// If we're here there is a password. We must treat the password view as captive.
		if (!in_array($view, $allowedViews))
		{
			$this->container->get('application')->redirect('index.php?view=password');
		}

		return true;
	}

	/**
	 * Check if the session storage is working. If not, tell the user how to make it work.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function checkSession(): bool
	{
		if (!$this->container->get('session')->isStorageWorking())
		{
			$view = $this->container->get('input')->getCmd('view', $this->defaultView);

			if ($view !== 'session')
			{
				$this->container->get('application')->redirect('index.php?view=session');
			}
		}

		return true;
	}

	private function checkCLIConfig(): bool
	{
		$paths      = $this->getContainer()->get('paths');
		$configFile = $paths->get('installation') . '/config.yml.php';

		return !file_exists($configFile);
	}
}