<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Controller;

defined('_AKEEBA') or die();

use Akeeba\BRS\Controller\AbstractMain;
use Akeeba\BRS\Platform\Model\Configuration;

class Main extends AbstractMain
{
	/**
	 * Try to read Joomla's configuration.php and populate the Configuration model.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function getconfig(): void
	{
		// Load the default configuration and save it to the session
		/** @var Configuration $model */
		$model = $this->getContainer()->get('mvcFactory')->model('Configuration');
		$this->getContainer()->get('session')->saveData();

		// Try to load the configuration from the site's configuration.php
		$filename = $this->getContainer()->get('paths')->get('site') . '/configuration.php';

		if (file_exists($filename))
		{
			$vars = $model->loadFromFile($filename);

			foreach ($vars as $k => $v)
			{
				$model->set($k, $v);
			}

			$this->getContainer()->get('session')->saveData();

			@ob_clean();
			echo json_encode(true);
		}
		else
		{
			@ob_clean();
			echo json_encode(false);
		}
	}
}