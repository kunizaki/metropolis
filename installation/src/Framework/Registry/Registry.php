<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Registry;

use Akeeba\BRS\Framework\Registry\Format\Json;

defined('_AKEEBA') or die();

final class Registry implements \JsonSerializable
{
	/**
	 * Internal data store.
	 *
	 * @var    \stdClass
	 * @since  10.0
	 */
	protected $data;

	/**
	 * Has the registry object been initialized?
	 *
	 * @var    bool
	 * @since  10.0
	 */
	protected $initialized = false;

	/**
	 * Constructor
	 *
	 * @param   mixed  $data  The data to bind to the new Registry object.
	 *
	 * @since   10.0
	 */
	public function __construct($data = null)
	{
		// Instantiate the internal data object.
		$this->data = new \stdClass();

		// Optionally load supplied data.
		if ($data instanceof self)
		{
			$this->merge($data);
		}
		elseif (\is_array($data) || \is_object($data))
		{
			$this->bindData($this->data, $data);
		}
		elseif (!empty($data) && \is_string($data))
		{
			$this->loadString($data);
		}
	}

	/**
	 * Properly clone the internal data.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __clone()
	{
		$this->data = \unserialize(\serialize($this->data));
	}

	/**
	 * Magic function to convert the registry object into a string.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Implements the JsonSerializable interface. Allows calling json_encode on the object.
	 *
	 * @return  object
	 *
	 * @since   10.0
	 * @note    The interface is only present in PHP 5.4 and up.
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return $this->data;
	}

	/**
	 * Sets a default value if not already assigned.
	 *
	 * @param   string  $key      The name of the parameter.
	 * @param   mixed   $default  An optional value for the parameter.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function def(string $key, $default = '')
	{
		$value = $this->get($key, $default);

		$this->set($key, $value);

		return $value;
	}

	/**
	 * Check if a registry path exists.
	 *
	 * @param   string  $path  Registry path (e.g. foo.bar.baz)
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function exists(string $path): bool
	{
		if (empty($path))
		{
			return false;
		}

		$keyNames = \explode('.', $path);
		$node     = $this->data;

		foreach ($keyNames as $n)
		{
			if (\is_array($node))
			{
				if (isset($node[$n]))
				{
					$node = $node[$n];

					continue;
				}

				return false;
			}

			if (\is_object($node))
			{
				if (isset($node->$n))
				{
					$node = $node->$n;
				}

				return false;
			}

			return false;
		}

		return true;
	}

	/**
	 * Get a registry value.
	 *
	 * @param   string  $path     Registry path (e.g. foo.bar.baz)
	 * @param   mixed   $default  Default value, returned when the value is unset, NULL, or empty string.
	 *
	 * @return  mixed
	 *
	 * @since   10.0
	 */
	public function get(string $path, $default = null)
	{
		if (empty($path))
		{
			return $default;
		}

		if (!\strpos($path, '.'))
		{
			return (isset($this->data->$path) && $this->data->$path !== null && $this->data->$path !== '')
				? $this->data->$path
				: $default;
		}

		$keyNames = \explode('.', \trim($path));
		$node     = $this->data;

		foreach ($keyNames as $n)
		{
			if (\is_array($node))
			{
				if (isset($node[$n]))
				{
					$node = $node[$n];

					continue;
				}

				return $default;
			}

			if (\is_object($node))
			{
				if (isset($node->$n))
				{
					$node = $node->$n;

					continue;
				}

				return $default;
			}

			return $default;
		}

		if ($node === null || $node === '')
		{
			return $default;
		}

		return $node;
	}

	/**
	 * Load the public variables of an object into the registry.
	 *
	 * @param   object  $object  The object to import.
	 *
	 * @return  $this
	 * @since   10.0
	 */
	public function loadObject(object $object): Registry
	{
		$this->bindData($this->data, $object);

		return $this;
	}

