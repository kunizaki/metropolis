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

use Akeeba\BRS\Framework\Buffer\Buffer;
use Throwable;

/**
 * A configuration parser class which includes the file after some mild pre-processing.
 *
 * @since  10.0
 */
class IncludeParser extends AbstractParser
{
	/** @inheritDoc */
	protected $priority = 1000;

	/** @inheritDoc */
	public function isSupported(): bool
	{
		return version_compare(PHP_VERSION, '7.0.0', 'ge')
		       && Buffer::canRegisterWrapper();
	}

	/** @inheritDoc */
	public function parseFile(string $file, string $className): array
	{
		try
		{
			// Create a random class name
			do
			{
				$randomClass = 'ParseFile' . str_replace(['+', '/', '='], [
						'Z', 'X', '',
					], base64_encode(random_bytes(32)));
			} while (class_exists($randomClass));

			// Get the original file's contents and replace the class name
			$contents = file_get_contents($file);
			$contents = str_replace(' ' . $className . ' ', ' ' . $randomClass . ' ', $contents);
			$contents = str_replace(' ' . $className . "\n", $randomClass . "\n", $contents);
			$contents = str_replace(' ' . $className . "\r", $randomClass . "\r", $contents);

			// Use the memory buffer to include the modified file
			file_put_contents('buffer://temp.php', $contents);

			include('buffer://temp.php');

			// This should never happen. If it does, we fall back to the legacy parser
			if (!class_exists($randomClass))
			{
				throw new \RuntimeException('Oopsie');
			}

			// Create a new object from the random class and return its public properties.
			$o = new $randomClass();

			return get_object_vars($o);
		}
		catch (Throwable $e)
		{
			// Fallback to the legacy parser if the file throws an error.
			$legacyParser = new LegacyParser();

			return $legacyParser->parseFile($file, $className);
		}
	}
}