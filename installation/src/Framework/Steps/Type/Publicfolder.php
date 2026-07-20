<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Steps\Type;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Steps\StepItem;

/**
 * Step type handler for the `publicfolder` step.
 *
 * @since  10.0
 */
class Publicfolder extends AbstractStepType
{
	/**
	 * @inheritDoc
	 */
	public function getStepData(): array
	{
		if (!$this->getContainer()->get('configuration')->publicFolder->custom)
		{
			return [];
		}

		return [
			new StepItem($this->getContainer(), 'publicfolder')
		];
	}
}