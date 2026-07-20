<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Helper;

defined('_AKEEBA') or die();

final class ApplicationHelper
{
	private function __construct()
	{
		throw new \RuntimeException('This class is not meant to be instantiated');
	}

	/**
	 * Configures the application to run in debug mode if the debug constant is defined.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public static function applyDebugMode(): void
	{
		if (!defined('AKEEBA_DEBUG'))
		{
			self::autoDebugMode();
		}

		$chatty = defined('AKEEBA_DEBUG') && AKEEBA_DEBUG;

		if (function_exists('error_reporting'))
		{
			if ($chatty)
			{
				error_reporting(E_ALL | E_NOTICE | E_DEPRECATED);
			}
			else
			{
				error_reporting(E_ERROR & ~E_NOTICE & ~E_DEPRECATED);
			}
		}

		if (function_exists('ini_set'))
		{
			ini_set('display_errors', $chatty ? 1 : 0);
		}
	}

	/**
	 * Sets a higher memory limit for the application if allowed by the environment.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public static function applyMemoryLimit(): void
	{
		if (!function_exists('ini_set') || defined('AKEEBA_DISABLE_HIGH_LIMITS'))
		{
			return;
		}

		@ini_set('memory_limit', '1G');
	}

	/**
	 * Sets a high time limit for the script execution if the environment allows it.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public static function applyTimeLimit()
	{
		if (!function_exists('ini_set') || defined('AKEEBA_DISABLE_HIGH_LIMITS'))
		{
			return;
		}

		@ini_set('max_execution_time', 86400);
	}

	/**
	 * Automatically enables debug mode if the application is running on a local development environment.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private static function autoDebugMode(): void
	{
		$hostName    = $_SERVER['HTTP_HOST'] ?? '';
		$isAutoDebug = $hostName == 'localhost' || substr($hostName, -11) == '.akeeba.dev';

		if (!$isAutoDebug)
		{
			return;
		}

		define('AKEEBA_DEBUG', true);
	}

}