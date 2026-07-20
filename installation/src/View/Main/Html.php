<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View\Main;

use Akeeba\BRS\Framework\Document\Html as HtmlDocument;
use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\Framework\RestorationCheck\RestorationCheckInterface;
use Akeeba\BRS\Model\Main;
use Akeeba\BRS\View\StepsTrait;

defined('_AKEEBA') or die();

class Html extends View
{
	use StepsTrait;

	/**
	 * Required pre-restoration checks.
	 *
	 * @var   RestorationCheckInterface[]
	 * @since 10.0
	 */
	public $requiredChecks;

	/**
	 * Recommended pre-restoration checks.
	 *
	 * @var   RestorationCheckInterface[]
	 * @since 10.0
	 */
	public $recommendedChecks;

	/**
	 * Do all recommended checks pass?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $meetsRecommended;

	/**
	 * Do all required checks pass>
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $meetsRequired;

	/**
	 * Site information to present to the user.
	 *
	 * @var   \Akeeba\BRS\DataShape\SiteInfoInterface[]
	 * @since 10.0
	 */
	public $siteInfo;

	/**
	 * Has any of the site information changed between backup and restoration time?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $siteInfoChanged;

	public function onBeforeMain(): bool
	{
		/** @var HtmlDocument $doc */
		$doc = $this->getContainer()->get('application')->getDocument();
		$doc->appendButton(
			'GENERIC_BTN_STARTOVER', 'btn-danger', 'fa-fire', 'startover'
		);
		$doc->appendButton(
			'GENERIC_BTN_RECHECK', 'btn-warning', 'fa-redo', 'checkagain'
		);

		$doc->addMediaScript('main.js');

		return true;
	}

	public function onBeforeInit(): bool
	{
		/** @var HtmlDocument $doc */
		$doc = $this->getContainer()->get('application')->getDocument();
		$doc->appendButton(
			'GENERIC_BTN_STARTOVER', 'btn-danger', 'fa-fire', 'startover'
		);
		$doc->appendButton(
			'GENERIC_BTN_RECHECK', 'btn-warning', 'fa-redo', 'checkagain'
		);
		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/angie-common-main.html');

		$this->addButtonPreviousStep();
		$this->addButtonNextStep();

		$doc->addMediaScript('main.js');

		/** @var Main $model */
		$model                   = $this->getModel();
		$this->requiredChecks    = $model->getRequired();
		$this->recommendedChecks = $model->getRecommended();
		$this->meetsRecommended  = $model->isRecommendedMet();
		$this->meetsRequired     = $model->isRequiredMet();
		$this->siteInfo          = $model->getSiteInfo();
		$this->siteInfoChanged   = $model->isSiteInfoChanged();

		return true;
	}
}