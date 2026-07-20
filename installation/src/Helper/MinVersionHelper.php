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

class MinVersionHelper
{
	public static function getMinimumVersion($text)
	{
		$lines   = explode("\n", $text);
		$phpVersionId = null;

		foreach ($lines as $line)
		{
			if (strpos($line, 'PHP_VERSION_ID') === false)
			{
				continue;
			}

			$line = substr($line, strpos($line, 'PHP_VERSION_ID'));
			$line = substr($line, 0, strpos($line, ')'));
			[, $phpVersionId] = explode('>=', $line);
			$phpVersionId = intval(trim($phpVersionId));

			break;
		}

		if (!$phpVersionId)
		{
			return null;
		}


		$patch = (int) substr($phpVersionId, -2);
		$minor = (int) substr($phpVersionId, -3, 1);
		$major = (int) substr($phpVersionId, 0, -3) / 10;

		return implode('.', [$major, $minor, $patch]);
	}

	public static function enforceMinPhpVersion()
	{
		$minVersion = self::getMinimumVersion(file_get_contents(__DIR__ . '/../../vendor/composer/platform_check.php'));

		if (!$minVersion)
		{
			return;
		}

		if (!version_compare(PHP_VERSION, $minVersion, '<'))
		{
			return;
		}

		if (defined('AKEEBA_CLΙ') && constant('AKEEBA_CLΙ'))
		{
			$curVersion = PHP_VERSION;

			echo <<< TEXT
This application requires PHP $minVersion

You are curently using PHP $curVersion which is not compatible.

TEXT;

			exit(255);
		}

		@ob_start();
		require_once __DIR__ . '/../../template/php_version.php';
		$html = @ob_get_clean();
		@ob_end_clean();

		$filePath = __DIR__ . '/../../template/default/error.php';

		if (file_exists($filePath))
		{
			$error_message = $html;
			$error_code    = 500;

			require_once $filePath;
		}
		else
		{
			@header('HTTP/1.0 500 Internal Server Error');
			echo "<!doctype html><html><head><title>Error</title></head><body>$html</body></html>";
		}

		exit();
	}
}