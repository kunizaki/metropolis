<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Mvc;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Akeeba\BRS\Framework\Input\InputFilter;
use Akeeba\BRS\Framework\Registry\Registry;
use Akeeba\Replace\WordPress\MVC\Input\Input;
use Psr\Container\ContainerInterface;

#[\AllowDynamicProperties]
class Model implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Should I save the model's state in the session?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $saveState = true;

	/**
	 * Are the state variables already set?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $isStateSet = false;

	/**
	 * Input variables, passed on from the controller, in an associative array
	 *
	 * @var   Input
	 * @since 10.0
	 */
	protected $input = [];

	/**
	 * The model (base) name
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $name;

	/**
	 * A state object
	 *
	 * @var    Registry
	 * @since  10.0
	 */
	protected $state;

	/**
	 * The prefix used to store variables in the session.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	private $hash = null;

	/**
	 * Public class constructor
	 *
	 * @param   ContainerInterface|null  $container  Application container
	 * @param   array                    $config
	 *
	 * @since   10.0
	 */
	public function __construct(?ContainerInterface $container = null, array $config = [])
	{
		$this->setContainer($container);

		$this->name       = $config['name'] ?? null;
		$this->state      = new Registry($config['state'] ?? null);
		$this->isStateSet = boolval($config['ignore_request'] ?? false);
	}

	/**
	 * Magic chainable state setter (virtual method access).
	 *
	 * @param   string  $name
	 * @param   mixed   $arguments
	 *
	 * @return  static
	 * @since   10.0
	 */
	public function __call($name, $arguments)
	{
		$arg1 = array_shift($arguments);

		$this->setState($name, $arg1);

		return $this;
	}

	/**
	 * Magic state getter.
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __get($name)
	{
		return $this->getState($name);
	}

	/**
	 * Magic state setter (virtual property access).
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __set($name, $value)
	{
		return $this->setState($name, $value);
	}

	/**
	 * Clear the model state.
	 *
	 * @return  static
	 * @since   10.0
	 */
	public function clearState()
	{
		$this->state = new Registry();

		return $this;
	}

	/**
	 * Returns the session prefix for this model's state.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getHash(): string
	{
		if (is_null($this->hash))
		{
			$input        = $this->getContainer()->get('input');
			$this->hash   = $input instanceof Cli
				? (strtolower($this->getName()) . '.')
				: $input->getCmd('view', $this->getName()) . '.';
		}

		return $this->hash;
	}

	/**
	 * Get the model's name.
	 *
	 * @return  string  The name of the model
	 * @since   10.0
	 */
	public function getName()
	{
		if (!empty($this->name))
		{
			return $this->name;
		}

		$parts      = explode('\\', get_class($this));
		$this->name = end($parts);

		return $this->name;
	}

	/**
	 * Get a filtered state variable
	 *
	 * @param   string|null  $key          State variable name
	 * @param   mixed        $default      Default value
	 * @param   string       $filter_type  Filter type to be applied
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function getState(?string $key = null, $default = null, string $filter_type = 'raw')
	{
		if (empty($key))
		{
			return $this->internalGetState()->toObject();
		}

		// Get the savestate status
		$value = $this->internalGetState($key);

		if (is_null($value))
		{
			$value = $this->getUserStateFromRequest($key, $key, $value, 'none', $this->saveState);

			if (is_null($value))
			{
				return $default;
			}
		}

		if (strtoupper($filter_type) == 'RAW')
		{
			return $value;
		}

		return (new InputFilter())->clean($value, $filter_type);
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 * @since   10.0
	 */
	public function setState(string $property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Sets the model state auto-save status.
	 *
	 * @param   bool  $newState  True to save the state, false to not save it.
	 *
	 * @return  static
	 * @since   10.0
	 */
	public function savestate(bool $newState)
	{
		$this->saveState = $newState;

		return $this;
	}

	/**
	 * Gets the value of a user state variable.
	 *
	 * @param   string       $key           The key of the user state variable.
	 * @param   string       $request       The name of the variable passed in a request.
	 * @param   string|null  $default       The default value for the variable if not found.
	 * @param   string       $type          Filter for the variable.
	 * @param   bool         $setUserState  Should I save the variable in the user state? Default: true.
	 *
	 * @return  mixed   The request user state.
	 * @since   10.0
	 */
	protected function getUserStateFromRequest(
		string $key, string $request, ?string $default = null, string $type = 'none', bool $setUserState = true
	)
	{
		$session      = $this->getContainer()->get('session');
		$hash         = $this->getHash();
		$oldState     = $session->get($hash . $key, null);
		$currentState = (!is_null($oldState)) ? $oldState : $default;
		$input        = $this->getContainer()->get('input');
		$newState     = $input instanceof Cli
			? null
			: $input->get($request, null, $type);

		// Save the new value only if it was set in this request
		if ($setUserState)
		{
			if ($newState !== null)
			{
				$session->set($hash . $key, $newState);
			}
			else
			{
				$newState = $currentState;
			}
		}
		elseif (is_null($newState))
		{
			$newState = $currentState;
		}

		return $newState;
	}

	/**
	 * Method to populate the model state.
	 *
	 * Do note call from getState(); it will cause a recursion.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function populateState() {}

	/**
	 * Method to get model state variables
	 *
	 * @param   string|null  $property  Optional parameter name
	 * @param   mixed        $default   Optional default value
	 *
	 * @return  mixed|Registry
	 * @since   10.0
	 */
	private function internalGetState(?string $property = null, $default = null)
	{
		if (!$this->isStateSet)
		{
			$this->populateState();

			$this->isStateSet = true;
		}

		return $property === null ? $this->state : $this->state->get($property, $default);
	}
}
