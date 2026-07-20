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
 * Step type handler for the `database` step.
 *
 * @since  10.0
 */
final class Database extends AbstractStepType
{
	/** @inheritdoc */
	public function getStepData(): array
	{
		return array_map(
			function (\Akeeba\BRS\Framework\Configuration\Database $database) {
				return new StepItem($this->getContainer(), 'database', basename($database->sqlfile, '.sql'));
			},
			$this->getContainer()->get('configuration')->databases
		);
	}
}