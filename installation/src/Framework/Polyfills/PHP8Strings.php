<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * Polyfills for PHP 8 string functions str_starts_with, str_contains, and str_ends_with.
 *
 * This allows PHP 7 to use these functions, albeit with slightly lower performance.
 */

if (!function_exists('str_starts_with'))
{
	function str_starts_with(string $haystack, string $needle): bool
	{
		return strlen($needle) === 0 || strpos($haystack, $needle) === 0;
	}
}

if (!function_exists('str_contains'))
{
	function str_contains(string $haystack, string $needle): bool
	{
		return strlen($needle) === 0 || strpos($haystack, $needle) !== false;
	}
}

if (!function_exists('str_ends_with'))
{
	function str_ends_with(string $haystack, string $needle): bool
	{
		return strlen($needle) === 0 || substr($haystack, -strlen($needle)) === $needle;
	}
}