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

use DirectoryIterator;

/**
 * Abstract implementation to a configuration parser class.
 *
 * @since  10.0
 */
abstract class AbstractParser implements ParserInterface
{
	/**
	 * The priority for this parser. Lower number runs first.
	 *
	 * @var   int
	 * @since 9.1.0
	 */
	protected $priority = PHP_INT_MAX;

	/**
	 * Get the best match configuration parser.
	 *
	 * @return  null|ParserInterface
	 * @since   10.0
	 */
	public static function getParser(): ?ParserInterface
	{
		$excluded     = ['AbstractParser', 'ParserInterface'];
		$bestPriority = PHP_INT_MAX;
		$parser       = null;
		$di           = new DirectoryIterator(__DIR__);

		foreach ($di as $file)
		{
			// Ignore folders and non-PHP files
			if ($file->isDot() || !$file->isFile() || $file->getExtension() != 'php')
			{
				continue;
			}

			$baseName = $file->getBasename('.php');

			// Make sure the filename is not one of the forbidden ones
			if (in_array($baseName, $excluded))
			{
				continue;
			}

			// Make sure the filename does not contain any non-alpha characters
			$didMatch = preg_match('#[a-z]*#i', $baseName, $matches);

			if (!$didMatch || $matches[0] !== $baseName)
			{
				continue;
			}

			// Get the classname
			$className = __NAMESPACE__ . '\\' . $baseName;

			if (!class_exists($className, true))
			{
				continue;
			}

			/** @var ParserInterface $o */
			$o = new $className();

			if (!$o->isSupported() || $o->getPriority() > $bestPriority)
			{
				continue;
			}

			$bestPriority = $o->getPriority();
			$parser       = $o;
		}

		return $parser;
	}

	/**
	 * @inheritDoc
	 */
	public function getPriority(): int
	{
		return $this->priority;
	}
}