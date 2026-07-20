<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Cli\Step;

use Akeeba\BRS\Framework\Cli\AbstractInstallationStep;
use Akeeba\BRS\Framework\Cli\Exception\ExecutionException;

defined('_AKEEBA') or die();

class Setup extends AbstractInstallationStep
{
	/**
	 * @inheritDoc
	 */
	public function isApplicable(): bool
	{
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): void
	{
		// TODO Refactor the Model so that validation is separate from execution.
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): void
	{
		$output = $this->getContainer()->get('output');

		$output->heading('Writing new configuration file');

		$model = $this->getContainer()->get('mvcFactory')->model('Setup');

		foreach ($this->configuration as $k => $v)
		{
			$model->setState($k, $v);
		}

		$writtenConfiguration = $model->applySettings();

		if (!$writtenConfiguration)
		{
			throw new ExecutionException('Could not write to the site configuration file.');
		}
	}

	/** @inheritDoc */
	protected function getDefaultConfiguration(): array
	{
		$configModel = $this->getContainer()->get('mvcFactory')->model('Configuration');
		$paths       = $this->getContainer()->get('paths');
		$jVersion    = $this->getContainer()->get('session')->get('jversion', '3.6.0');
		// Default tmp directory: tmp in the root of the site
		$defaultTmpPath = $paths->get('root') . '/tmp';
		// Default logs directory: logs in the administrator directory of the site
		$defaultLogPath = $paths->get('administrator') . '/logs';

		// If it's a Joomla! 1.x, 2.x or 3.0 to 3.5 site (inclusive) the default log dir is in the site's root
		if (!empty($jVersion) && version_compare($jVersion, '3.5.999', 'le'))
		{
			// I use log instead of logs because "logs" isn't writeable on many hosts.
			$defaultLogPath = $paths->get('root') . '/log';
		}

		$ret = [
			'superuserid'             => 0,
			'superuseremail'          => null,
			'superuserpassword'       => null,
			'superuserpasswordrepeat' => null,
			'removephpini'            => false,
			'replacewebconfig'        => false,
			'removehtpasswd'          => false,
			'htaccessHandling'        => 'none',
			'newsecret'               => true,
			'sitename'                => $configModel->get('sitename', 'Restored website'),
			'siteemail'               => $configModel->get('mailfrom', 'no-reply@example.com'),
			'emailsender'             => $configModel->get('fromname', 'Restored website'),
			'livesite'                => $configModel->get('live_site', ''),
			'cookiedomain'            => $configModel->get('cookie_domain', ''),
			'cookiepath'              => $configModel->get('cookie_path', ''),
			'tmppath'                 => $configModel->get('tmp_path', $defaultTmpPath),
			'cache_path'              => $configModel->get('cache_path', ''),
			'logspath'                => $configModel->get('log_path', $defaultLogPath),
			'force_ssl'               => $configModel->get('force_ssl', 2),
			'mailonline'              => $configModel->get('mailonline', 1),
			'default_tmp'             => $defaultTmpPath,
			'default_log'             => $defaultLogPath,
			'site_root_dir'           => $paths->get('root'),
			'resetsessionoptions'     => false,
			'resetcacheoptions'       => false,
		];

		if (version_compare($jVersion, '2.5.0', 'ge'))
		{
			$ret['robotHandling'] = 0;
		}

		if (version_compare($jVersion, '4.0', 'lt'))
		{
			$ret = array_merge(
				$ret,
				[
					'ftpenable' => $configModel->get('ftp_enable', false),
					'ftphost'   => $configModel->get('ftp_host', ''),
					'ftpport'   => $configModel->get('ftp_port', ''),
					'ftpuser'   => $configModel->get('ftp_user', ''),
					'ftppass'   => $configModel->get('ftp_pass', ''),
					'ftpdir'    => $configModel->get('ftp_root', ''),
				]
			);
		}

		return $ret;
	}


}