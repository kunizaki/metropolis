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
 * Implements the UriInterface (read-only parsing of URIs)
 *
 * @since  10.0
 */
abstract class AbstractUri implements UriInterface
{
	/**
	 * Original URI
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $uri;

	/**
	 * Protocol
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $scheme;

	/**
	 * Host
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $host;

	/**
	 * Port
	 *
	 * @var    integer
	 * @since  10.0
	 */
	protected $port;

	/**
	 * Username
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $user;

	/**
	 * Password
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $pass;

	/**
	 * Path
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $path;

	/**
	 * Query
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $query;

	/**
	 * Anchor
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $fragment;

	/**
	 * Query variable hash
	 *
	 * @var    array
	 * @since  10.0
	 */
	protected $vars = [];

	/**
	 * Constructor.
	 *
	 * You can pass a URI string to the constructor to initialise a specific URI.
	 *
	 * @param   string|null  $uri  The optional URI string
	 *
	 * @since   10.0
	 */
	public function __construct(?string $uri = null)
	{
		if ($uri !== null)
		{
			$this->parse($uri);
		}
	}

	/**
	 * Build a query from an array (reverse of the PHP parse_str()).
	 *
	 * @param   array  $params  The array of key => value pairs to return as a query string.
	 *
	 * @return  string  The resulting query string.
	 *
	 * @since   10.0
	 * @see     parse_str()
	 */
	protected static function buildQuery(array $params)
	{
		return urldecode(http_build_query($params, '', '&'));
	}

	/**
	 * Magic method to get the string representation of the UriInterface object.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function __toString()
	{
		return $this->toString();
	}

	/**
	 * Returns full URI string.
	 *
	 * @param   array  $parts  An array of strings specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 * @since   10.0
	 */
	public function toString(array $parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']
	): string
	{
		$bitmask = 0;

		foreach ($parts as $part)
		{
			$const = 'static::' . strtoupper($part);

			if (\defined($const))
			{
				$bitmask |= \constant($const);
			}
		}

		return $this->render($bitmask);
	}

	/**
	 * Returns full uri string.
	 *
	 * @param   integer  $parts  A bitmask specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 * @since   10.0
	 */
	public function render(int $parts = self::ALL): string
	{
		// Make sure the query is created
		$query = $this->getQuery();

		$uri = $parts & static::SCHEME ? (!empty($this->scheme) ? $this->scheme . '://' : '') : '';
		$uri .= $parts & static::USER ? $this->user : '';
		$uri .= $parts & static::PASS ? (!empty($this->pass) ? ':' : '') . $this->pass . (!empty($this->user) ? '@'
				: '') : '';
		$uri .= $parts & static::HOST ? $this->host : '';
		$uri .= $parts & static::PORT ? (!empty($this->port) ? ':' : '') . $this->port : '';
		$uri .= $parts & static::PATH ? $this->path : '';
		$uri .= $parts & static::QUERY ? (!empty($query) ? '?' . $query : '') : '';
		$uri .= $parts & static::FRAGMENT ? (!empty($this->fragment) ? '#' . $this->fragment : '') : '';

		return $uri;
	}

	/**
	 * Checks if variable exists.
	 *
	 * @param   string  $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 * @since   10.0
	 */
	public function hasVar(string $name): bool
	{
		return array_key_exists($name, $this->vars);
	}

	/**
	 * Returns a query variable by name.
	 *
	 * @param   string       $name     Name of the query variable to get.
	 * @param   string|null  $default  Default value to return if the variable is not set.
	 *
	 * @return  mixed   Requested query variable if present otherwise the default value.
	 * @since   10.0
	 */
	public function getVar(string $name, ?string $default = null)
	{
		if (array_key_exists($name, $this->vars))
		{
			return $this->vars[$name];
		}

		return $default;
	}

	/**
	 * Returns flat query string.
	 *
	 * @param   boolean  $toArray  True to return the query as a key => value pair array.
	 *
	 * @return  string|array   Query string or Array of parts in query string depending on the function param
	 * @since   10.0
	 */
	public function getQuery(bool $toArray = false)
	{
		if ($toArray)
		{
			return $this->vars ?: [];
		}

		// If the query is empty build it first
		if ($this->query === null)
		{
			$this->query = static::buildQuery($this->vars ?: []) ?: '';
		}

		return $this->query ?: '';
	}

