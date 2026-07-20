<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Ip;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class Ip implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The IP address of the current visitor
	 *
	 * @var    string
	 * @since  10.0
	 */
	private $ip = null;

	/**
	 * Should I allow IP overrides through X-Forwarded-For or Client-Ip HTTP headers?
	 *
	 * @var    boolean
	 * @since  10.0
	 */
	private $allowIpOverrides = true;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}

	/**
	 * Get the current visitor's IP address
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getIp()
	{
		if ($this->ip !== null)
		{
			return $this->ip;
		}

		$ip = $this->detectAndCleanIP();

		if (!empty($ip) && ($ip != '0.0.0.0') && \function_exists('inet_pton') && \function_exists('inet_ntop'))
		{
			$myIP = @inet_pton($ip);

			if ($myIP !== false)
			{
				$ip = inet_ntop($myIP);
			}
		}

		$this->setIp($ip);

		return $this->ip;
	}

	/**
	 * Set the IP address of the current visitor
	 *
	 * @param   string  $ip  The visitor's IP address
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setIp($ip)
	{
		$this->ip = $ip;
	}

	/**
	 * Should I allow the remote client's IP to be overridden by an X-Forwarded-For or Client-Ip HTTP header?
	 *
	 * @param   boolean  $newState  True to allow the override
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setAllowIpOverrides(bool $newState)
	{
		$this->allowIpOverrides = $newState;
	}

	/**
	 * Gets the visitor's IP address.
	 *
	 * Automatically handles reverse proxies reporting the IPs of intermediate devices.
	 *
	 * @return  string
	 * @since   10.0
	 */
	protected function detectAndCleanIP(): string
	{
		$ip = trim($this->detectIP());

		if (strstr($ip, ',') !== false || strstr($ip, ' ') !== false)
		{
			$ip  = str_replace(' ', ',', $ip);
			$ips = array_filter(array_map('trim', explode(',', $ip)));
			$ip  = array_unshift($ips);
		}

		return $ip;
	}

	/**
	 * Gets the visitor's IP address
	 *
	 * @return  string
	 * @since   10.0
	 */
	protected function detectIP(): string
	{
		$input       = $this->getContainer()->get('input');

		if ($input instanceof Cli)
		{
			return '127.0.0.1';
		}

		$serverInput = $input->server;
		$hasGetEnv   = function_exists('getenv');

		$keys   = $this->allowIpOverrides
			? ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR']
			: ['REMOTE_ADDR'];
		$values = array_map(
			function ($key) use ($serverInput, $hasGetEnv): ?string {
				return $serverInput->getRaw($key, $hasGetEnv ? getenv($key) : null);
			}, $keys
		);

		foreach ($values as $value)
		{
			if (is_string($value) && !empty(trim($value)))
			{
				return trim($value);
			}
		}

		// Catch-all for broken servers
		return '';
	}
}