<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Server;

defined('_AKEEBA') or die();

/**
 * A simple class for extracting PHP handler information from .htaccess files
 *
 * @since  10.0
 */
final class Handler
{
	/**
	 * Extract the PHP handler configuration from a .htaccess file.
	 *
	 * @param   string  $htaccess
	 *
	 * @return  string|null  NULL when not found
	 */
	public static function extractHandler(string $htaccess): ?string
	{
		$invokable = new self();

		return $invokable($htaccess);
	}

	/**
	 * Extract the PHP handler configuration from a .htaccess file.
	 *
	 * This method supports AddHandler lines and SetHandler blocks.
	 *
	 * @param   string  $htaccess
	 *
	 * @return  string|null  NULL when not found
	 */
	public function __invoke(string $htaccess): ?string
	{
		// Normalize the .htaccess
		$htaccess = $this->normalizeHtaccess($htaccess);

		// Look for SetHandler and AddHandler in Files and FilesMatch containers
		foreach (['Files', 'FilesMatch'] as $container)
		{
			$result = $this->extractContainer($container, $htaccess);

			if (!is_null($result))
			{
				return $result;
			}
		}

		// Fallback: extract an AddHandler line
		$found = preg_match('#^AddHandler\s?.*\.php.*$#mi', $htaccess, $matches);

		if ($found >= 1)
		{
			return $matches[0];
		}

		return null;
	}

	/**
	 * Extracts a Files or FilesMatch container with an AddHandler or SetHandler line
	 *
	 * @param   string  $container  "Files" or "FilesMatch"
	 * @param   string  $htaccess   The .htaccess file content
	 *
	 * @return  string|null  NULL when not found
	 */
	protected function extractContainer(string $container, string $htaccess)
	{
		// Try to find the opening container tag e.g. <Files....>
		$pattern = sprintf('#<%s\s*.*\.php.*>#m', $container);
		$found   = preg_match($pattern, $htaccess, $matches, PREG_OFFSET_CAPTURE);

		if (!$found)
		{
			return null;
		}

		// Get the rest of the .htaccess sample
		$openContainer = $matches[0][0];
		$htaccess      = trim(substr($htaccess, intval($matches[0][1]) + strlen($matches[0][0])));

		// Try to find the closing container tag
		$pattern = sprintf('#</%s\s*>#m', $container);
		$found   = preg_match($pattern, $htaccess, $matches, PREG_OFFSET_CAPTURE);

		if (!$found)
		{
			return null;
		}

		// Get the rest of the .htaccess sample
		$htaccess       = trim(substr($htaccess, 0, $matches[$found - 1][1]));
		$closeContainer = $matches[$found - 1][0];

		if (empty($htaccess))
		{
			return null;
		}

		// Now we'll explode remaining lines and find the first SetHandler or AddHandler line
		$lines = array_map('trim', explode("\n", $htaccess));
		$lines = array_filter(
			$lines, function ($line) {
			return preg_match('#(Add|Set)Handler\s?#i', $line) >= 1;
		}
		);

		if (empty($lines))
		{
			return null;
		}

		return $openContainer . "\n" . array_shift($lines) . "\n" . $closeContainer;
	}

	/**
	 * Normalize the .htaccess file content, making it suitable for handler extraction
	 *
	 * @param   string  $htaccess  The original file
	 *
	 * @return  string  The normalized file
	 */
	private function normalizeHtaccess(string $htaccess): string
	{
		// Convert all newlines into UNIX style
		$htaccess = str_replace("\r\n", "\n", $htaccess);
		$htaccess = str_replace("\r", "\n", $htaccess);

		// Return only non-comment, non-empty lines
		$isNonEmptyNonComment = function ($line) {
			$line = trim($line);

			return !empty($line) && (substr($line, 0, 1) !== '#');
		};

		$lines = array_map('trim', explode("\n", $htaccess));

		return implode("\n", array_filter($lines, $isNonEmptyNonComment));
	}
}