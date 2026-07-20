<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Controller;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\Controller;

/**
 * Controller for the final page of the restoration script.
 *
 * @since  10.0
 */
class Finalise extends Controller
{
	/**
	 * Runs before the default task handler.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function onBeforeMain(): bool
	{
		$defaultShowConfig = !$this->getContainer()->get('session')->get('writtenConfiguration', true);

		// Set the model's `showconfig` state from the request.
		$this->getModel()->setState(
			'showconfig',
			$this->getContainer()->get('input')->get->getBool('showconfig', $defaultShowConfig)
		);

		return true;
	}

	/**
	 * Deletes the installation folder.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function cleanup(): void
	{
		$result = true;

		try
		{
			$result = $this->getModel()->cleanup();
		}
		catch (\Exception $e)
		{
			$result = false;
		}

		@ob_end_clean();
		echo '###' . json_encode($result) . '###';

		$this->getContainer()->get('application')->close();
	}
}