<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Document;

defined('_AKEEBA') or die();

trait MediaFolderTrait
{
	/**
	 * Find the most appropriate media file.
	 *
	 * The rules for finding the most appropriate file are:
	 *
	 * * Files in installation/platform/media have priority over those in installation/media
	 * * When AKEEBA_DEBUG is unset or false non-minified files have priority over minified files
	 * * When AKEEBA_DEBUG is set and true minified files have priority over non-minified files
	 *
	 * @param   string  $mediaRelative  The file to look for, e.g. `foo.js`, or `bar.css`.
	 * @param   string  $suffix         The media subdirectory and file suffix, e.g. `js`, or `css`.
	 *
	 * @return  array{relative: string, absolute: string}
	 * @since   10.0
	 */
	private function getScriptFilePath(string $mediaRelative, string $suffix = 'js'): array
	{
		$paths         = $this->getContainer()->get('paths');
		$mediaRelative = ltrim($mediaRelative, '/');
		$longLength    = 5 + strlen($suffix);
		$shortLength   = 1 + strlen($suffix);
		$longSuffix    = '.min.' . $suffix;
		$shortSuffix   = '.' . $suffix;
		$bare          = str_ends_with($mediaRelative, '.min.' . $suffix)
			? substr($mediaRelative, 0, -$longLength)
			: substr($mediaRelative, 0, -$shortLength);
		$isDebug       = defined('AKEEBA_DEBUG') && constant('AKEEBA_DEBUG');
		$possibleFiles = [
			$bare . ($isDebug ? $longSuffix : $shortSuffix),
			$bare . ($isDebug ? $shortSuffix : $longSuffix),
		];

		foreach ($possibleFiles as $possibleFile)
		{
			$prefix       = 'platform/media/' . $suffix . '/';
			$absolutePath = $paths->get('installation') . '/platform/media/' . $suffix . '/' . $possibleFile;

			if (!file_exists($absolutePath))
			{
				$prefix       = 'media/' . $suffix . '/';
				$absolutePath = $paths->get('media') . '/' . $suffix . '/' . $possibleFile;
			}

			if (!file_exists($absolutePath))
			{
				continue;
			}

			return [
				'relative' => $prefix . $possibleFile,
				'absolute' => $absolutePath,
			];
		}

		return [
			'relative' => 'media/js/' . $mediaRelative,
			'absolute' => $paths->get('media') . '/js/' . $mediaRelative,
		];
	}
}