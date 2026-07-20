<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\String;

defined('_AKEEBA') or die();

/**
 * String handling helper
 *
 * @since  10.0
 */
final class StringHelper
{
	private function __construct()
	{
		throw new \RuntimeException('This class must not be instantiated.');
	}

	/**
	 * UTF-8 aware replacement for trim()
	 *
	 * Strip whitespace (or other characters) from the beginning and end of a string.
	 *
	 * This only needs to be used if the character list argument contains UTF-8 characters. Otherwise, PHP's standard
	 * `trim()` will work just fine.
	 *
	 * This method is an adaptation of the php_utf8 library code by Andreas Gohr.
	 *
	 * @param   string       $str       The string to be trimmed
	 * @param   string|null  $charlist  The optional charlist of additional characters to trim
	 *
	 * @return  string  The trimmed string
	 * @since   10.0
	 */
	public static function trim(string $str, ?string $charlist = null): string
	{
		if ($charlist === null)
		{
			return trim($str);
		}

		if (empty($charlist))
		{
			return $str;
		}

		$charlist = preg_replace('!([\\\\\\-\\]\\[/^])!', '\\\${1}', $charlist);

		// Left trim
		$str = preg_replace('/^[' . $charlist . ']+/u', '', $str);
		// Right trim
		$str = preg_replace('/[' . $charlist . ']+$/u', '', $str);

		return $str;
	}

	/**
	 * UTF-8 aware replacement for strlen()
	 *
	 * @param   string  $string  The string whose length is to be determined
	 *
	 * @return  int  The length of the string
	 */
	public static function strlen(string $string): int
	{
		if (function_exists('mb_strlen'))
		{
			return mb_strlen($string, 'UTF-8');
		}

		return strlen($string);
	}

	/**
	 * UTF-8 safe wordwrap() replacement
	 *
	 * This method safely wraps a UTF-8 encoded string to a given number of characters.
	 *
	 * @param   string  $string  The input string
	 * @param   int     $width   The number of characters at which the string will be wrapped
	 * @param   string  $break   The line break character (default: "\n")
	 * @param   bool    $cut     Whether to force break words longer than the width
	 *
	 * @return  string  The wrapped string
	 */
	public static function wordwrap(string $string, int $width = 75, string $break = "\n", bool $cut = false
	): string
	{
		if (self::strlen($string) <= $width || $width <= 0)
		{
			return $string;
		}

		$lines = explode($break, $string);

		foreach ($lines as &$line)
		{
			$line = rtrim($line);

			if (self::strlen($line) <= $width)
			{
				continue;
			}

			$words  = explode(' ', $line);
			$line   = '';
			$actual = '';

			foreach ($words as $word)
			{
				if (self::strlen($actual . $word) <= $width)
				{
					$actual .= $word . ' ';
				}
				else
				{
					if ($actual != '')
					{
						$line .= rtrim($actual) . $break;
					}

					$actual = $word;

					if ($cut)
					{
						while (self::strlen($actual) > $width)
						{
							$line   .= self::substr($actual, 0, $width) . $break;
							$actual = self::substr($actual, $width);
						}
					}

					$actual .= ' ';
				}
			}

			$line .= trim($actual);
		}

		return implode($break, $lines);
	}


	/**
	 * UTF-8 aware replacement for substr()
	 *
	 * Returns the portion of the string specified by the start and length parameters.
	 *
	 * @param   string    $string  The input string
	 * @param   int       $start   The position to start extraction
	 * @param   int|null  $length  The maximum length of the extracted string
	 *
	 * @return  string  The extracted portion of the string
	 * @since   10.0
	 */
	public static function substr(string $string, int $start, ?int $length = null): string
	{
		if (function_exists('mb_substr'))
		{
			return mb_substr($string, $start, $length, 'UTF-8');
		}

		return is_null($length) ? substr($string, $start) : substr($string, $start, $length);
	}

	/**
	 * UTF-8 aware replacement for strpos().
	 *
	 * @param   string  $haystack  The string to search in.
	 * @param   string  $needle    The substring to search for.
	 * @param   int     $offset    Optional. The position in the string to start searching. Default is 0.
	 *
	 * @return  int|false The numeric position of the first occurrence of the needle in the haystack, or false if not found.
	 */
	public static function strpos(string $haystack, string $needle, int $offset = 0)
	{
		if (function_exists('mb_substr'))
		{
			return mb_strpos($haystack, $needle, $offset, 'UTF-8');
		}

		return strpos($haystack, $needle, $offset);
	}

	/**
	 * UTF-8 aware replacement for str_pad()
	 *
	 * @param   string  $string      The input string
	 * @param   int     $length      The target length of the padded string
	 * @param   string  $pad_string  The string to pad with, defaults to a single space
	 * @param   int     $pad_type    The padding type (STR_PAD_RIGHT, STR_PAD_LEFT, STR_PAD_BOTH, etc.)
	 *
	 * @return  string  The padded string
	 */
	public static function str_pad(string $string, int $length, string $pad_string = ' ', int $pad_type = STR_PAD_RIGHT)
	{
		if (function_exists('mb_str_pad'))
		{
			return mb_str_pad($string, $length, $pad_string, $pad_type, 'UTF-8');
		}

		return str_pad($string, $length, $pad_string, $pad_type);
	}
}