	/**
	 * Get the URI scheme (protocol)
	 *
	 * @return  string  The URI scheme.
	 * @since   10.0
	 */
	public function getScheme(): string
	{
		return $this->scheme ?: '';
	}

	/**
	 * Get the URI username
	 *
	 * @return  string  The username, or null if no username was specified.
	 * @since   10.0
	 */
	public function getUser(): ?string
	{
		return $this->user ?: null;
	}

	/**
	 * Get the URI password
	 *
	 * @return  string  The password, or null if no password was specified.
	 * @since   10.0
	 */
	public function getPass(): ?string
	{
		return $this->pass ?: null;
	}

	/**
	 * Get the URI host
	 *
	 * @return  string  The hostname/IP or null if no hostname/IP was specified.
	 * @since   10.0
	 */
	public function getHost(): ?string
	{
		return $this->host ?: null;
	}

	/**
	 * Get the URI port
	 *
	 * @return  integer  The port number, or null if no port was specified.
	 * @since   10.0
	 */
	public function getPort(): ?int
	{
		return $this->port ?: null;
	}

	/**
	 * Gets the URI path string
	 *
	 * @return  string  The URI path string.
	 * @since   10.0
	 */
	public function getPath(): string
	{
		return $this->path ?: '';
	}

	/**
	 * Get the URI anchor string
	 *
	 * @return  string|null  The URI anchor string.
	 * @since   10.0
	 */
	public function getFragment(): ?string
	{
		return $this->fragment ?: null;
	}

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS.
	 * @since   10.0
	 */
	public function isSsl(): bool
	{
		return strtolower($this->getScheme()) === 'https';
	}

	/**
	 * Parse a given URI and populate the class fields.
	 *
	 * @param   string  $uri  The URI string to parse.
	 *
	 * @return  boolean  True on success.
	 * @since   10.0
	 */
	protected function parse(string $uri): bool
	{
		// Set the original URI to fall back on
		$this->uri = $uri;

		/*
		 * Parse the URI and populate the object fields. If URI is parsed properly,
		 * set method return value to true.
		 */

		$parts = UriHelper::parse_url($uri);

		if ($parts === false)
		{
			throw new \RuntimeException(sprintf('Could not parse the requested URI %s', $uri));
		}

		$retval = ($parts) ? true : false;

		// We need to replace &amp; with & for parse_str to work right...
		if (isset($parts['query']) && strpos($parts['query'], '&amp;') !== false)
		{
			$parts['query'] = str_replace('&amp;', '&', $parts['query']);
		}

		foreach ($parts as $key => $value)
		{
			$this->$key = $value;
		}

		// Parse the query
		if (isset($parts['query']))
		{
			parse_str($parts['query'], $this->vars);
		}

		return $retval;
	}

	/**
	 * Resolves //, ../ and ./ from a path and returns the result.
	 *
	 * For example:
	 * /foo/bar/../boo.php    => /foo/boo.php
	 * /foo/bar/../../boo.php => /boo.php
	 * /foo/bar/.././/boo.php => /foo/boo.php
	 *
	 * @param   string  $path  The URI path to clean.
	 *
	 * @return  string  Cleaned and resolved URI path.
	 * @since   10.0
	 */
	protected function cleanPath(string $path): string
	{
		$path = explode('/', preg_replace('#(/+)#', '/', $path));

		for ($i = 0, $n = \count($path); $i < $n; $i++)
		{
			if ($path[$i] == '.' || $path[$i] == '..')
			{
				if (($path[$i] == '.') || ($path[$i] == '..' && $i == 1 && $path[0] == ''))
				{
					unset($path[$i]);
					$path = array_values($path);
					$i--;
					$n--;
				}
				elseif ($path[$i] == '..' && ($i > 1 || ($i == 1 && $path[0] != '')))
				{
					unset($path[$i], $path[$i - 1]);

					$path = array_values($path);
					$i    -= 2;
					$n    -= 2;
				}
			}
		}

		return implode('/', $path);
	}
}
