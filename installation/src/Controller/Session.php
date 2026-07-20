<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Controller;

use Akeeba\BRS\Framework\Mvc\Controller;

defined('_AKEEBA') or die();

/**
 * Controller for the unwriteable session page view
 *
 * @since  10.0
 */
class Session extends Controller
{
	/**
	 * Default task.
	 *
	 * If the storage is working it will immediately redirect to the main page of the application.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function main(): void
	{
		if ($this->getContainer()->get('session')->isStorageWorking())
		{
			$this->setRedirect('index.php?view=main');

			return;
		}

		$this->display();
	}
}