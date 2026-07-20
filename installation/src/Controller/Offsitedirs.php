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
 * The Controller for restoring off-site directory contents.
 *
 * @since  10.0
 */
class Offsitedirs extends Controller
{
	/**
	 * Recursively move the off-site directory's content to their intended destination.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function move()
	{
		$lang = $this->getContainer()->get('language');

		// We have to use the raw filter, since the key could contain a forward slash e.g. virtual_folders/first_folder.
		$key = $this->getContainer()->get('input')->getRaw('key', null);

		if (empty($key))
		{
			$result = [
				'percent' => 0,
				'error'   => $lang->text('OFFSITEDIRS_ERR_INVALIDKEY'),
				'done'    => 1,
			];
			@ob_clean();
			echo json_encode($result);

			return;
		}

		try
		{
			$info = $this->getContainer()->get('input')->get('info', [], 'raw');

			if (!is_array($info))
			{
				$info = [];
			}

			/** @var \Akeeba\BRS\Model\Offsitedirs $model */
			$model = $this->getThisModel();
			$model->moveDir($key, $info['target'] ?? null);

			$result = [
				'percent' => 100,
				'error'   => '',
				'done'    => 1,
			];
		}
		catch (Throwable $exc)
		{
			$result = [
				'percent' => 0,
				'error'   => $exc->getMessage(),
				'done'    => 1,
			];
		}

		@ob_clean();
		echo json_encode($result);
	}

	/**
	 * Do I have any offsite dir that I have to restore?
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function hasoffsitedirs()
	{
		/** @var \Akeeba\BRS\Model\Offsitedirs $model */
		$model = $this->getThisModel();
		$dirs  = $model->getDirs();

		@ob_clean();
		echo json_encode((bool) count($dirs));
	}
}