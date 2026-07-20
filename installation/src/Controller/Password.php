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
 * Controller for the password view.
 *
 * @since  10.0
 */
class Password extends Controller
{
	public function unlock(): void
	{
		/** @var \Akeeba\BRS\Model\Password $model */
		$model    = $this->getThisModel();
		$password = $this->getContainer()->get('input')->getRaw('password', '');

		if (!$model->isPasswordCorrect($password))
		{
			$this->getContainer()->get('session')->reset();
			$this->setRedirect(
				'index.php?view=password',
				$this->getContainer()->get('language')->text('PASSWORD_ERR_INVALID_PASSWORD'),
				'error'
			);

			return;
		}
		$this->getContainer()->get('session')->set('brs.unlocked', true);
		$this->getContainer()->get('session')->saveData();
		$this->setRedirect('index.php?view=main');
	}
}