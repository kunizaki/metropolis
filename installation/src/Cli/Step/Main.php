<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Step;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Cli\AbstractInstallationStep;

/**
 * CLI installation `main` step.
 *
 * @since  10.0
 */
class Main extends AbstractInstallationStep
{
	use WriteStepTrait;

	public function isApplicable(): bool
	{
		return true;
	}

	public function validate(): void
	{
		// No-op
	}

	public function execute(): void
	{
		$output = $this->getContainer()->get('output');

		$output->heading('Initialising restoration');

		$this->writeStep('Detecting versions');
		/** @var \Akeeba\BRS\Model\Main $mainModel */
		$mainModel = $this->getContainer()->get('mvcFactory')->model('Main');
		$mainModel->detectVersion();
	}
}