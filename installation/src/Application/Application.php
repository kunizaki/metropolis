<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Application;

use Akeeba\BRS\Framework\Application\AbstractApplication;

defined('_AKEEBA') or die();

class Application extends AbstractApplication
{
	/**
	 * @inheritDoc
	 */
	public function initialise()
	{
		$this->getContainer()->get('language')->addIniProcessCallback(new DefaultLanguagePostProcess);

		// Use the default template if none is already set.
		$this->template = $this->template ?? 'default';

		require_once $this->container->get('paths')->get('base') . '/version.php';
	}
}