	/**
	 * Load a string into the registry
	 *
	 * @param   string  $data     String to load into the registry
	 * @param   string  $format   Format of the string
	 * @param   array   $options  Options used by the formatter
	 *
	 * @return  $this
	 * @since   10.0
	 */
	public function loadString(string $data, array $options = []): Registry
	{
		$obj = $this->stringToObject($data, $options);

		if (!$this->initialized)
		{
			$this->data        = $obj;
			$this->initialized = true;

			return $this;
		}

		$this->loadObject($obj);

		return $this;
	}

	/**
	 * Merge another registry object into this.
	 *
	 * @param   Registry  $source     The registry object to merge.
	 * @param   boolean   $recursive  Should children values be merged recursively?
	 *
	 * @return  $this
	 * @since   10.0
	 */
	public function merge(Registry $source, bool $recursive = false): Registry
	{
		$this->bindData($this->data, $source->toArray(), $recursive, false);

		return $this;
	}

	/**
	 * Set a registry value.
	 *
	 * @param   string  $path   Registry Path (e.g. foo.bar.baz)
	 * @param   mixed   $value  Value of entry
	 *
	 * @return  mixed  The value of the that has been set.
	 * @since   10.0
	 */
	public function set($path, $value)
	{
		$keyNames = \array_values(\array_filter(\explode('.', $path), 'strlen'));

		if (!$keyNames)
		{
			return null;
		}

		$node = $this->data;

		for ($i = 0, $n = \count($keyNames) - 1; $i < $n; $i++)
		{
			if (\is_object($node))
			{
				if (!isset($node->{$keyNames[$i]}))
				{
					$node->{$keyNames[$i]} = new \stdClass();
				}

				$node = &$node->{$keyNames[$i]};

				continue;
			}

			if (\is_array($node))
			{
				if (!isset($node[$keyNames[$i]]))
				{
					$node[$keyNames[$i]] = new \stdClass();
				}

				$node = &$node[$keyNames[$i]];
			}

			return null;
		}

		// Get the old value if exists so we can return it
		if (\is_object($node))
		{
			$result                = $node->{$keyNames[$i]} ?? null;
			$node->{$keyNames[$i]} = $value;

			return $result;
		}

		if (\is_array($node))
		{
			$result              = $node[$keyNames[$i]] ?? null;
			$node[$keyNames[$i]] = $value;

			return $result;
		}

		return null;
	}

	/**
	 * Remove a registry value
	 *
	 * @param   string  $path  Registry Path (e.g. foo.bar.baz)
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function remove(string $path)
	{
		if (!\strpos($path, '.'))
		{
			$result = (isset($this->data->$path) && $this->data->$path !== null && $this->data->$path !== '')
				? $this->data->$path
				: null;

			unset($this->data->$path);

			return $result;
		}

		$keyNames = \array_values(\array_filter(\explode('.', $path), 'strlen'));

		if (!$keyNames)
		{
			return null;
		}

		$node   = $this->data;
		$parent = null;

		// Traverse the registry to find the correct node for the result.
		for ($i = 0, $n = \count($keyNames) - 1; $i < $n; $i++)
		{
			if (\is_object($node))
			{
				if (!isset($node->{$keyNames[$i]}))
				{
					continue;
				}

				$parent = &$node;
				$node   = $node->{$keyNames[$i]};

				continue;
			}

			if (\is_array($node))
			{
				if (!isset($node[$keyNames[$i]]))
				{
					continue;
				}

				$parent = &$node;
				$node   = $node[$keyNames[$i]];

				continue;
			}

			return null;
		}

		// Get the old value if exists so we can return it
		if (\is_object($node))
		{
			$result = $node->{$keyNames[$i]} ?? null;

			unset($parent->{$keyNames[$i]});

			return $result;
		}

		if (\is_array($node))
		{
			$result = $node[$keyNames[$i]] ?? null;

			unset($parent[$keyNames[$i]]);

			return $result;
		}

		return null;
	}

	/**
	 * Transforms a namespace to an array
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function toArray(): array
	{
		return $this->asArray($this->data);
	}

	/**
	 * Transforms a namespace to an object
	 *
	 * @return  object
	 * @since   10.0
	 */
	public function toObject()
	{
		return $this->data;
	}

