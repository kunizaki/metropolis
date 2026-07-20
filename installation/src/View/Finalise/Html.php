<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View\Finalise;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\Model\Finalise;
use Akeeba\BRS\View\StepsTrait;

/**
 * View Controller for the final page of the restoration script.
 *
 * @since  10.0
 */
class Html extends View
{
	use StepsTrait;

	/**
	 * Should I show the configuration file contents?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $showConfig = false;

	/**
	 * The name of the configuration file.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	public $configFilename = null;

	/**
	 * The contents of the configuration file.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $configuration = '';

	/**
	 * Any additional warnings to display at the top of the final page.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $extra_warning = '';

	/**
	 * Runs before displaying the default task.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function onBeforeMain(): bool
	{
		/** @var Finalise $model */
		$model = $this->getModel();
		$this->showConfig     = $model->getShowConfig();
		$this->configFilename = $model->getConfigFilename();
		$this->configuration  = $this->showConfig ? $model->getConfigContents() : '';

		/** @var \Akeeba\BRS\Framework\Document\Html $doc */
		$doc = $this->getContainer()->get('application')->getDocument();

		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/finalise.html');

		$this->addButtonPreviousStep();
		$this->addButtonNextStep();

		$doc->addMediaScript('finalise.js');

		return true;
	}
}