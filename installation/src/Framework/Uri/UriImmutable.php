<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Uri;

/**
 * Immutable URI class
 *
 * @since  10.0
 */
final class UriImmutable extends AbstractUri
{
	/**
	 * Flag if the class been instantiated
	 *
	 * @var    boolean
	 * @since  10.0
	 */
	private $constructed = false;

	/**
	 * This is a special constructor that prevents calling the __construct method again.
	 *
	 * @param   string|null  $uri  The optional URI string
	 *
	 * @throws  \BadMethodCallException
	 * @since   10.0
	 */
	public function __construct(?string $uri = null)
	{
		if ($this->constructed === true)
		{
			throw new \BadMethodCallException('This is an immutable object');
		}

		$this->constructed = true;

		parent::__construct($uri);
	}

	/**
	 * Prevent setting undeclared properties.
	 *
	 * @param   string  $name   This is an immutable object, setting $name is not allowed.
	 * @param   mixed   $value  This is an immutable object, setting $value is not allowed.
	 *
	 * @return  void  This method always throws an exception.
	 *
	 * @throws  \BadMethodCallException
	 * @since   10.0
	 */
	public function __set($name, $value)
	{
		throw new \BadMethodCallException('This is an immutable object');
	}
}
