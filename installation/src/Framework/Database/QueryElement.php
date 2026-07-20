<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

defined('_AKEEBA') or die();

final class QueryElement
{
	/**
	 * The name of the element.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $name = null;

	/**
	 * An array of elements.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $elements = null;

	/**
	 * Glue piece.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $glue = null;

	/**
	 * Constructor.
	 *
	 * @param   string        $name      The name of the element.
	 * @param   string|array  $elements  String or array.
	 * @param   string        $glue      The glue for elements.
	 *
	 * @since   10.0
	 */
	public function __construct(string $name, $elements, string $glue = ',')
	{
		$this->elements = [];
		$this->name     = $name;
		$this->glue     = $glue;

		$this->append($elements);
		$this->elements = is_array($elements) ? $elements : [$elements];
	}

	/**
	 * Magic function to convert the query element to a string.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function __toString()
	{
		if (substr($this->name, -2) == '()')
		{
			return PHP_EOL . substr($this->name, 0, -2) . '(' . implode($this->glue, $this->elements) . ')';
		}

		return PHP_EOL . $this->name . ' ' . implode($this->glue, $this->elements);
	}

	/**
	 * Appends element parts to the internal list.
	 *
	 * @param   mixed  $elements  String or array.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function append($elements)
	{
		$this->elements = is_array($this->elements) ? $this->elements : [$this->elements];

		if (is_array($elements))
		{
			$this->elements = array_merge($this->elements, $elements);

			return;
		}

		$this->elements = array_merge($this->elements, [$elements]);
	}

	/**
	 * Gets the elements of this element.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getElements()
	{
		return $this->elements;
	}

	/**
	 * Method to provide deep copy support to nested objects and arrays when cloning.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __clone()
	{
		foreach ($this as $k => $v)
		{
			if (!is_object($v) && !is_array($v))
			{
				continue;
			}

			$this->{$k} = unserialize(serialize($v));
		}
	}
}