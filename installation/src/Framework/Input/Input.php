<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Input;

defined('_AKEEBA') or die();

/**
 * Application input handling.
 *
 * @property-read  Input $get      GET variables ($_GET)
 * @property-read  Input $post     POST variables ($_POST)
 * @property-read  Input $request  Request variables ($_REQUEST)
 * @property-read  Input $server   Server environment variables ($_SERVER)
 * @property-read  Input $env      Environment variables ($_ENV, only really works in CLI)
 *
 * @method  int     getInt($name, $default = null)       Get a signed integer.
 * @method  int     getUint($name, $default = null)      Get an unsigned integer.
 * @method  float   getFloat($name, $default = null)     Get a floating-point number.
 * @method  bool    getBool($name, $default = null)      Get a boolean value.
 * @method  string  getWord($name, $default = null)      Get a word filtered string.
 * @method  string  getAlnum($name, $default = null)     Get an alphanumeric string.
 * @method  string  getCmd($name, $default = null)       Get a command filtered string.
 * @method  string  getBase64($name, $default = null)    Get a base64 encoded string.
 * @method  string  getString($name, $default = null)    Get a filtered string.
 * @method  string  getPath($name, $default = null)      Get a file path.
 * @method  string  getUsername($name, $default = null)  Get a username.
 * @method  mixed   getRaw($name, $default = null)       Get an unfiltered value.
 *
 * @since  10.0
 */
final class Input implements \Countable
{
	/**
	 * Which superglobals I can access.
	 *
	 * @var    array
	 * @since  10.0
	 */
	private const ALLOWED_GLOBALS = ['REQUEST', 'GET', 'POST', 'FILES', 'SERVER', 'ENV'];

	/**
	 * Filter object to use.
	 *
	 * @var    InputFilter
	 * @since  10.0
	 */
	protected $filter;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  10.0
	 */
	protected $data = [];

	/**
	 * Input objects.
	 *
	 * @var    Input[]
	 * @since  10.0
	 */
	protected $inputs = [];

	/**
	 * Constructor.
	 *
	 * @param   array|null        $source  Source data. NULL to use `$_REQUEST`.
	 * @param   InputFilter|null  $filter  Filter object. NULL to create a new instance of InputFilter.
	 *
	 * @since   10.0
	 */
	public function __construct(?array $source = null, ?InputFilter $filter = null)
	{
		$this->data   = $source ?? $_REQUEST;
		$this->filter = $filter ?? new InputFilter;
	}

	/**
	 * Magic getter.
	 *
	 * @param   mixed  $name  Name of the input object to retrieve.
	 *
	 * @return  Input  The request input object
	 * @since   10.0
	 */
	public function __get($name)
	{
		if (isset($this->inputs[$name]))
		{
			return $this->inputs[$name];
		}

		$className = __NAMESPACE__ . '\\' . ucfirst($name);

		if (class_exists($className))
		{
			$this->inputs[$name] = new $className(null, $this->filter);

			return $this->inputs[$name];
		}

		$superGlobal = '_' . strtoupper($name);

		if (\in_array(strtoupper($name), self::ALLOWED_GLOBALS, true) && isset($GLOBALS[$superGlobal]))
		{
			$this->inputs[$name] = new self($GLOBALS[$superGlobal], $this->filter);

			return $this->inputs[$name];
		}

		throw new \RuntimeException('Undefined Input property: ' . $name);
	}

	/**
	 * Get the number of variables.
	 *
	 * @return  int  The number of variables in the input.
	 *
	 * @since   10.0
	 */
	#[\ReturnTypeWillChange]
	public function count()
	{
		return \count($this->data);
	}

	/**
	 * Get a (filtered) input value.
	 *
	 * @param   string  $name     Input variable name.
	 * @param   mixed   $default  Default value, if variable isn't set.
	 * @param   string  $filter   Filter type to apply.
	 *
	 * @return  mixed
	 *
	 * @since   10.0
	 * @see     InputFilter::clean()
	 */
	public function get(string $name, $default = null, string $filter = 'cmd')
	{
		return $this->exists($name) ? $this->filter->clean($this->data[$name], $filter) : $default;
	}

	/**
	 * Returns the input data after applying the specified filter.
	 *
	 * @param   string  $type  Filter type to apply.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getData(string $type = 'raw'): array
	{
		// Save some CPU cycles when I am asked to return the raw data.
		if (strtolower($type) == 'raw')
		{
			return $this->data;
		}

		return array_map(
			function ($value) use ($type) {
				return $this->filter->clean($value, $type);
			}, $this->data
		);
	}

	/**
	 * Get an array of values.
	 *
	 * @param   array  $vars        An associative array of keys and filter types to apply.
	 * @param   mixed  $datasource  Array to retrieve data from, or null for default
	 *
	 * @return  mixed  The filtered input data.
	 *
	 * @since   10.0
	 */
	public function getArray(array $vars = [], $datasource = null)
	{
		if (empty($vars) && $datasource === null)
		{
			return $this->getData('string');
		}

		return array_combine(
			array_keys($vars),
			array_map(
				function ($k, $v) use ($datasource) {
					if (\is_array($v))
					{
						if ($datasource === null)
						{
							return $this->getArray($v, $this->get($k, null, 'array'));
						}

						return $this->getArray($v, $datasource[$k]);
					}

					if ($datasource === null)
					{
						return $this->get($k, null, $v);
					}

					return $this->filter->clean($datasource[$k] ?? null, $v);
				}, array_keys($vars), array_values($vars)
			)
		);

	}

	/**
	 * Get the Input instance for the current request method.
	 *
	 * @return  Input
	 * @since   10.0
	 */
	public function getInputForRequestMethod(): Input
	{
		switch (strtoupper($this->getMethod()))
		{
			case 'GET':
				return $this->get;

			case 'POST':
			case 'PUT':
			case 'PATCH':
				return $this->post;

			// HEAD, DELETE, CONNECT, OPTIONS, TRACE don't have a relevant superglobal.
			default:
				return $this;
		}
	}

	/**
	 * Sets a value, overriding the existing one.
	 *
	 * @param   string  $name   Input variable name.
	 * @param   mixed   $value  Value to set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function set(string $name, $value)
	{
		$this->data[$name] = $value;
	}

	/**
	 * Set a value, but only if there is no existing value (or if it exists, but it's NULL).
	 *
	 * @param   string  $name   Input variable name.
	 * @param   mixed   $value  Default value to set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function def(string $name, $value)
	{
		if (!$this->exists($name))
		{
			$this->set($name, $value);
		}
	}

	/**
	 * Check if an input variable name exists.
	 *
	 * @param   string  $name  Value name
	 *
	 * @return  boolean
	 * @since   10.0
	 */
	public function exists(string $name): bool
	{
		return isset($this->data[$name]);
	}

	/**
	 * Magic getter for filter types.
	 *
	 * @param   string  $name       Filter type prefixed with `get`, e.g. `getCmd` for the `cmd` filter.
	 * @param   array   $arguments  [0] The name of the variable [1] The default value.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __call($name, $arguments)
	{
		if (substr($name, 0, 3) === 'get')
		{
			return $this->get($arguments[0], $arguments[1] ?? null, substr($name, 3));
		}

		throw new \InvalidArgumentException('Undefined Input method ' . $name);
	}

	/**
	 * Returns the request's HTTP method in uppercase.
	 *
	 * @return  string   The HTTP method, e.g. `GET`.
	 * @since   10.0
	 */
	public function getMethod()
	{
		return strtoupper($this->server->getCmd('REQUEST_METHOD'));
	}
}
