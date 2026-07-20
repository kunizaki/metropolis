<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Step;

use Akeeba\BRS\Framework\Cli\AbstractInstallationStep;

defined('_AKEEBA') or die();

class Finalise extends AbstractInstallationStep
{
	/**
	 * @inheritDoc
	 */
	public function isApplicable(): bool
	{
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): void
	{
		// No-op
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): void
	{
		// No-op
	}
}