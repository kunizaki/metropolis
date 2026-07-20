<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Cli\Step;

defined('_AKEEBA') or die();

use Akeeba\BRS\Cli\Step\WriteStepTrait;
use Akeeba\BRS\Framework\Cli\AbstractInstallationStep;
use Akeeba\BRS\Framework\Cli\Exception\ValidationException;

class Publicfolder extends AbstractInstallationStep
{
	use WriteStepTrait;

	/**
	 * @inheritDoc
	 */
	public function isApplicable(): bool
	{
		return !$this->getThisModel()->isServedDirectly();
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): void
	{
		$model              = $this->getThisModel();
		$paths              = $this->getContainer()->get('paths');
		$isWindows          = substr(strtolower(PHP_OS), 0, 3) === 'win';
		$isServedDirectly   = $model->isServedDirectly();
		$isServedFromPublic = $model->isServedFromPublic();
		$noChoice           = $isWindows
		                      || ($isServedDirectly && !$isServedFromPublic)
		                      || ($isServedFromPublic && !$isServedDirectly);
		$useSplit           = !$isWindows;

		$configuration = $this->getConfiguration();

		if ($noChoice && $configuration['usepublic'] != $useSplit)
		{
			throw new ValidationException(
				sprintf(
					'Invalid %s.usepublic: You must use %s or ommit the configuration value altogether.',
					$this->getIdentifier(),
					$useSplit ? 'TRUE' : 'FALSE'
				)
			);
		}

		if ($configuration['usepublic'])
		{
			$newDir = trim($configuration['newpublic'] ?: $paths->get('root'));

			if (empty($newDir) || !@is_dir($newDir) || !@is_writeable($newDir))
			{
				throw new ValidationException(
					sprintf(
						'Invalid %s.newpublic: You must use an existing, writeable directory.',
						$this->getIdentifier()
					)
				);
			}
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
		$paths         = $this->getContainer()->get('paths');
		$isQuiet       = $input->getOption('quiet', false, 'bool');

		$model->setState('usepublic', (bool) $configuration['usepublic']);
		$model->setState('newpublic', $configuration['newpublic'] ?: $paths->get('root'));

		if (!$isQuiet)
		{
			$output->heading('Processing Joomla! Public Folder settings');
		}

		$this->writeStep('Checking settings');
		$model->checkSettings();

		$root      = $paths->get('root');
		$usePublic = $model->getState('usepublic', true);
		$target    = $usePublic ? $model->getState('newpublic', $root) : $root;

		if ($target === $root)
		{
			$this->writeStep('Deleting obsolete files');
			$model->deleteFilesOnRevertingPublicFolder();
		}

		$this->writeStep('Copying necessary Joomla! files to the public folder');
		$model->moveBasicFiles();

		$this->writeStep('Moving files from external_files/JPATH_PUBLIC to the public folder.');
		$model->recursiveMove();

		$this->writeStep('Updating paths in the public folder files');
		$model->autoEditFiles();

		$this->writeStep('Removing the external_files/JPATH_PUBLIC folder');
		$model->removeExternalFilesContainerFolder();

		$this->writeStep('Updating internal indices');
		$usePublic = (bool) $model->getState('usepublic', true);
		$newPublic = $model->getState('newpublic');

		$this->getContainer()->get('session')->set(
			'joomla.public_folder', $usePublic ? $newPublic : $paths->get('root')
		);
	}

	/** @inheritDoc */
	protected function getDefaultConfiguration(): array
	{
		$model              = $this->getThisModel();
		$isWindows          = substr(strtolower(PHP_OS), 0, 3) === 'win';
		$isServedDirectly   = $model->isServedDirectly();
		$isServedFromPublic = $model->isServedFromPublic();

		return [
			'usepublic' => $isServedFromPublic && !$isWindows,
			'newpublic' => $isWindows || ($isServedDirectly && !$isServedFromPublic)
				? ''
				: $model->getState('newpublic', $model->getDefaultPublicFolder()),
		];
	}


	/**
	 * Get the model specific to this step.
	 *
	 * @return  \Akeeba\BRS\Platform\Model\Publicfolder
	 * @since   10.0
	 */
	private function getThisModel(): \Akeeba\BRS\Platform\Model\Publicfolder
	{
		return $this->getContainer()->get('mvcFactory')->model('Publicfolder');
	}
}