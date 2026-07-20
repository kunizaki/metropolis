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
use Throwable;

/**
 * Abstract Controller for the Site Setup step.
 *
 * Each platform implements its own concrete class, taking into account platform-specific requirements for this step.
 *
 * @since  10.0
 */
abstract class AbstractSetup extends Controller
{
	protected $nextView = 'finalise';

	/**
	 * Applies the information submitted in the Site Setup step.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function apply(): void
	{
		/** @var \Akeeba\BRS\Model\AbstractSetup $model */
		$model = $this->getThisModel();
		$input = $this->getContainer()->get('input');
		$msg   = null;
		$type  = null;

		$model->setState('removephpini', $input->getBool('removephpini', false));
		$model->setState('replacewebconfig', $input->getBool('replacewebconfig', false));
		$model->setState('removehtpasswd', $input->getBool('removehtpasswd', false));

		try
		{
			$writtenConfiguration = $model->applySettings();
			$url                  = 'index.php?view=' . $this->nextView;

			$this->getContainer()->get('session')->set('writtenConfiguration', $writtenConfiguration);

			if (!$writtenConfiguration && $this->nextView === 'finalise')
			{
				$url .= '&showconfig=1';
			}
		}
		catch (Throwable $exc)
		{
			$type = 'error';
			$msg  = $exc->getMessage();
			$url  = 'index.php?view=setup';
		}

		$this->setRedirect($url, $msg, $type);

		// Encode the result if we're in JSON format
		if ($this->getContainer()->get('input')->getCmd('format', '') == 'json')
		{
			$result['error'] = $msg;

			@ob_clean();
			echo json_encode($result);
		}
	}
}