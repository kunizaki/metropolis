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
 * Interface to a configuration parser class.
 *
 * @since  10.0
 */
interface ParserInterface
{
	/**
	 * Get the parser's priority. Lowest numbers run first.
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getPriority(): int;

	/**
	 * Is the parser supported on this server
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isSupported(): bool;

	/**
	 * Parse a configuration file, returning an array of configuration values
	 *
	 * @param   string  $file       Absolute filesystem path to the file to parse
	 * @param   string  $className  Expected class name
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function parseFile(string $file, string $className): array;

}