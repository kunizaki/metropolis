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

use Akeeba\BRS\Controller\AbstractSetup;

/**
 * Controller for the Site Setup step for Joomla! sites.
 *
 * @since  10.0
 */
class Setup extends AbstractSetup
{
	public function apply(): void
	{
		$input = $this->getContainer()->get('input');
		$model = $this->getThisModel();

		$model->setState('mailonline', $input->getBool('mailonline', false));
		$model->setState('resetsessionoptions', $input->getBool('resetsessionoptions', false));
		$model->setState('resetcacheoptions', $input->getBool('resetcacheoptions', false));

		parent::apply();
	}
}