	/**
	 * Get a namespace in a given string format
	 *
	 * @param   array  $options  Parameters used by the formatter, see formatters for more info
	 *
	 * @return  string   Namespace in string format
	 * @since   10.0
	 */
	public function toString(array $options = []): string
	{
		return $this->objectToString($this->data, $options);
	}

	/**
	 * Recursively bind data to a parent object.
	 *
	 * @param   object        $parent     The object where the data will be bound.
	 * @param   array|object  $data       An array or object of data to bind to the parent object.
	 * @param   boolean       $recursive  True to support recursive binding.
	 * @param   boolean       $allowNull  True to allow null values.
	 *
	 * @return  void
	 *
	 * @since   10.0
	 */
	protected function bindData(object $parent, $data, bool $recursive = true, bool $allowNull = true)
	{
		$this->initialized = true;

		if (!\is_object($data) && !\is_array($data))
		{
			return;
		}

		$data = \is_object($data) ? \get_object_vars($data) : (array) $data;

		foreach ($data as $k => $v)
		{
			if (!$allowNull && !(($v !== null) && ($v !== '')))
			{
				continue;
			}

			if ($recursive && ((\is_array($v) && $this->isAssociative($v)) || \is_object($v)))
			{
				if (!isset($parent->$k))
				{
					$parent->$k = new \stdClass();
				}

				$this->bindData($parent->$k, $v);

				continue;
			}

			$parent->$k = $v;
		}
	}

	/**
	 * Recursively convert an object to an array.
	 *
	 * @param   object|array  $data  The object or array to return as an array.
	 *
	 * @return  array  Array representation of the input object.
	 *
	 * @since   10.0
	 */
	protected function asArray($data): array
	{
		if (!\is_object($data) && !\is_array($data))
		{
			return [];
		}

		$array = [];

		if (\is_object($data))
		{
			$data = \get_object_vars($data);
		}

		foreach ($data as $k => $v)
		{
			if (\is_object($v) || \is_array($v))
			{
				$array[$k] = $this->asArray($v);

				continue;
			}

			$array[$k] = $v;
		}

		return $array;
	}

	/**
	 * Is the argument an associative array?
	 *
	 * @param   mixed  $array  THe argument to test.
	 *
	 * @return  boolean
	 *
	 * @since   10.0
	 */
	private function isAssociative($array): bool
	{
		if (!\is_array($array))
		{
			return false;
		}

		foreach (array_keys($array) as $k => $v)
		{
			if ($k !== $v)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Converts an object into a JSON-formatted string.
	 *
	 * @param   object  $object   Source object.
	 * @param   array   $options  Formatter options.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function objectToString(object $object, array $options = []): string
	{
		$bitMask = $options['bitmask'] ?? 0;
		$depth   = $options['depth'] ?? 512;

		return \json_encode($object, $bitMask, $depth);
	}

	/**
	 * Converts a JSON-formatted string into an object.
	 *
	 * @param   string  $data     Formatted string.
	 * @param   array   $options  Formatter options.
	 *
	 * @return  object
	 * @since   10.0
	 */
	private function stringToObject(string $data, array $options = []): object
	{
		$data = \trim($data);

		if (empty($data))
		{
			return new \stdClass();
		}

		$decoded = @\json_decode($data);

		// Check for an error decoding the data
		if ($decoded === null && \json_last_error() !== JSON_ERROR_NONE)
		{
			throw new \RuntimeException(\sprintf('Error decoding JSON data: %s', \json_last_error_msg()));
		}

		return (object) $decoded;
	}

}