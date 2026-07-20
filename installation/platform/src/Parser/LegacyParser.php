<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Parser;

defined('_AKEEBA') or die();

/**
 * A configuration parser class which performs naïve, line-by-line parsing.
 *
 * @since  10.0
 */
class LegacyParser extends AbstractParser
{
	protected $priority = 100000;

	/**
	 * @inheritDoc
	 */
	public function isSupported(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function parseFile(string $file, string $className): array
	{
		$ret          = [];
		$fileContents = file($file);

		foreach ($fileContents as $line)
		{
			$line = trim($line);

			if ((strpos($line, 'public') !== 0) && (strpos($line, 'var') !== 0))
			{
				continue;
			}

			if (strpos($line, 'public') === 0)
			{
				$line = substr($line, 6);
			}
			else
			{
				$line = substr($line, 3);
			}

			$line = trim($line);
			$line = rtrim($line, ';');
			$line = ltrim($line, '$');
			$line = trim($line);

			// Explode only on the first occurrence of the equal sign or it will create invalid values with properties
			// containing an equal (ie URLs)
			[$key, $value] = explode('=', $line, 2);

			$key   = trim($key);
			$value = trim($value);

			// Parse the value depending on what we believe is the right type
			// -- String
			if ((strstr($value, '"') !== false) || (strstr($value, "'") !== false))
			{
				$value = $this->parseStringDefinition($value);
			}
			// -- Boolean
			elseif (in_array($value, ['true', 'false']))
			{
				$value = ($value === 'true');
			}
			// -- Numeric (Joomla only uses integers in its configuration)
			elseif (is_numeric($value))
			{
				$value = (int) $value;
			}
			// -- array, object etc; used in some old Joomla 3 versions but no longer in use for 4.x. Ignored.
			else
			{
				continue;
			}

			$ret[$key] = $value;
		}

		return $ret;
	}

	/**
	 * Parses a string definition, surrounded by single or double quotes.
	 *
	 * It removes any comments which may be left tucked to its end, reducing escaped characters to their unescaped
	 * equivalent, and returning the clean string.
	 *
	 * @param   string  $value
	 *
	 * @return  null|string  Null if we can't parse $value as a string.
	 * @since   10.0
	 */
	private function parseStringDefinition(string $value): ?string
	{
		/**
		 * At this point the value may be in the form 'foobar');#comment'gargh" if the original line was something like
		 * define('DB_NAME', 'foobar');#comment'gargh");
		 */
		$quote = $value[0];

		// The string ends in a different quote character. Backtrack to the matching quote.
		if (substr($value, -1) != $quote)
		{
			$lastQuote = strrpos($value, $quote);

			if ($lastQuote <= 1)
			{
				return null;
			}

			$value = substr($value, 0, $lastQuote + 1);
		}

		/**
		 * At this point the value may be cleared but still in the form 'foobar');#comment'
		 * We need to parse the string like PHP would. First, let's trim the quotes
		 */
		$value = trim($value, $quote);

		$pos = 0;

		while ($pos !== false)
		{
			$pos = strpos($value, $quote, $pos);

			if ($pos === false)
			{
				break;
			}

			if (substr($value, $pos - 1, 1) == '\\')
			{
				$pos++;

				continue;
			}

			$value = substr($value, 0, $pos);
		}

		// Finally, reduce the escaped characters.
		if ($quote == "'")
		{
			// Single quoted strings only escape single quotes and backspaces
			$value = str_replace(["\\'", "\\\\",], ["'", "\\"], $value);
		}
		else
		{
			// Double-quoted strings just need `stripslashes`.
			$value = stripslashes($value);
		}

		return $value;
	}
}