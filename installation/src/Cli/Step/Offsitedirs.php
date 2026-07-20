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
use Akeeba\BRS\Framework\Cli\Exception\ValidationException;
use Akeeba\BRS\Framework\Configuration\Folders;
use Akeeba\BRS\Framework\Console\Color;

class Offsitedirs extends AbstractInstallationStep
{
	use WriteStepTrait;

	/**
	 * @inheritDoc
	 */
	public function isApplicable(): bool
	{
		return !empty($this->getSubstep()) && $this->getSubstep() !== 'JPATH_PUBLIC';
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): void
	{
		$configuration = $this->getConfiguration();

		if ($configuration['skip'])
		{
			return;
		}

		$target = $configuration['target'];

		if (empty($target) || !@is_dir($target) || !@is_writeable($target))
		{
			throw new ValidationException(
				sprintf(
					'Invalid %s.%s.target: must be an existing, writeable folder',
					$this->getIdentifier(),
					$this->getSubstep()
				)
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): void
	{
		$model         = $this->getThisModel();
		$configuration = $this->getConfiguration();
		$input         = $this->getContainer()->get('input');
		$output        = $this->getContainer()->get('output');
		$quiet         = $input->getOption('quiet', false, 'bool');

		if (!$quiet)
		{
			$output->heading(sprintf('Off-site files restoration for ‘%s’', $this->getSubstep()));
		}

		if ($configuration['skip'])
		{
			$cRed = new Color('red');

			$this->writeStep($cRed('Skipped'));

			return;
		}

		$this->writeStep('Moving files');
		$model->moveDir($this->getSubstep(), $configuration['target'] ?? null);
	}

	/** @inheritDoc */
	protected function getDefaultConfiguration(): array
	{
		return [
			'skip'   => false,
			'target' => $this->getFolderDefinition()->name,
		];
	}

	/**
	 * Get the current sub step's folder definitions.
	 *
	 * @return  null|Folders
	 * @since   10.0
	 */
	private function getFolderDefinition(): ?Folders
	{
		return array_reduce(
			$this->getContainer()->get('configuration')->folders,
			function (?Folders $carry, Folders $folder) {
				return $carry ?? ($folder->virtual === $this->getSubStep() ? $folder : null);
			}
		);
	}

	/**
	 * Get the model specific to this step.
	 *
	 * @return  \Akeeba\BRS\Platform\Model\Publicfolder
	 * @since   10.0
	 */
	private function getThisModel(): \Akeeba\BRS\Model\Offsitedirs
	{
		return $this->getContainer()->get('mvcFactory')->model('Offsitedirs');
	}
}