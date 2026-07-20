<?php
/**
 * @package   akeebabackup
 * @copyright Copyright 2006-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Site\Model\Json\Task;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Component\AkeebaBackup\Administrator\Model\BackupModel;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\CMS\Filter\InputFilter;
use RuntimeException;

/**
 * Start a backup job
 */
class StartBackup extends AbstractTask
{
	/**
	 * Execute the JSON API task
	 *
	 * @param   array  $parameters  The parameters to this task
	 *
	 * @return  mixed
	 *
	 * @throws  RuntimeException  In case of an error
	 */
	public function execute(array $parameters = [])
	{
		$filter = InputFilter::getInstance();

		// Get the passed configuration values
		$defConfig = [
			'profile'     => 1,
			'description' => '',
			'comment'     => '',
			'backupid'    => null,
			'overrides'   => [],
		];

		$defConfig = array_merge($defConfig, $parameters);

		$profile     = (int) $defConfig['profile'];
		$profile     = max(1, $profile); // Make sure $profile is a positive integer >= 1
		$description = $filter->clean($defConfig['description'], 'string');
		$comment     = $filter->clean($defConfig['comment'], 'string');
		$overrides   = $filter->clean($defConfig['overrides'], 'array');

		if (empty($description))
		{
			/** @var BackupModel $backupModel */
			$backupModel = $this->factory->createModel('Backup', 'Administrator', ['ignore_request' => true]);
			$description = $backupModel->getDefaultDescription() . ' (JSON API)';
		}

		JoomlaFactory::getApplication()->getSession()->set('akeebabackup.profile', $profile);
		define('AKEEBA_PROFILE', $profile);

		/**
		 * DO NOT REMOVE!
		 *
		 * The Model will only try to load the configuration after nuking the factory. This causes Profile 1 to be
		 * loaded first. Then it figures out it needs to load a different profile and it does – but the protected keys
		 * are NOT replaced, meaning that certain configuration parameters are not replaced. Most notably, the chain.
		 * This causes backups to behave weirdly. So, DON'T REMOVE THIS UNLESS WE REFACTOR THE MODEL.
		 */
		Platform::getInstance()->load_configuration($profile);

		/** @var BackupModel $model */
		$model = $this->factory->createModel('Backup', 'Administrator', ['ignore_request' => true]);
		$model->setState('tag', 'json');
		$model->setState('description', $description);
		$model->setState('comment', $comment);
		$model->setState('profile', $profile);

		$array = $model->startBackup($overrides);

		if ($array['Error'] != '')
		{
			throw new RuntimeException('A backup error has occurred: ' . $array['Error'], 500);
		}

		// BackupID contains the numeric backup record ID. backupid contains the backup id (usually in the form id123)
		$statistics        = Factory::getStatistics();
		$array['BackupID'] = $statistics->getId();

		// Remote clients expect a boolean, not an integer.
		$array['HasRun'] = ($array['HasRun'] === 0);
		$array['Profile'] = Platform::getInstance()->get_active_profile();

		return $array;
	}
}
