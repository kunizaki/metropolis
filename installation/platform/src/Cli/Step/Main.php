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
use Akeeba\BRS\Platform\Model\Configuration;

class Main extends \Akeeba\BRS\Cli\Step\Main
{
	use WriteStepTrait;

	public function execute(): void
	{
		parent::execute();

		$this->writeStep('Loading existing configuration');

		// Load the default configuration and save it to the session
		/** @var Configuration $model */
		$model = $this->getContainer()->get('mvcFactory')->model('Configuration');
		$this->getContainer()->get('session')->saveData();

		// Try to load the configuration from the site's configuration.php
		$filename = $this->getContainer()->get('paths')->get('site') . '/configuration.php';

		if (!file_exists($filename))
		{
			$this->writeStep('No configuration.php found');

			return;
		}

		$vars = $model->loadFromFile($filename);

		foreach ($vars as $k => $v)
		{
			$model->set($k, $v);
		}

		$this->getContainer()->get('session')->saveData();
	}

}