<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Step;

use Akeeba\BRS\Framework\Console\Color;

defined('_AKEEBA') or die();

trait WriteStepTrait
{
	/**
	 * Writes a step message to the output unless the "quiet" option is enabled.
	 *
	 * @param   string  $message  The message to be written.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function writeStep(string $message): void
	{
		if ($this->getContainer()->get('input')->getOption('quiet', false, 'bool'))
		{
			return;
		}

		$output   = $this->getContainer()->get('output');
		$cMagenta = new Color('light-magenta');

		$output->writeln(sprintf('  %s %s', $cMagenta('»'), $message));
	}
}