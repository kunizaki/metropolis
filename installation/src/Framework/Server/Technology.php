<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Server;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Determines server support for various configuration files.
 *
 * @since 10.0
 */
final class Technology implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	public const HTACCESS = 'htaccess';

	public const NGINX = 'nginx';

	public const WEB_DOT_CONFIG = 'webdotconfig';

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The container instance to be set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Does the current server support .htaccess files?
	 *
	 * @return  int  0=No, 1=Yes, 2=Maybe
	 * @since   10.0
	 */
	public function isHtaccessSupported(): int
	{
		// Get the server string
		$serverString = $this->getContainer()->get('input')->server->getRaw('SERVER_SOFTWARE');

		// Not defined? Return maybe (2)
		if (empty($serverString))
		{
			return 2;
		}

		// Apache? Yes
		if (strtoupper(substr($serverString, 0, 6)) == 'APACHE')
		{
			return 1;
		}

		// NginX? No
		if (strtoupper(substr($serverString, 0, 5)) == 'NGINX')
		{
			return 0;
		}

		// IIS? No
		if (strstr($serverString, 'IIS') !== false)
		{
			return 0;
		}

		// Anything else? Maybe.
		return 2;
	}

	/**
	 * Does the current server supports NginX configuration files?
	 *
	 * @return  int  0=No, 1=Yes, 2=Maybe
	 * @since   10.0
	 */
	public function isNginxSupported(): int
	{
		// Get the server string
		$serverString = $this->getContainer()->get('input')->server->getRaw('SERVER_SOFTWARE');

		// Not defined? Return maybe (2)
		if (empty($serverString))
		{
			return 2;
		}

		// NginX? Yes
		if (strtoupper(substr($serverString, 0, 5)) == 'NGINX')
		{
			return 1;
		}

		// Anything else? No.
		return 0;
	}

	/**
	 * Does the current server support web.config files?
	 *
	 * @return  int  0=No, 1=Yes, 2=Maybe
	 * @since   10.0
	 */
	public function isWebConfigSupported(): int
	{
		// Get the server string
		$serverString = $this->getContainer()->get('input')->server->getRaw('SERVER_SOFTWARE');

		// Not defined? Return maybe (2)
		if (empty($serverString))
		{
			return 2;
		}

		// Apache? No
		if (strtoupper(substr($serverString, 0, 6)) == 'APACHE')
		{
			return 0;
		}

		// NginX? No
		if (strtoupper(substr($serverString, 0, 5)) == 'NGINX')
		{
			return 0;
		}

		// IIS? Yes
		if (strstr($serverString, 'IIS') !== false)
		{
			return 1;
		}

		// Anything else? No.
		return 0;
	}

	/**
	 * Determines if the current server supports a specified configuration type.
	 *
	 * @param   string  $what  The configuration type to check, such as .htaccess, NginX, or web.config.
	 *
	 * @return  int  0=No, 1=Yes, or 2=Maybe, indicating if the specified configuration type is supported.
	 * @since   10.0
	 */
	public function supports(string $what): int
	{
		switch ($what)
		{
			case self::HTACCESS:
				return $this->isHtaccessSupported();

			case self::NGINX:
				return $this->isNginxSupported();

			case self::WEB_DOT_CONFIG:
				return $this->isWebConfigSupported();

			default:
				return 0;
		}
	}
}