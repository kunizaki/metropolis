<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Uri;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Uri object factory service.
 *
 * Provides Singleton implementation of the Uri constructor, and deals with receiving information about the current URL
 * reported by the server.
 *
 * @since  10.0
 */
final class Factory implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * An array of URI instances.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $instances = [];

	/**
	 * The current calculated base URL segments.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $base = [];

	/**
	 * The current calculated root URL segments.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $root = [];

	/**
	 * The current URL.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected static $current;

	/**
	 * Should we use the Forwarded and X-Forwarded-Host header to determine the hostname?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected static $useHostnameDetection = true;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Set the flag to use the Forwarded and X-Forwarded-Host header to determine the hostname.
	 *
	 * Setting this flag to TRUE (default) will prioritise the contents of these headers over the `Host` header. This is
	 * useful when you have a CDN, TLS Terminator, or reverse proxy responding to one hostname (e.g. `example.com`) but
	 * the origin server runs in a different and possibly private hostname (e.g. `origin.local`), internal network IP
	 * address (e.g. `10.0.0.1`), or generally something that's other than the actual hostname you want to use for your
	 * site.
	 *
	 * @param   bool  $useHostnameDetection  TRUE to use the optional headers.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public static function setUseHostnameDetection(bool $useHostnameDetection): void
	{
		self::$useHostnameDetection = $useHostnameDetection;
	}

	/**
	 * Returns a singleton URI object for the provided URL.
	 *
	 * @param   string  $uri  The URI to parse. If null, uses current URL.
	 *
	 * @return  Uri  The URI object.
	 * @since   10.0
	 */
	public function instance($uri = 'SERVER'): Uri
	{
		if (isset(self::$instances[$uri]))
		{
			return self::$instances[$uri];
		}

		if ($uri !== 'SERVER')
		{
			return self::$instances[$uri] = new Uri($uri);
		}

		$input = $this->getContainer()->get('input');

		if ($input instanceof Cli)
		{
			return self::$instances[$uri] = new Uri('https://akeeba.invalid/installation/index.php');
		}

		$serverInput = $input->server;

		// Determine if the request was over SSL (HTTPS).
		$protocol = ($this->isHttps() ? 'https' : 'http') . '://';

		// Determine the hostname
		$hostname = $this->getHostname();

		/**
		 * Determine if we run under Apache or IIS and construct the server URI accordingly.
		 *
		 * If PHP_SELF and REQUEST_URI are present, we will assume we are running on Apache.
		 */
		$phpSelf    = $serverInput->getRaw('PHP_SELF');
		$requestUri = $serverInput->getRaw('REQUEST_URI');

		if (!empty($phpSelf) && !empty($requestUri))
		{
			// To build the entire URI we need to prefix the protocol, and the host to the request URI path string.
			$fullURL = $protocol . $hostname . $requestUri;

			return self::$instances[$uri] = new Uri($fullURL);
		}

		/**
		 * When PHP_SELF or REQUEST_URI are not set we assume we are running under IIS.
		 *
		 * IIS does not provide the REQUEST_URI environment variable. Therefore, we need to use the SCRIPT_NAME and
		 * QUERY_STRING to work out the request URI path.
		 */
		$scriptName  = $serverInput->getRaw('SCRIPT_NAME');
		$queryString = $serverInput->getRaw('QUERY_STRING', null);
		$queryString = empty($queryString) ? '' : "?$queryString";
		$fullURL     = $protocol . $hostname . $scriptName . $queryString;

		return self::$instances[$uri] = new Uri($fullURL);
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   bool  $pathOnly  If true, omits the scheme, host and port information.
	 *
	 * @return  string  The base URI string
	 * @since   10.0
	 */
	public function base(bool $pathOnly = false): string
	{
		if (!empty(self::$base))
		{
			return $pathOnly ? self::$base['path'] : self::$base['prefix'] . self::$base['path'] . '/';
		}

		$input                = $this->getContainer()->get('input');
		$serverInput          = $input->server;
		$uri                  = $this->instance();
		self::$base['prefix'] = $uri->toString(['scheme', 'host', 'port']);
		$scriptName           = $serverInput->getRaw('SCRIPT_NAME');

		// Is this PHP-CGI on Apache with "cgi.fix_pathinfo = 0"?
		$requestUri = $serverInput->getRaw('REQUEST_URI', '');

		if (
			strpos(php_sapi_name(), 'cgi') !== false
			&& !ini_get('cgi.fix_pathinfo')
			&& !empty($requestUri)
		)
		{
			$scriptName = $serverInput->getRaw('PHP_SELF');
		}

		self::$base['path'] = rtrim(dirname($scriptName), '/\\');

		return $pathOnly ? self::$base['path'] : self::$base['prefix'] . self::$base['path'] . '/';
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   bool         $pathOnly  If true, omits the scheme, host and port information.
	 * @param   string|null  $path      Override the root path
	 *
	 * @return  string  The root URI string.
	 * @since   10.0
	 */
	public function root(bool $pathOnly = false, ?string $path = null): string
	{
		// Get the scheme
		if (empty(self::$root))
		{
			$uri                  = $this->instance(self::base());
			self::$root['prefix'] = $uri->toString(['scheme', 'host', 'port']);
			self::$root['path']   = rtrim($uri->toString(['path']), '/\\');
		}

		// Get the scheme
		if ($path !== null)
		{
			self::$root['path'] = $path;
		}

		return $pathOnly ? self::$root['path'] : self::$root['prefix'] . self::$root['path'] . '/';
	}

	/**
	 * Returns the URL for the request, minus the query.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function current(): string
	{
		if (empty(self::$current))
		{
			self::$current = $this->instance()->toString(['scheme', 'host', 'port', 'path']);
		}

		return self::$current;
	}

	/**
	 * Resets the cached information for Uri object instances, and the base, root, and current path.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function reset(): void
	{
		self::$instances = [];
		self::$base      = [];
		self::$root      = [];
		self::$current   = '';
	}

	/**
	 * Are we running under HTTPS?
	 *
	 * We first evaluate the optional HTTP headers `Forwarded`, `X-Forwarded-Proto, `X-Url-Scheme`, `Front-End-Https`,
	 * and `X-Forwarded-Ssl`. To prevent HTTP downgrade attacks we only allow these optional headers to **upgrade** the
	 * connection to HTTPS.
	 *
	 * If these headers are not set, or do not indicate an HTTPS connection, we use the `HTTPS` server environment
	 * variable. If it's not set we assume HTTPS. If it's set to anything other than the string value `off` we assume
	 * HTTPS.
	 *
	 * @return  bool
	 * @since   10.0.4
	 */
	private function isHttps(): bool
	{
		$serverInput = $this->getContainer()->get('input')->server;
		$scheme      = null;

		// Forwarded HTTP header
		$forwarded = $this->parseForwardedHeader();

		if (is_array($forwarded) && !empty($forwarded['proto'] ?? null) && strtolower($forwarded['proto']) == 'https')
		{
			$scheme = 'https';
		}

		// X-Forwarded-Proto HTTP header
		$xForwardedProto = $this->getHttpHeader('X-Forwarded-Proto');

		if (is_string($xForwardedProto) && strtolower($xForwardedProto) == 'https')
		{
			$scheme = 'https';
		}

		// X-Url-Scheme HTTP header
		$xUrlScheme = $this->getHttpHeader('X-Url-Scheme');

		if (is_string($xUrlScheme) && strtolower($xUrlScheme) == 'https')
		{
			$scheme = 'https';
		}

		// Front-End-Https HTTP header
		$frontEndHttps = $this->getHttpHeader('Front-End-Https');

		if (is_string($frontEndHttps) && strtolower($frontEndHttps) === 'on')
		{
			$scheme = 'https';
		}

		// X-Forwarded-Ssl HTTP header
		$xForwardedSsl = $this->getHttpHeader('X-Forwarded-Ssl');

		if (is_string($xForwardedSsl) && strtolower($xForwardedSsl) === 'on')
		{
			$scheme = 'https';
		}

		if ($scheme === 'https')
		{
			return true;
		}

		return strtolower($serverInput->getRaw('HTTPS', null) ?: 'off') !== 'off';
	}

	private function getHostname(): string
	{
		$serverInput = $this->getContainer()->get('input')->server;
		$hostname    = null;

		// Forwarded
		$forwarded = $this->parseForwardedHeader();

		if (is_array($forwarded) && !empty($forwarded['host'] ?? null))
		{
			$hostname = trim($forwarded['host'] ?: '');
		}

		// X-Forwarded-Host
		$xForwardedHost = $this->getHttpHeader('X-Forwarded-Host');

		if (is_string($xForwardedHost))
		{
			$hostname = trim($xForwardedHost);
		}

		// Fallback for no detection (lack of headers, or feature disabled)
		if (empty($hostname) || !self::$useHostnameDetection)
		{
			return $serverInput->getRaw('HTTP_HOST', null) ?: 'akeeba.invalid';
		}

		return $hostname;
	}

	/**
	 * Parses the HTTP `Forwarded` header into an array.
	 *
	 * @return  array|null
	 * @since   10.0.4
	 * @link    https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Forwarded
	 */
	private function parseForwardedHeader(): ?array
	{
		// Get the header contents
		$serverInput = $this->getContainer()->get('input')->server;
		$content     = $serverInput->getRaw('HTTP_FORWARDED', null) ?: null;
		$content     = is_string($content) ? strtolower(trim($content)) : null;

		// If the Forwarded header doesn't exist, is empty, or contains garbage we will return NULL.
		if (empty($content))
		{
			return null;
		}

		// Initialise the return array.
		$ret = [];

		/**
		 * Get the key-value pairs.
		 *
		 * The `$content` looks like this:
		 * by=192.168.42.42;for=10.0.0.1;host=example.com;proto=https
		 * */
		$pairs = explode(';', $content);

		foreach ($pairs as $pair)
		{
			// The pair must have an equals sign.
			if (strpos($pair, '=') === false)
			{
				continue;
			}

			[$key, $value] = explode('=', $pair, 2);

			$key   = strtolower(trim($key));
			$value = trim($value);

			// Only accept certain keys
			if (!in_array($key, ['by', 'for', 'host', 'proto']))
			{
				continue;
			}

			$ret[$key] = $value;
		}

		return $ret;
	}

	/**
	 * Get the contents of an HTTP header.
	 *
	 * @param   string  $headerName  The name of the header, e.g. `X-Forwarded-Proto`.
	 *
	 * @return  string|null  The header value; NULL if it's empty, or not set.
	 * @since   10.0.4
	 */
	private function getHttpHeader(string $headerName): ?string
	{
		$headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $headerName));
		$content   = $this->getContainer()->get('input')->server->getRaw($headerKey, null) ?: null;

		return is_string($content) ? strtolower(trim($content)) : null;
	}
}