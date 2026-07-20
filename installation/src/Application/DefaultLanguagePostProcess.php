<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Application;

defined('_AKEEBA') or die();

final class DefaultLanguagePostProcess
{
	public function __invoke(string $filename, array $strings)
	{
		return array_map(function (string $string): string {
			return str_replace('\\n', "\n", $string);
		}, $strings);
	}

}