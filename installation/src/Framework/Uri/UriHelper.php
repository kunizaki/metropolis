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
 * UTF-8-safe version of parse_url().
 *
 * @since  10.0
 */
class UriHelper
{
	/**
	 * UTF-8-safe version of PHP's parse_url function
	 *
	 * @param   string   $url        URL to parse
	 * @param   integer  $component  Retrieve just a specific URL component
	 *
	 * @return  array|boolean  An associative array or false if badly formed URL.
	 *
	 * @since   10.0
	 *@link    https://www.php.net/manual/en/function.parse-url.php
	 */
	public static function parse_url(string $url, int $component = -1)
	{
		$result = [];

		// If no UTF-8 chars in the url just parse it using php native parse_url which is faster.
		if (extension_loaded('mbstring') && mb_convert_encoding($url, 'ISO-8859-1', 'UTF-8') === $url)
		{
			return parse_url($url, $component);
		}

		// URL with UTF-8 chars in the url.

		// Build the reserved uri encoded characters map.
		$reservedUriCharactersMap = [
			'%21' => '!',
			'%2A' => '*',
			'%27' => "'",
			'%28' => '(',
			'%29' => ')',
			'%3B' => ';',
			'%3A' => ':',
			'%40' => '@',
			'%26' => '&',
			'%3D' => '=',
			'%24' => '$',
			'%2C' => ',',
			'%2F' => '/',
			'%3F' => '?',
			'%23' => '#',
			'%5B' => '[',
			'%5D' => ']',
		];

		// Encode the URL (so UTF-8 chars are encoded), revert the encoding in the reserved uri characters and parse the url.
		$parts = parse_url(strtr(urlencode($url), $reservedUriCharactersMap), $component);

		// With a well formed url decode the url (so UTF-8 chars are decoded).
		return $parts ? array_map('urldecode', $parts) : $parts;
	}
}
