<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View\Offsitedirs;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Configuration\Folders;
use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\View\StepsTrait;

/**
 * The View Controller for restoring off-site directory contents.
 *
 * @since  10.0
 */
class Html extends View
{
	use StepsTrait;

	/**
	 * The key of the current off-site directory to restore.
	 *
	 * @var   Folders|null
	 * @since 10.0
	 */
	public $substep;

	public function onBeforeMain(): bool
	{
		/** @var \Akeeba\BRS\Framework\Document\Html $doc */
		$doc = $this->getContainer()->get('application')->getDocument();

		$doc->addMediaScript('offsitedirs.js');
		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/offsite.html');

		$this->addButtonPreviousStep();
		$this->addButtonNextStep(true);
		$this->addButtonSubmitStep();

		$this->substep = array_reduce(
			$this->getContainer()->get('configuration')->folders,
			function (?Folders $carry, Folders $folder) {
				return $carry ?? ($folder->virtual === $this->container->get('steps')->current()->getSubStep() ? $folder : null);
			}
		);

		$doc->addScriptOptions('brs.offsitedirs.key', $this->substep ? $this->substep->virtual : null);

		return true;
	}

}