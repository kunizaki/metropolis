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

use Akeeba\BRS\Framework\Mvc\Controller;
use Throwable;

/**
 * The Controller for handling Joomla's public folder relocation.
 *
 * @since  10.0
 */
class Publicfolder extends Controller
{
	public function apply()
	{
		/** @var \Akeeba\BRS\Platform\Model\Publicfolder $model */
		$model      = $this->getThisModel();
		$msg        = null;
		$type       = null;
		$stepsQueue = $this->getContainer()->get('steps');
		$input      = $this->getContainer()->get('input');
		$paths      = $this->getContainer()->get('paths');

		try
		{
			$model->setState('usepublic', $input->getBool('usepublic', true));
			$model->setState('newpublic', $input->getRaw('newpublic', $paths->get('root')));

			$model->checkSettings();
			$model->deleteFilesOnRevertingPublicFolder();
			$model->moveBasicFiles();
			$model->recursiveMove();
			$model->autoEditFiles();
			$model->removeExternalFilesContainerFolder();
			// TODO Conditionally delete the JPATH_PUBLIC key in #__akeeba_common

			$usePublic = (bool) $model->getState('usepublic', true);
			$newPublic = $model->getState('newpublic');
			$paths     = $this->getContainer()->get('paths');
			$this->getContainer()->get('session')->set(
				'joomla.public_folder', $usePublic ? $newPublic : $paths->get('root')
			);

			$url = $stepsQueue->nextStep()->getUri()->toString();
		}
		catch (Throwable $exc)
		{
			$type = 'error';
			$msg  = $exc->getMessage();
			$url  = $stepsQueue->current()->getUri()->toString();
		}

		// Encode the result if we're in JSON format
		if ($this->getContainer()->get('input')->getCmd('format', '') == 'json')
		{
			$result['error'] = $msg;

			@ob_clean();
			echo json_encode($result);

			return;
		}

		// Redirect in HTML mode
		$this->setRedirect($url, $msg, $type);
	}
}