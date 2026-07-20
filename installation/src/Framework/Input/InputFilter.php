<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Input;

use Akeeba\BRS\Framework\String\StringHelper;

defined('_AKEEBA') or die();

/**
 * A simple input filter abstraction layer.
 *
 * Note: Unlike Joomla's InputFilter this is NOT meant to handle HTML. This is intentional. Site restoration is de facto
 * performed by a trusted user with full access to the hosting server. Use the extraction script's Stealth Mode, or
 * password-protect the installer to ensure this is always the case.
 *
 * @since  10.0
 */
final class InputFilter
{
	/**
	 * Cleans the input data using the filter type specified.
	 *
	 * @param   mixed   $source  Input data to clean.
	 * @param   string  $type    The filter type to apply.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function clean($source, string $type = 'string')
	{
		// Handle arrays
		if (\is_array($source))
		{
			return array_map(
				function ($item) use ($type) {
					return $this->clean($item, $type);
				}, $source
			);
		}

		// Handle objects
		if (\is_object($source))
		{
			return (object) $this->clean(get_object_vars($source), $type);
		}

		// Non-scalar values other than array or object (null, resource, callable) are returned as-is.
		if (!is_scalar($source))
		{
			return $source;
		}

		$type = strtolower($type);

		if ($type === 'raw')
		{
			return $source;
		}

		if ($type === 'array')
		{
			return (array) $source;
		}

		$method = 'clean' . $type;
		$method = method_exists($this, $method) ? $method : 'cleanString';

		return $this->$method((string) $source);
	}

	/**
	 * Integer filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  int
	 * @since   10.0
	 */
	private function cleanInt(string $source): int
	{
		return (int) filter_var($source, FILTER_SANITIZE_NUMBER_INT);
	}

	/**
	 * Alias for cleanInt()
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  int
	 * @since   10.0
	 */
	private function cleanInteger(string $source): int
	{
		return $this->cleanInt($source);
	}

	/**
	 * Unsigned integer filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  int
	 * @since   10.0
	 */
	private function cleanUint(string $source): int
	{
		return abs($this->cleanInt($source));
	}

	/**
	 * Float filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  float
	 * @since   10.0
	 */
	private function cleanFloat(string $source): float
	{
		return (float) filter_var($source, FILTER_SANITIZE_NUMBER_FLOAT);
	}

	/**
	 * Alias for cleanFloat()
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  float
	 * @since   10.0
	 */
	private function cleanDouble(string $source): float
	{
		return $this->cleanFloat($source);
	}

	/**
	 * A Boolean filter. This filter understands fuzzy boolean strings such as 0/1, yes/no, true/false, on/off.
	 *
	 * @param   string|bool  $source  The string to be filtered
	 *
	 * @return  bool
	 * @since   10.0
	 * @link    https://www.php.net/manual/en/filter.constants.php#constant.filter-validate-bool
	 */
	private function cleanBool($source): bool
	{
		if (is_bool($source))
		{
			return $source;
		}

		if (!is_string($source))
		{
			return (bool) $source;
		}

		// Filter name was FILTER_VALIDATE_BOOLEAN before PHP 8.0, it is FILTER_VALIDATE_BOOL from PHP 8.0 onwards.

		/** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
		return filter_var(
			$source,
			version_compare(PHP_VERSION, '8.0.0', 'ge') ? FILTER_VALIDATE_BOOL : FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Alias for cleanBool()
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function cleanBoolean(string $source): bool
	{
		return $this->cleanBool($source);
	}

	/**
	 * Word filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanWord(string $source): string
	{
		$pattern = '/[^A-Z_]/i';

		return preg_replace($pattern, '', $this->cleanString($source));
	}

	/**
	 * Alphanumerical filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanAlnum(string $source): string
	{
		$pattern = '/[^A-Z0-9]/i';

		return preg_replace($pattern, '', $this->cleanString($source));
	}

	/**
	 * Command filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanCmd(string $source): string
	{
		$pattern = '/[^A-Z0-9_\.-]/i';

		$result = preg_replace($pattern, '', $this->cleanString($source));
		$result = ltrim($result, '.');

		return $result;
	}

	/**
	 * Base64 filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanBase64(string $source): string
	{
		$pattern = '/[^A-Z0-9\/+=]/i';

		return preg_replace($pattern, '', $this->cleanString($source));
	}

	/**
	 * String filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanString(string $source): ?string
	{
		return strip_tags(filter_var($source, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW));
	}

	/**
	 * Path filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanPath(string $source): string
	{
		$linuxPattern = '/^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/]+[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

		if (preg_match($linuxPattern, $source))
		{
			return preg_replace('~/+~', '/', $source);
		}

		$windowsPattern = '/^([A-Za-z]:(\\\\|\/))?[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*((\\\\|\/)+[A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

		if (preg_match($windowsPattern, $source))
		{
			return preg_replace('~(\\\\|\/)+~', '\\', $source);
		}

		return '';
	}

	/**
	 * Trim filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanTrim(string $source): string
	{
		$result = trim($source);
		$result = StringHelper::trim($result, \chr(0xE3) . \chr(0x80) . \chr(0x80));
		$result = StringHelper::trim($result, \chr(0xC2) . \chr(0xA0));

		return $result;
	}

	/**
	 * Username filter
	 *
	 * @param   string  $source  The string to be filtered
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function cleanUsername(string $source): string
	{
		$pattern = '/[\x00-\x1F\x7F<>"\'%&]/';

		return preg_replace($pattern, '', $source);
	}
}
