<?php
/**
 * @package   akeebabackup
 * @copyright Copyright 2006-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Api\Controller;

\defined('_JEXEC') || die;

use Akeeba\Component\AkeebaBackup\Administrator\Mixin\AkeebaEngineTrait;
use Akeeba\Component\AkeebaBackup\Administrator\Mixin\RunPluginsTrait;
use Akeeba\Component\AkeebaBackup\Api\View\Api\JsonView;
use Akeeba\Component\AkeebaBackup\Site\Model\Json\Task;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Util\Complexify;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\ApiController as BaseApiController;
use Joomla\CMS\Plugin\PluginHelper;
use RuntimeException;
use Throwable;

class ApiController extends BaseApiController
{
	use AkeebaEngineTrait;
	use RunPluginsTrait;

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  9.6.0
	 */
	protected $default_view = 'api';

	/**
	 * Secret Key (cached for quicker retrieval)
	 *
	 * @var   null|string
	 * @since 7.4.0
	 */
	private ?string $key = null;

	/**
	 * The API method handler
	 *
	 * @var   Task|null
	 * @since 9.6.0
	 */
	private ?Task $taskHandler = null;

	/**
	 * @inheritDoc
	 */
	public function execute($task)
	{
		// We only allow one task
		return parent::execute('simpleDisplay');
	}

	/**
	 * Handles a JSON API request
	 *
	 * @return  $this
	 * @throws  \Exception
	 * @since   9.6.0
	 *
	 * @noinspection PhpUnused
	 */
	public function simpleDisplay(): self
	{
		$view   = $this->getMyView();

		try
		{
			$this->akeebaEngineInitialisation();
			$this->authenticate();

			$method            = $this->input->get('method');
			$data              = $this->getRequestData();
			$this->taskHandler = Task::getInstance($this->factory);

			$view->data = $this->taskHandler->execute($method, $data);
		}
		catch (Throwable $e)
		{
			$view->data = $e;
		}

		$view->display();

		return $this;
	}

	/**
	 * Get the request data as an array
	 *
	 * @return  array
	 * @since   9.6.0
	 */
	protected function getRequestData(): array
	{
		switch ($this->input->getMethod() ?? 'GET')
		{
			case 'GET':
				$input = $this->input->get;
				break;

			default:
			case 'POST':
				$input = $this->input->post;
				break;
		}

		$cleanedData = $input->getArray();
		$data        = [];

		foreach ($cleanedData as $key => $value)
		{
			if (is_array($value))
			{
				$data[$key] = $input->get($key, [], 'array');
			}
			else
			{
				$data[$key] = $input->get($key, null, 'raw');
			}
		}

		return $data;
	}

	/**
	 * Get the BackupApi view object
	 *
	 * @return  JsonView
	 * @since   9.6.0
	 */
	protected function getMyView(): JsonView
	{
		static $view;

		if (empty($view))
		{
			$viewType   = $this->app->getDocument()->getType();
			$viewName   = $this->input->get('view', $this->default_view);
			$viewLayout = $this->input->get('layout', 'default', 'string');

			try
			{
				$view = $this->getView(
					$viewName,
					$viewType,
					'',
					['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
				);
			}
			catch (\Exception $e)
			{
				throw new RuntimeException($e->getMessage());
			}

			$view->document = $this->app->getDocument();
		}

		return $view;
	}

	/**
	 * Verifies the Secret Key (API token)
	 *
	 * @return  bool
	 * @since   7.4.0
	 */
	private function verifyKey(): bool
	{
		$cParams = ComponentHelper::getParams('com_akeebabackup');

		// Is the JSON API enabled?
		if ($cParams->get('jsonapi_enabled', 0) != 1)
		{
			return false;
		}

		// Is the key secure enough?
		$validKey = $this->serverKey();

		if (empty($validKey) || empty(trim($validKey)) || !Complexify::isStrongEnough($validKey, false))
		{
			return false;
		}

		/**
		 * Get the API authentication token. There are two sources
		 * 1. X-Akeeba-Auth header (preferred, overrides all others)
		 * 2. the _akeebaAuth GET parameter
		 */
		$authSource = $this->input->server->getString('HTTP_X_AKEEBA_AUTH', null);

		if (is_null($authSource))
		{
			$authSource = $this->input->get->getString('_akeebaAuth', null);
		}

		// No authentication token? No joy.
		if (empty($authSource) || !is_string($authSource) || empty(trim($authSource)))
		{
			return false;
		}

		return hash_equals($validKey, $authSource);
	}

	/**
	 * Get the server key, i.e. the Secret Word for the front-end backups and JSON API
	 *
	 * @return  mixed
	 *
	 * @since   7.4.0
	 */
	private function serverKey()
	{
		if (is_null($this->key))
		{
			$this->key = Platform::getInstance()->get_platform_configuration_option('frontend_secret_word', '');
		}

		return $this->key;
	}

	/**
	 * Initialises the Akeeba Engine
	 *
	 * @return  void
	 * @since   9.6.0
	 */
	private function akeebaEngineInitialisation(): void
	{
		$this->loadAkeebaEngine();
		Platform::getInstance()->load_version_defines();

		if (!defined('AKEEBA_BACKUP_ORIGIN'))
		{
			define('AKEEBA_BACKUP_ORIGIN', 'json');
		}
	}

	/**
	 * Performs user authentication
	 *
	 * @return  void
	 * @since   9.6.0
	 */
	private function authenticate(): void
	{
		// API v3 authentication: only Joomla API authentication
		if (
			$this->input->getInt('_apiVersion', 0) == 3
			&& !$this->app->login(['username' => ''], ['silent' => true, 'action' => 'core.login.api'])
		)
		{
			throw new RuntimeException('Access denied', 503);
		}

		// API v2 authentication: Akeeba Backup Secret Key _or_ Joomla API authentication
		if (
			!$this->verifyKey()
			&& !$this->protectedLogin(['username' => ''], ['silent' => true, 'action' => 'core.login.api'])
		)
		{
			throw new RuntimeException('Access denied', 503);
		}
	}

	/**
	 * Login a user without trigger the login failure plugins.
	 *
	 * @param   array  $credentials  Array('username' => string, 'password' => string)
	 * @param   array  $options      Array('remember' => boolean)
	 *
	 * @return  boolean|\Exception  True on success, false if failed or silent handling is configured, or a \Exception object on authentication error.
	 *
	 * @since   9.6.0
	 */
	private function protectedLogin($credentials, $options = [])
	{
		$app = $this->app;

		// Get the global Authentication object.
		$authenticate = Authentication::getInstance('api-authentication');
		$response     = $authenticate->authenticate($credentials, $options);

		// Import the user plugin group.
		PluginHelper::importPlugin('user');

		if ($response->status === Authentication::STATUS_SUCCESS) {
			/*
			 * Validate that the user should be able to log in (different to being authenticated).
			 * This permits authentication plugins blocking the user.
			 */
			$authorisations = $authenticate->authorise($response, $options);
			$denied_states  = Authentication::STATUS_EXPIRED | Authentication::STATUS_DENIED;

			foreach ($authorisations as $authorisation) {
				if ((int) $authorisation->status & $denied_states) {
					// If silent is set, just return false.
					return false;
				}
			}

			// OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
			$results = $this->triggerPluginEvent('onUserLogin', [(array) $response, $options]);

			/*
			 * If any of the user plugins did not successfully complete the login routine
			 * then the whole method fails.
			 *
			 * Any errors raised should be done in the plugin as this provides the ability
			 * to provide much more information about why the routine may have failed.
			 */
			$user = Factory::getApplication()->getIdentity();

			if ($response->type === 'Cookie')
			{
				if (method_exists($user, 'set'))
				{
					/** @noinspection PhpDeprecationInspection */
					$user->set('cookieLogin', true);
				}
				else
				{
					$user->cookieLogin = true;
				}
			}

			if (\in_array(false, $results, true) == false) {
				$options['user']         = $user;
				$options['responseType'] = $response->type;

				// The user is successfully logged in. Run the after login events
				$this->triggerPluginEvent('onUserAfterLogin', [$options]);

				return true;
			}
		}

		return false;
	}


}