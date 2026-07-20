<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Controller;

use Akeeba\BRS\Framework\Database\Restore\Exception\Dbname;
use Akeeba\BRS\Framework\Database\Restore\Exception\Dbuser;
use Akeeba\BRS\Framework\Mvc\Controller;
use Throwable;

defined('_AKEEBA') or die();

/**
 * Controller for the AJAX database restoration.
 *
 * @since  10.0
 */
class Dbrestore extends Controller
{
	/**
	 * Start the database restoration.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function start(): void
	{
		$input = $this->getContainer()->get('input');
		$text  = $this->getContainer()->get('language');

		$key             = $input->get('key', null);
		$data            = $input->getRaw('dbinfo', null);
		$specific_tables = $input->getRaw('specific_tables', []);

		if (empty($key) || empty($data['dbtype']))
		{
			$result = [
				'percent'  => 0,
				'restored' => 0,
				'total'    => 0,
				'eta'      => 0,
				'error'    => $text->text('DATABASE_ERR_INVALIDKEY'),
				'done'     => 1,
			];

			@ob_clean();
			echo json_encode($result);

			return;
		}

		/** @var \Akeeba\BRS\Model\Database $model */
		$model     = $this->getModel('Database');
		$savedData = $model->getDatabaseInfo($key);
		$data      = array_merge($savedData->toArray(), $data);

		$model->setDatabaseInfo($key, $data);

		try
		{
			$restoreEngine = $this->getContainer()->get('db')->restore($key, $data);

			// First of all let's prime the restoration engine
			$restoreEngine->removeInformationFromStorage();
			$restoreEngine->removeLog();

			// Then set the list of tables we want to restore
			$restoreEngine->setSpecificEntities($specific_tables);

			$result = [
				'percent'  => 0,
				'restored' => 0,
				'total'    => $restoreEngine->getTotalSize(true),
				'eta'      => '–––',
				'error'    => '',
				'done'     => 0,
			];
		}
		catch (Throwable $exc)
		{
			$result = $this->exceptionToResultArray($exc);
		}

		@ob_clean();
		echo json_encode($result);
	}

	/**
	 * Step through the database restoration.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function step(): void
	{
		$input = $this->getContainer()->get('input');

		$key = $input->get('key', null);

		/** @var \Akeeba\BRS\Model\Database $model */
		$model = $this->getModel('Database');
		$data  = $model->getDatabaseInfo($key);

		try
		{
			$restoreEngine = $this->getContainer()->get('db')->restore($key, $data->toArray());
			$restoreEngine->getTimer()->resetTime();

			$result = $restoreEngine->stepRestoration();
		}
		catch (Throwable $exc)
		{
			$result = $this->exceptionToResultArray($exc);
		}

		@ob_clean();
		echo json_encode($result);
	}

	/**
	 * Create a result array from a Throwable.
	 *
	 * There are two special cases. If we get an `Akeeba\BRS\Framework\Database\Restore\Exception\Dbuser`, or an
	 * `Akeeba\BRS\Framework\Database\Restore\Exception\Dbname` exception we render the error with a special view
	 * template.
	 *
	 * @param   Throwable  $throwable
	 *
	 * @return  array{percent: float, restored: int, total: int, eta: string, error: string, stopError: int, done: int}
	 * @since   10.0
	 */
	private function exceptionToResultArray(Throwable $throwable): array
	{
		$result = [
			'percent'   => 0,
			'restored'  => 0,
			'total'     => 0,
			'eta'       => '',
			'error'     => '',
			'stopError' => 0,
			'done'      => 1,
		];

		try
		{
			@ob_start();

			$this->layout = 'default';

			if ($throwable instanceof Dbuser)
			{
				$this->layout        = 'dbuser';
				$result['stopError'] = 1;
			}
			elseif ($throwable instanceof Dbname)
			{
				$this->layout        = 'dbname';
				$result['stopError'] = 1;
			}

			$view         = $this->getContainer()->get('mvcFactory')->view(ucfirst($this->viewName ?: $this->view), 'html');
			$view->task   = $this->task;
			$view->doTask = $this->doTask;
			$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);
			$view->exception = $throwable;
			$view->display();

			$errorMessage = @ob_get_clean();
		}
		catch (Throwable $e)
		{
			$errorMessage = '';
		}

		$result['error'] = $errorMessage ?: $throwable->getMessage();

		return $result;
	}
}