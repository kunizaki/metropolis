<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View;

use Akeeba\BRS\Framework\Document\Html as HtmlDocument;

defined('_AKEEBA') or die();

trait StepsTrait
{
	/**
	 * Add a button to take you to the next step.
	 *
	 * The $isSkip parameter is used in the database, off-site directories, and data replacement views to skip the
	 * restoration action for the item presented.
	 *
	 * Views with an action or form need to use addButtonSubmitStep instead.
	 *
	 * @param   bool  $isSkip  Set to true to make this a Skip Restoration button.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function addButtonNextStep(bool $isSkip = false): void
	{
		$doc = $this->getContainer()->get('application')->getDocument();

		if (!$doc instanceof HtmlDocument)
		{
			return;
		}

		$nextStep = $this->getContainer()->get('steps')->nextStep();

		if (!$nextStep)
		{
			return;
		}

		$doc->addScriptOptions('nextStep.url', $nextStep->getUri()->toString());

		if ($isSkip)
		{
			$doc->appendButton('GENERAL_BTN_SKIP', 'btn-danger', 'fa-forward', 'nextStep');

			return;
		}

		$doc->appendButton('GENERAL_BTN_NEXT', 'btn-primary', 'fa-chevron-right', 'nextStep');
	}

	/**
	 * Add a Next button with the ID "submitStep".
	 *
	 * This is used in all views where a form needs to be submitted, and/or an action be taken against the information
	 * provided by the user.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function addButtonSubmitStep(): void
	{
		$doc = $this->getContainer()->get('application')->getDocument();

		if (!$doc instanceof HtmlDocument)
		{
			return;
		}

		$doc->appendButton('GENERAL_BTN_NEXT', 'btn-primary', 'fa-chevron-right', 'submitStep');
	}

	/**
	 * Add a Previous button which takes you to the immediately previous step.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function addButtonPreviousStep(): void
	{
		$doc = $this->getContainer()->get('application')->getDocument();

		if (!$doc instanceof HtmlDocument)
		{
			return;
		}

		$previousStep = $this->getContainer()->get('steps')->previousStep();

		if (!$previousStep)
		{
			return;
		}

		$doc->addScriptOptions('previousStep.url', $previousStep->getUri()->toString());
		$doc->appendButton('GENERAL_BTN_PREV', 'btn-dark', 'fa-chevron-left', 'previousStep');
	}
}