<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Controller;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\Controller;

/**
 * Abstract implementation of the main page controller
 *
 * @since  10.0
 */
class AbstractMain extends Controller
{
	/**
	 * Shows the "Initialising…" page; the very first page of the installer.
	 *
	 * @return  void
	 * @throws \Throwable
	 * @since   10.0
	 */
	public function init(): void
	{
		$this->layout = 'init';

		$this->getThisModel()->resetDatabaseConnectionInformationOnNewHost();
		$this->display();
	}

	/**
	 * Starts the restoration over.
	 *
	 * Resets everything, and starts over. This is the “turn it off and on again” switch, basically.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function startover(): void
	{
		$session = $this->getContainer()->get('session');

		$session->reset();
		$session->saveData();

		$this->setRedirect('index.php?view=main');
	}

	/**
	 * Populate the Configuration model with the current site's configuration.
	 *
	 * This needs to be overridden in each platform.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function getconfig(): void
	{
		@ob_clean();
		echo json_encode(true);
	}

	/**
	 * Try to detect the CMS version.
	 *
	 * This takes place in an isolated AJAX call to prevent any errors from ruining someone's already stressful day.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function detectversion(): void
	{
		$model = $this->getThisModel();
		$model->detectVersion();

		@ob_clean();
		echo json_encode(true);
	}
}