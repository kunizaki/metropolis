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
 * URI parsing and manipulation
 *
 * @since  10.0
 */
class Uri extends AbstractUri
{
	/**
	 * Adds a query variable and value, replacing the value if it already exists and returning the old value
	 *
	 * @param   string  $name   Name of the query variable to set.
	 * @param   string  $value  Value of the query variable.
	 *
	 * @return  string  Previous value for the query variable.
	 *
	 * @since   10.0
	 */
	public function setVar(string $name, string $value): ?string
	{
		$tmp = $this->vars[$name] ?? null;

		$this->vars[$name] = $value;

		// Empty the query
		$this->query = null;

		return $tmp;
	}

	/**
	 * Removes an item from the query string variables if it exists
	 *
	 * @param   string  $name  Name of variable to remove.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function delVar(string $name): void
	{
		if (array_key_exists($name, $this->vars))
		{
			unset($this->vars[$name]);

			// Empty the query
			$this->query = null;
		}
	}

	/**
	 * Sets the query to a supplied string in format foo=bar&x=y
	 *
	 * @param   array|string  $query  The query string or array.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setQuery($query): void
	{
		if (\is_array($query))
		{
			$this->vars = $query;
		}
		else
		{
			if (strpos($query, '&amp;') !== false)
			{
				$query = str_replace('&amp;', '&', $query);
			}

			parse_str($query, $this->vars);
		}

		// Empty the query
		$this->query = null;
	}

	/**
	 * Set the URI scheme (protocol)
	 *
	 * @param   string  $scheme  The URI scheme.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setScheme(string $scheme): Uri
	{
		$this->scheme = $scheme;

		return $this;
	}

	/**
	 * Set the URI username
	 *
	 * @param   string  $user  The URI username.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setUser(string $user): Uri
	{
		$this->user = $user;

		return $this;
	}

	/**
	 * Set the URI password
	 *
	 * @param   string  $pass  The URI password.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setPass(string $pass): Uri
	{
		$this->pass = $pass;

		return $this;
	}

	/**
	 * Set the URI host
	 *
	 * @param   string  $host  The URI host.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setHost(string $host): Uri
	{
		$this->host = $host;

		return $this;
	}

	/**
	 * Set the URI port
	 *
	 * @param   integer  $port  The URI port number.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setPort(int $port): Uri
	{
		$this->port = $port;

		return $this;
	}

	/**
	 * Set the URI path string
	 *
	 * @param   string  $path  The URI path string.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setPath(string $path): Uri
	{
		$this->path = $this->cleanPath($path);

		return $this;
	}

	/**
	 * Set the URI anchor string
	 *
	 * @param   string  $anchor  The URI anchor string.
	 *
	 * @return  Uri  This method supports chaining.
	 * @since   10.0
	 */
	public function setFragment(string $anchor): Uri
	{
		$this->fragment = $anchor;

		return $this;
	}
}
