<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\View\Publicfolder;

use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\Platform\Model\Publicfolder;
use Akeeba\BRS\View\ShowOnTrait;
use Akeeba\BRS\View\StepsTrait;

defined('_AKEEBA') or die();

class Html extends View
{
	use StepsTrait;
	use ShowOnTrait;

	/**
	 * The backed up site's public folder.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $oldPublic;

	/**
	 * The backed up site's root folder.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $oldRoot;

	/**
	 * Used to convey model state to the frontend.
	 *
	 * @var   object
	 * @since 10.0
	 */
	protected $stateVars;

	/**
	 * Are we running under Windows?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $isWindows;

	/**
	 * Is the restored site currently being served directly, without a separate public folder?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $isServedDirectly;

	/**
	 * Is the restored site currently being served through a separate public folder?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $isServedFromPublic;

	/**
	 * True if the user is given no choice about whether to use a public folder or not.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $noChoice;

	/**
	 * True when we need to hide the restoration interface.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $hideInterface;

	/**
	 * @return  bool
	 */
	public function onBeforeMain(): bool
	{
		/** @var Publicfolder $model */
		$model                    = $this->getModel();
		$this->oldPublic          = $this->getContainer()->get('configuration')->publicFolder->oldPublic ?: '???';
		$this->oldRoot            = $this->getContainer()->get('configuration')->publicFolder->oldRoot ?: '???';
		$this->isWindows          = substr(strtolower(PHP_OS), 0, 3) === 'win';
		$this->isServedDirectly   = $model->isServedDirectly();
		$this->isServedFromPublic = $model->isServedFromPublic();
		$this->hideInterface      = $this->isWindows
		                            || ($this->isServedDirectly && !$this->isServedFromPublic);
		$this->noChoice           = $this->isWindows
		                            || ($this->isServedDirectly && !$this->isServedFromPublic)
		                            || ($this->isServedFromPublic && !$this->isServedDirectly);
		$useSplit                 = !$this->isWindows;

		if ($this->isServedDirectly && !$this->isServedFromPublic)
		{
			$useSplit = false;
		}

		$this->stateVars = (object) [
			'usesplit'     => $useSplit,
			'newpublic'    => $model->getState('newpublic', $model->getDefaultPublicFolder()),
			'samplefolder' => $model->getExamplePublicRoot(),
		];

		$doc = $this->container->get('application')->getDocument();

		$doc->addMediaScript('publicfolder.js');
		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/joomla-publicfolder.html');

		$this->addButtonPreviousStep();
		$this->addButtonSubmitStep();

		return true;
	}
}