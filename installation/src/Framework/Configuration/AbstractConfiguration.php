<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

defined('_AKEEBA') or die();

/**
 * Abstract configuration container.
 *
 * @since  10.0
 */
abstract class AbstractConfiguration
{
	/**
	 * Is this an immutable instance?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $_immutable = true;

	/**
	 * Constructor method for initializing object properties with provided data.
	 *
	 * @param   array  $data  An associative array containing property names as keys and their corresponding values.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(array $data, $immutable = true)
	{
		$this->_immutable = $immutable;

		foreach ($data as $key => $value)
		{
			if (!property_exists($this, $key))
			{
				continue;
			}

			$methodName = 'set' . ucfirst($key);

			if (method_exists($this, $methodName))
			{
				$this->$methodName($value);

				continue;
			}

			$methodName = $this->camelize('get_' . ucfirst($key));

			if (method_exists($this, $methodName))
			{
				$this->$methodName($value);

				continue;
			}

			$this->{$key} = $value;
		}
	}

	/**
	 * Magic method to retrieve the value of an inaccessible or undefined property.
	 *
	 * @param   string  $name  The name of the property to retrieve.
	 *
	 * @return  mixed  The value of the property or the result of a getter method if defined.
	 * @throws  \InvalidArgumentException  If the property or getter method does not exist.
	 * @since   10.0
	 */
	public function __get($name)
	{
		$methodName = 'get' . ucfirst($name);

		if (method_exists($this, $methodName))
		{
			return $this->$methodName();
		}

		$methodName = $this->camelize('get_' . ucfirst($name));

		if (method_exists($this, $methodName))
		{
			return $this->$methodName();
		}

		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		throw new \InvalidArgumentException('Undefined configuration option ' . $name);
	}

	/**
	 * Magic method for setting the value of inaccessible or undefined properties.
	 *
	 * @param   string  $name   The name of the property to set.
	 * @param   mixed   $value  The value to assign to the property.
	 *
	 * @return  void
	 * @throws  \InvalidArgumentException  If the property does not exist or no setter method is defined.
	 * @since   10.0
	 */
	public function __set($name, $value)
	{
		if ($this->_immutable)
		{
			throw new \RuntimeException(
				'Cannot set configuration option ' . $name . ' because this instance is immutable'
			);
		}

		$methodName = 'set' . ucfirst($name);

		if (method_exists($this, $methodName))
		{
			$this->$methodName($value);

			return;
		}

		$methodName = $this->camelize('get_' . ucfirst($name));

		if (method_exists($this, $methodName))
		{
			$this->$methodName($value);

			return;
		}

		if (property_exists($this, $name))
		{
			$this->$name = $value;
		}

		throw new \InvalidArgumentException('Undefined configuration option ' . $name);
	}

	/**
	 * Converts the object's properties into an associative array.
	 *
	 * Properties that are scalar values will be included directly. Properties that
	 * are instances of AbstractConfiguration will be converted to arrays using their
	 * respective toArray method. Specific properties may be excluded from the resulting
	 * array based on predefined criteria.
	 *
	 * @return  array  An associative array representation of the object's properties.
	 * @since   10.0
	 */
	public function toArray(): array
	{
		$source = $source ?? $this;
		$keys   = array_keys(get_object_vars($source));
		$ret    = [];

		foreach ($keys as $key)
		{
			if ($key === 'container' || substr($key, 0, 1) == '_')
			{
				continue;
			}

			$ret[$key] = $this->convertValue($source->$key);
		}

		return $ret;
	}

	/**
	 * Converts a provided value into an appropriate format based on its type.
	 *
	 * @param   mixed  $source  The source value to be converted. It can be a scalar, array, object, or other type.
	 *
	 * @return  mixed  The converted value based on type. Returns the source if scalar, a mapped array if array,
	 *                 an array representation if an instance of AbstractConfiguration, an array cast if an object,
	 *                 or null for resources or null values.
	 */
	protected function convertValue($source)
	{
		if (is_scalar($source))
		{
			return $source;
		}

		if (is_array($source))
		{
			return array_map([$this, 'convertValue'], $source);
		}

		if ($source instanceof AbstractConfiguration)
		{
			return $source->toArray();
		}

		if (is_object($source))
		{
			try
			{
				return (array) $source;
			}
			catch (\Throwable $e)
			{
				return null;
			}
		}

		// Resource, or null
		return null;
	}

	/**
	 * Returns given word as CamelCased.
	 *
	 * Converts a word like "foo_bar" or "foo bar" to "FooBar". It will remove non alphanumeric characters from the
	 * word, so "who's online" will be converted to "WhoSOnline".
	 *
	 * @param   string  $word  Word to convert to camel case.
	 *
	 * @return  string  UpperCamelCasedWord
	 *
	 * @since   10.0
	 */
	private function camelize(string $word): string
	{
		$word = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $word);

		return str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $word))));
	}
}