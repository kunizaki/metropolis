<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Helper;

defined('_AKEEBA') or die();

/**
 * Helper class for the Site Setup step.
 *
 * @since  10.0
 */
abstract class Setup
{
	/**
	 * Cleans and normalizes a given URL string.
	 *
	 * @param   string|null  $url  The URL to be cleaned and normalized, or null.
	 *
	 * @return  string  The cleaned and normalized URL as a string.
	 * @since   10.0
	 */
	public static function cleanLiveSite(?string $url): string
	{
		// If the URL is empty there's nothing to do
		if (!$url)
		{
			return $url;
		}

		// If the url doesn't start with http or https let's strip any protocol and force HTTP
		if (!preg_match('#^http(s)?://#', $url))
		{
			$url = 'http://' . preg_replace('#^.*?://#', '', $url);
		}

		// Trim trailing slash
		$url = rtrim($url, '/');

		// Remove anything after the hash or a question mark
		$url = preg_replace('@(#|\?).*@', '', $url);

		//If the URL ends in .php, .html or .htm remove the last part of the URL.
		if (preg_match('#(\.php|\.htm(l)?)$#', $url))
		{
			$url = substr($url, 0, strrpos($url, '/'));
		}

		// Replace commas with dots (common spelling mistake)
		return str_replace(',', '.', $url);
	}
}