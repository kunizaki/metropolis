<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Compat;

use RuntimeException;
use UConverter;

defined('_AKEEBA') or die();

/**
 * PHP 9-compatible replacements for the removed utf8_encode() and utf8_decode() functions.
 *
 * @see https://wiki.php.net/rfc/remove_utf8_decode_and_utf8_encode
 */
final class Utf8
{
	private function _construct()
	{
		throw new RuntimeException('This class is not meant to be instantiated');
	}

	/**
	 * Encodes a Latin-1 string to UTF-8.
	 *
	 * Attempts mb_convert_encoding, UConverter, iconv, then a pure-PHP fallback.
	 *
	 * @param   string  $s  The input string to be encoded to UTF-8.
	 *
	 * @return  string  The UTF-8 encoded string.
	 * @since   10.0
	 */
	public static function utf8_encode(string $s): string
	{
		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
		}

		if (class_exists(UConverter::class))
		{
			return UConverter::transcode($s, 'UTF8', 'ISO-8859-1');
		}

		if (function_exists('iconv'))
		{
			return iconv('ISO-8859-1', 'UTF-8', $s);
		}

		/**
		 * Fallback to the pure PHP implementation from Symfony Polyfill for PHP 7.2
		 *
		 * @link https://github.com/symfony/polyfill-php72/blob/v1.26.0/Php72.php
		 */
		$s .= $s;
		$len = \strlen($s);

		for ($i = $len >> 1, $j = 0; $i < $len; ++$i, ++$j) {
			switch (true) {
				case $s[$i] < "\x80": $s[$j] = $s[$i]; break;
				case $s[$i] < "\xC0": $s[$j] = "\xC2"; $s[++$j] = $s[$i]; break;
				default: $s[$j] = "\xC3"; $s[++$j] = \chr(\ord($s[$i]) - 64); break;
			}
		}

		return substr($s, 0, $j);
	}

	/**
	 * Converts a UTF-8 string to ISO-8859-1 (Latin-1) encoding.
	 *
	 * Attempts mb_convert_encoding, UConverter, iconv, then a pure-PHP fallback.
	 *
	 * @param   string  $s  The UTF-8 encoded string to be decoded.
	 *
	 * @return  string  The ISO-8859-1 encoded string.
	 * @since   10.0
	 */
	public static function utf8_decode(string $s): string
	{
		if (function_exists('mb_convert_encoding'))
		{
			return mb_convert_encoding($s, 'ISO-8859-1', 'UTF-8');
		}

		if (class_exists(UConverter::class))
		{
			return UConverter::transcode($s, 'ISO-8859-1', 'UTF8');
		}

		if (function_exists('iconv'))
		{
			return iconv('UTF-8', 'ISO-8859-1', $s);
		}

		/**
		 * Fallback to the pure PHP implementation from Symfony Polyfill for PHP 7.2
		 *
		 * @link https://github.com/symfony/polyfill-php72/blob/v1.26.0/Php72.php
		 */
		$s = (string) $s;
		$len = \strlen($s);

		for ($i = 0, $j = 0; $i < $len; ++$i, ++$j) {
			switch ($s[$i] & "\xF0") {
				case "\xC0":
				case "\xD0":
					$c = (\ord($s[$i] & "\x1F") << 6) | \ord($s[++$i] & "\x3F");
					$s[$j] = $c < 256 ? \chr($c) : '?';
					break;

				case "\xF0":
					++$i;
				// no break

				case "\xE0":
					$s[$j] = '?';
					$i += 2;
					break;

				default:
					$s[$j] = $s[$i];
			}
		}

		return substr($s, 0, $j);
	}
}