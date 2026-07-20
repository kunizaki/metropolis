<?php

/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Database\FixMySQLTrait;
use Akeeba\BRS\Framework\Ftp\Ftp;
use Akeeba\BRS\Framework\Helper\Setup as SetupHelper;
use Akeeba\BRS\Framework\Version\Version;
use Akeeba\BRS\Model\AbstractSetup;
use Exception;
use Joomla\CMS\Encrypt\Aes;
use Random\RandomException;

/**
 * Model for the Site Setup step for Joomla! sites.
 *
 * @since  10.0
 */
class Setup extends AbstractSetup
{
	use FixMySQLTrait;

	/**
	 * The AES-128 encryption engine using the backed up site's secret key.
	 *
	 * @var   Aes
	 * @since 10.0
	 */
	private $oldAes;

	/**
	 * The AES-128 encryption engine using the restored site's secret key.
	 *
	 * @var   Aes
	 * @since 10.0
	 */
	private $newAes;

	/**
	 * The backed up site's secret key.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $oldSecret;

	/**
	 * The restored site's secret key.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $newSecret;

	/**
	 * @inheritDoc
	 */
	public function applySettings(): bool
	{
		$session  = $this->getSession();
		$jVersion = $session->get('jversion', '2.5.0');

		// Apply the Super Administrator changes
		$this->applySuperAdminChanges();

		// Apply server config changes
		$this->applyServerconfigchanges();

		// Remove the autoload_psr4.php file
		$this->removeAutoloadPsr4();

		// Adjust Admin Tools for Joomla settings which store the site's domain name.
		$this->updateAdminToolsParams();

		// Get the state variables and update the global configuration
		$stateVars = $this->getStateVariables();

		// -- General settings
		$this->configModel->set('sitename', $stateVars->sitename);
		$this->configModel->set('mailfrom', $stateVars->siteemail);
		$this->configModel->set('fromname', $stateVars->emailsender);
		$this->configModel->set('live_site', $stateVars->livesite);
		$this->configModel->set('cookie_domain', $stateVars->cookiedomain);
		$this->configModel->set('cookie_path', $stateVars->cookiepath);
		$this->configModel->set('tmp_path', $stateVars->tmppath);
		$this->configModel->set('log_path', $stateVars->logspath);
		$this->configModel->set('force_ssl', $stateVars->force_ssl);

		$cachePath = $stateVars->cache_path ?? null;

		if (empty($cachePath))
		{
			$this->configModel->remove('cache_path');
		}
		else
		{
			$this->configModel->set('cache_path', $cachePath);
		}

		if (version_compare($session->get('jversion'), '3.2', 'ge'))
		{
			$this->configModel->set('mailonline', $stateVars->mailonline);
		}

		// -- FTP settings
		$this->configModel->set('ftp_enable', ($stateVars->ftpenable ? 1 : 0));
		$this->configModel->set('ftp_host', $stateVars->ftphost);
		$this->configModel->set('ftp_port', $stateVars->ftpport);
		$this->configModel->set('ftp_user', $stateVars->ftpuser);
		$this->configModel->set('ftp_pass', $stateVars->ftppass);
		$this->configModel->set('ftp_root', $stateVars->ftpdir);

		// -- Joomla 4 and later does not have FTP settings.
		if (version_compare($jVersion, '3.999.999', 'ge'))
		{
			$this->configModel->remove('ftp_enable');
			$this->configModel->remove('ftp_host');
			$this->configModel->remove('ftp_port');
			$this->configModel->remove('ftp_user');
			$this->configModel->remove('ftp_pass');
			$this->configModel->remove('ftp_root');
		}

		// -- Database settings
		$connectionVars = $this->getDbConnectionVars();

		$this->configModel->set('dbtype', $connectionVars->dbtype);
		$this->configModel->set(
			'host',
			$this->createMySQLHostname($connectionVars->dbhost, $connectionVars->dbport, $connectionVars->dbsocket)
		);
		$this->configModel->set('user', $connectionVars->dbuser);
		$this->configModel->set('password', $connectionVars->dbpass);
		$this->configModel->set('db', $connectionVars->dbname);
		$this->configModel->set('dbprefix', $connectionVars->prefix);

		// Joomla 4: Supports MySQL SSL/TLS connections
		if (version_compare($jVersion, '4.0.0', 'ge'))
		{
			$this->configModel->set('dbencryption', (bool) $connectionVars->dbencryption);
			$this->configModel->set('dbsslkey', $connectionVars->dbsslkey);
			$this->configModel->set('dbsslcert', $connectionVars->dbsslcert);
			$this->configModel->set('dbsslca', $connectionVars->dbsslca);
			$this->configModel->set('dbsslcipher', $connectionVars->dbsslcipher);
			$this->configModel->set('dbsslverifyservercert', (bool) $connectionVars->dbsslverifyservercert);
		}

		// Joomla 4: Reset state options
		if (version_compare($jVersion, '4.0.0', 'ge') && $stateVars->resetsessionoptions == 1)
		{
			$this->configModel->set('session_handler', 'database');
			$this->configModel->set('session_filesystem_path', '');
		}

		// Joomla 4: Reset caching options
		if (version_compare($jVersion, '4.0.0', 'ge') && $stateVars->resetcacheoptions == 1)
		{
			$this->configModel->set('caching', 0);
			$this->configModel->set('cache_handler', 'file');
		}

		// Joomla 5.1+: Reset cache path
		if (version_compare($jVersion, '5.1.0', 'ge') && $stateVars->resetcacheoptions == 1)
		{
			$this->configModel->remove('cache_path');
		}

		// Joomla 2.5 and later: Robots setting
		if (version_compare($jVersion, '2.5.0', 'ge') && $this->getState('robotHandling', 0) != 0)
		{
			switch ((int) $this->getState('robotHandling', 0))
			{
				// Index, Follow
				case 1:
					$this->configModel->set('robots', 'index, follow');
					break;

				// Index, No Follow
				case 2:
					$this->configModel->set('robots', 'index, nofollow');
					break;

				// No Index, No Follow
				case 3:
					$this->configModel->set('robots', 'noindex, nofollow');
					break;
			}
		}

		// Generate a new secret, if necessary
		$this->conditionallyGenerateNewSecret();

		// Commit the new options into the session
		$this->configModel->saveToSession();

		// Get the configuration.php file and try to save it
		$configurationPHP = $this->configModel->getFileContents();
		$paths            = $this->getContainer()->get('paths');
		$filepath         = $paths->get('site') . '/configuration.php';
		$oldConfiguration = @file_get_contents($filepath);
		$backupFilepath   = $paths->get('site') . '/configuration.bak.php';

		// Keep a backup copy of the existing configuration.
		if ($oldConfiguration)
		{
			$preamble = '<?' . 'php' . "\n";
			$preamble .= <<< TEXT
/**
 * ================================================================================
 * Backup copy of Joomla's configuration.php
 * ================================================================================
 *
 * Below you can find the contents of the configuration.php file which was read by
 * the Akeeba Backup Restoration Script when it initialised.
 *
 * This is NOT necessarily the contents of your backed up site's configuration.php
 * file. Every time you run the Restoration Script past the Site Setup page, the
 * configuration.php file gets modified. If you want to reset and start over after
 * going past the Site Setup page you'll need to extract your backup archive again. 
 */
TEXT;
			$preamble .= "?>";



			$this->writeToFile($backupFilepath, $preamble . ltrim($oldConfiguration));
		}

		// Write the new configuration to the configuration.php file.
		return $this->writeToFile($filepath, $configurationPHP);
	}

	/**
	 * Writes content to a file, overwriting or creating the file as needed.
	 *
	 * @param   string  $filepath  The absolute filesystem path of the file.
	 * @param   string  $content   The file content.
	 *
	 * @return  bool
	 * @since   10.0
	 * @deprecated 11.0 Use file_put_contents instead.
	 */
	private function writeToFile(string $filepath, string $content): bool
	{
		// Try direct file writes first.
		if (@file_put_contents($filepath, $content))
		{
			return true;
		}

		// If we are not allowed to use FTP there's nothing more to do here.
		if (!$this->configModel->get('ftp_enable', 0))
		{
			return false;
		}

		// Try with FTP
		$ftphost = $this->configModel->get('ftp_host', '');
		$ftpport = $this->configModel->get('ftp_port', '');
		$ftpuser = $this->configModel->get('ftp_user', '');
		$ftppass = $this->configModel->get('ftp_pass', '');
		$ftproot = $this->configModel->get('ftp_root', '');

		try
		{
			$ftp = Ftp::getInstance(
				$this->getContainer(), $ftphost, $ftpport, ['type' => FTP_AUTOASCII], $ftpuser, $ftppass
			);
			$ftp->chdir($ftproot);
			$ftp->write('configuration.php', $content);
			$ftp->chmod('configuration.php', 0644);
		}
		catch (Exception $exc)
		{
			return false;
		}

		return true;
	}

	/** @inheritDoc */
	public function getStateVariables(): object
	{
		// I have to extend the parent method to include FTP params, too
		$params               = (array) parent::getStateVariables();
		$params['superusers'] = $params['superusers'] ?? [];

		if (!empty($params['superusers']))
		{
			array_unshift(
				$params['superusers'],
				(object) [
					'id'       => 0,
					'username' => '&mdash;',
					'email'    => '',
				]
			);
		}

		$params = array_merge($params, $this->getFTPParamsVars());

		return (object) $params;
	}

	/**
	 * Checks if the current site has an .htaccess and an .htpasswd file in its administrator folder
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasHtpasswd(): bool
	{
		$files = [
			'administrator/.htaccess',
			'administrator/.htpasswd',
		];

		$publicFolder = $this->getPublicFolder();

		foreach ($files as $file)
		{
			if (file_exists($publicFolder . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the current site has user-defined configuration files (ie php.ini or .user.ini etc.)
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasPhpIni(): bool
	{
		$publicFolder = $this->getPublicFolder();

		$files = [
			'.user.ini',
			'.user.ini.bak',
			'php.ini',
			'php.ini.bak',
			'administrator/.user.ini',
			'administrator/.user.ini.bak',
			'administrator/php.ini',
			'administrator/php.ini.bak',
		];

		$paths = $this->getContainer()->get('paths');

		foreach ($files as $file)
		{
			if (file_exists($paths->get('site') . '/' . $file) || file_exists($publicFolder . '/' . $file))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the protocol we're restoring under is incompatible with the Force SSL setting.
	 *
	 * If we're using HTTP, and we are using any kind of forced HTTPS (admin or entire site) it will return false.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function protocolMismatch(): bool
	{
		$uri      = $this->getContainer()->get('uri')->instance();
		$protocol = $uri->toString(['scheme']);

		// Restoring under HTTPS, we're always good to go
		if ($protocol == 'https://')
		{
			return false;
		}

		$site_params = $this->getSiteParamsVars();

		// Force SSL not applied, we're good to go
		if ($site_params['force_ssl'] == 0)
		{
			return false;
		}

		// In any other cases, we have a protocol mismatch: we're restoring under HTTP,
		// but we set Force SSL to Entire site or Administrator only.
		return true;
	}

	/**
	 * Encrypt the data string using AES and the new site secret.
	 *
	 * @param   string  $data  The data to encrypt
	 *
	 * @return  string  Encrypted data
	 * @since   10.0
	 */
	public function encryptAes(string $data): string
	{
		if (!is_object($this->newAes))
		{
			return $data;
		}

		return '###AES128###' . $this->newAes->encryptString($data, true);
	}

	/** @inheritDoc */
	protected function getSiteParamsVars(): array
	{
		$paths    = $this->getContainer()->get('paths');
		$jVersion = $this->getSession()->get('jversion', '3.6.0');

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

		$defaultSSL = 2;

		if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')
		{
			$defaultSSL = 0;
		}

		$ret = [
			'sitename'            => $this->getState(
				'sitename', $this->configModel->get('sitename', 'Restored website')
			),
			'siteemail'           => $this->getState(
				'siteemail', $this->configModel->get('mailfrom', 'no-reply@example.com')
			),
			'emailsender'         => $this->getState(
				'emailsender', $this->configModel->get('fromname', 'Restored website')
			),
			'livesite'            => $this->getState('livesite', $this->configModel->get('live_site', '')),
			'cookiedomain'        => $this->getState('cookiedomain', $this->configModel->get('cookie_domain', '')),
			'cookiepath'          => $this->getState('cookiepath', $this->configModel->get('cookie_path', '')),
			'tmppath'             => $this->getState('tmppath', $this->configModel->get('tmp_path', $defaultTmpPath)),
			'cache_path'          => $this->getState('cache_path', $this->configModel->get('cache_path', '')),
			'logspath'            => $this->getState('logspath', $this->configModel->get('log_path', $defaultLogPath)),
			'force_ssl'           => $this->getState('force_ssl', $this->configModel->get('force_ssl', $defaultSSL)),
			// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
			'mailonline'          => $this->getState('mailonline', $this->configModel->get('mailonline', 0), 'bool'),
			'default_tmp'         => $defaultTmpPath,
			'default_log'         => $defaultLogPath,
			'site_root_dir'       => $paths->get('root'),
			// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
			'resetsessionoptions' => $this->getState('resetsessionoptions', 0, 'bool'),
			// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
			'resetcacheoptions'   => $this->getState('resetcacheoptions', 0, 'bool'),
		];

		// Lets cleanup the live site url
		$ret['livesite'] = SetupHelper::cleanLiveSite($ret['livesite']);

		// Deal with tmp, logs, and cache paths
		if (!@is_dir($ret['tmppath']) || !@is_writable($ret['tmppath']))
		{
			$ret['tmppath'] = $defaultTmpPath;
		}

		if (!@is_dir($ret['logspath']) || !@is_writable($ret['logspath']))
		{
			$ret['logspath'] = $defaultLogPath;
		}

		if (!empty($ret['cache_path']))
		{
			if (!@is_dir($ret['cache_path']) || !@is_writable($ret['cache_path']))
			{
				$ret['cache_path'] = '';
			}
		}

		return $ret;
	}

	/** @inheritDoc */
	protected function getSuperUsersVars(): array
	{
		$ret = [];

		// Connect to the database
		try
		{
			$db = $this->getDatabase();
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Find the Super User groups
		try
		{
			$query = $db->getQuery(true)
				->select($db->qn('rules'))
				->from($db->qn('#__assets'))
				->where($db->qn('parent_id') . ' = ' . $db->q(0));
			$db->setQuery($query, 0, 1);
			$rulesJSON = $db->loadResult();
			$rules     = json_decode($rulesJSON, true);

			$rawGroups = $rules['core.admin'];
			$groups    = [];

			if (empty($rawGroups))
			{
				return $ret;
			}

			foreach ($rawGroups as $g => $enabled)
			{
				if ($enabled)
				{
					$groups[] = $db->q($g);
				}
			}

			if (empty($groups))
			{
				return $ret;
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user IDs of users belonging to the SA groups
		try
		{
			$query = $db->getQuery(true)
				->select($db->qn('user_id'))
				->from($db->qn('#__user_usergroup_map'))
				->where($db->qn('group_id') . ' IN(' . implode(',', $groups) . ')');
			$db->setQuery($query);
			$rawUserIDs = $db->loadColumn(0);

			if (empty($rawUserIDs))
			{
				return $ret;
			}

			$userIDs = [];

			foreach ($rawUserIDs as $id)
			{
				$userIDs[] = $db->q($id);
			}
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		// Get the user information for the Super Administrator users
		try
		{
			$query = $db->getQuery(true)
				->select(
					[
						$db->qn('id'),
						$db->qn('username'),
						$db->qn('email'),
					]
				)->from($db->qn('#__users'))
				->where($db->qn('id') . ' IN(' . implode(',', $userIDs) . ')');
			$db->setQuery($query);
			$ret['superusers'] = $db->loadObjectList(0);
		}
		catch (Exception $exc)
		{
			return $ret;
		}

		return $ret;
	}

	/** @inheritDoc */
	protected function replaceHtaccess(): void
	{
		// If I don't have any .htaccess file there's no point in continuing.
		if (!$this->hasHtaccess())
		{
			return;
		}

		/**
		 * A note on what is happening here, because it's confusing.
		 *
		 * In previous versions of the restoration script, we were trying to download and use the default .htaccess file
		 * from the latest Joomla! development branch. This was possible because it had the predictable name `staging`.
		 * This had two problems. First, it would always download the latest version of the htaccess.txt which, while it
		 * was a sane thing to do when Joomla! 3 spanned 9 years, it's no longer a good idea when we have a new major
		 * version every 2 years. Second, the `staging` branch no longer exists.
		 *
		 * Ever since Joomla! 4.0 was released, Joomla uses versioned branches such as 4.0-dev, 4.1-dev, 5.0-dev etc. At
		 * any given time there are up to four active branches: previous major LTS (e.g. 4.4-dev), current minor STS
		 * (e.g. 5.2-dev), next minor STS (e.g. 5.3-dev), and next major STS (e.g. 6.0-dev). Moreover, Joomla keeps tags
		 * for each and every release you can find in its GitHub history, starting with 1.7.3.
		 *
		 * Based on this information, we formulate a different strategy to get the best htaccess.txt file possible:
		 * - Named tag for the exact Joomla version of the site being restored.
		 * - Named tag for the .0 patch Joomla version of the site being restored.
		 * - x.y-dev branch for the Joomla version of the site being restored.
		 * - Fallback to a minimal .htaccess which is updated infrequently, and is compatible with all Joomla! versions
		 *   supported by this restoration script.
		 */
		$paths          = $this->getContainer()->get('paths');
		$literalVersion = trim($this->getSession()->get('jversion', '2.5.0')) ?: '2.5.0';
		$jVersion       = Version::create($literalVersion);

		$htaccessTxtLocations = [
			sprintf('https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s/htaccess.txt', $literalVersion),
			sprintf(
				'https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s/htaccess.txt',
				$jVersion->fullVersion()
			),
			sprintf(
				'https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s.0/htaccess.txt',
				$jVersion->versionFamily()
			),
			$paths->get('platform.assets') . '/serverconfig/htaccess.txt',
		];

		$contents = array_reduce(
			$htaccessTxtLocations,
			function ($carry, $item) {
				return $carry ?: @file_get_contents($item);
			},
			false
		);

		$publicFolder = $this->getPublicFolder();

		// First of all let's remove any backup file. Then copy the current contents of the .htaccess file in a
		// backup file. Finally, delete the .htaccess file and write a new one with the default contents
		// If any of those steps fails we simply stop
		if (!@unlink($publicFolder . '/htaccess.bak'))
		{
			return;
		}

		$orig = @file_get_contents($publicFolder . '/.htaccess');

		if (!empty($orig) && !@file_put_contents($publicFolder . '/htaccess.bak', $orig))
		{
			return;
		}

		if (@file_exists($publicFolder . '/.htaccess') && !@unlink($publicFolder . '/.htaccess'))
		{
			return;
		}

		if (!file_put_contents($publicFolder . '/.htaccess', $contents))
		{
			return;
		}
	}

	/**
	 * Applies server configuration changes (removing/renaming server configuration files)
	 *
	 * @since 10.0
	 */
	private function applyServerconfigchanges(): void
	{
		$publicFolder = $this->getPublicFolder();

		// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
		if ($this->getState('removephpini', 0, 'bool'))
		{
			$this->removePhpini();
		}

		// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
		if ($this->getState('replacewebconfig',0, 'bool'))
		{
			$this->replaceWebconfig();
		}

		// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
		if ($this->getState('removehtpasswd', 0, 'bool'))
		{
			$this->removeHtpasswd($publicFolder . '/administrator');
		}

		$htaccessHandling = $this->getState('htaccessHandling', 'none', 'cmd');
		$this->applyHtaccessHandling($htaccessHandling);
	}

	private function applySuperAdminChanges(): void
	{
		// Get the Super User ID. If it's empty, skip.
		$id = $this->getState('superuserid', 0);

		if (!$id)
		{
			return;
		}

		// Get the Super User email and password
		$email     = $this->getState('superuseremail', '');
		$password1 = $this->getState('superuserpassword', '');
		$password2 = $this->getState('superuserpasswordrepeat', '');

		// If the passwords are empty, skip
		if (empty($password1) && empty($password2))
		{
			return;
		}

		$text  = $this->getContainer()->get('language');
		$paths = $this->getContainer()->get('paths');

		// Make sure the passwords match
		if ($password1 != $password2)
		{
			throw new Exception($text->text('SETUP_ERR_PASSWORDSDONTMATCH'));
		}

		// If the email is empty, but the passwords are not: fail.
		if (empty($email))
		{
			throw new Exception($text->text('SETUP_ERR_EMAILEMPTY'));
		}

		// Connect to the database
		$db = $this->getDatabase();

		// Create a new salt and encrypted password (legacy method for Joomla! 1.5.0 through 3.2.0)
		$salt      = $this->genRandomPassword(32);
		$crypt     = hash('md5', $password1 . $salt);
		$cryptpass = $crypt . ':' . $salt;

		// Get the Joomla! version. If none was detected we assume it's 1.5.0 (so we can use the legacy method)
		$jVersion = $this->getSession()->get('jversion', '1.5.0');

		// Joomla! 3.2.2 or later: use bCrypt
		if (version_compare($jVersion, '3.2.2', 'ge'))
		{
			$cryptpass = password_hash($password1, PASSWORD_BCRYPT, ['cost' => 10]);
		}

		/**
		 * Joomla! 4.0.0 or later on PHP 7.3 or later: use Argon2id
		 *
		 * Note: Joomla! 4.x and 5.x only ever uses bCrypt, even though passwords hashed by Argon2id (and Argon2i) are
		 * supported. While what we do here will work for logging into the restored site, if you change the password of
		 * the Super User the new password will be hashed with the slightly less secure bCrypt method again.
		 */
		if (
			version_compare($jVersion, '4.0.0', 'ge')
			&& version_compare(PHP_VERSION, '7.3.0', 'ge')
			&& \defined('PASSWORD_ARGON2ID'))
		{
			$cryptpass = password_hash($password1, \PASSWORD_ARGON2ID);
		}

		// Update the database record
		$query = $db->getQuery(true)
			->update($db->qn('#__users'))
			->set($db->qn('password') . ' = ' . $db->q($cryptpass))
			->set($db->qn('email') . ' = ' . $db->q($email))
			->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Generates a random password of the specified length.
	 *
	 * This uses a cryptographically secure method, with a fallback to a legacy pseudorandom method for environments
	 * lacking modern randomness API support.
	 *
	 * @param   int  $length  The desired length of the generated password. Defaults to 8 characters.
	 *
	 * @return  string  A randomly generated password string of the specified length.
	 * @throws  RandomException  If random_bytes fails to execute (PHP 8.2+).
	 * @since   10.0
	 */
	private function genRandomPassword($length = 8): string
	{
		$salt     = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$base     = strlen($salt);
		$makepass = '';

		// Prefer using random_bytes(), either native or the ParagonIE user-land implementation
		if (function_exists('random_bytes'))
		{
			/*
			 * Start with a cryptographic strength random string, then convert it to a string with the numeric base of
			 * the salt. Shift the base conversion on each character so the character distribution is even, and
			 * randomize the start shift, so it's not predictable.
			 */
			$random = random_bytes($length + 1);
			$shift  = \ord($random[0]);

			for ($i = 1; $i <= $length; ++$i)
			{
				$makepass .= $salt[($shift + \ord($random[$i])) % $base];
				$shift    += \ord($random[$i]);
			}

			return $makepass;
		}

		// This legacy code should no longer be called.
		$stat = @stat(__FILE__);

		if (empty($stat) || !is_array($stat))
		{
			$stat = [php_uname()];
		}

		mt_srand(crc32(microtime() . implode('|', $stat)));

		for ($i = 0; $i < $length; $i++)
		{
			$makepass .= $salt[mt_rand(0, $base - 1)];
		}

		return $makepass;
	}

	/**
	 * Gets the FTP connection parameters
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function getFTPParamsVars(): array
	{
		$ret = [
			'ftpenable' => $this->getState('enableftp', $this->configModel->get('ftp_enable', 0)),
			'ftphost'   => $this->getState('ftphost', $this->configModel->get('ftp_host', '')),
			'ftpport'   => $this->getState('ftpport', $this->configModel->get('ftp_port', 21)),
			'ftpuser'   => $this->getState('ftpuser', $this->configModel->get('ftp_user', '')),
			'ftppass'   => $this->getState('ftppass', $this->configModel->get('ftp_pass', '')),
			'ftpdir'    => $this->getState('ftpdir', $this->configModel->get('ftp_root', '')),
		];

		return $ret;
	}

	/**
	 * Load the Admin Tools configuration from the database
	 *
	 * @return  array
	 * @since   10.0
	 */
	private function loadAdminToolsConfig(): ?array
	{
		try
		{
			$db         = $this->getDatabase();
			$query      = $db->getQuery(true)
				->select($db->quoteName('value'))
				->from($db->quoteName('#__admintools_storage'))
				->where($db->quoteName('key') . ' = ' . $db->quote('cparams'));
			$jsonConfig = $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			return null;
		}

		if (empty($jsonConfig))
		{
			return null;
		}

		try
		{
			$config = @json_decode($jsonConfig, true);
		}
		catch (Exception $e)
		{
			$config = null;
		}

		return $config;
	}

	/**
	 * Remove Joomla 4's PSR-4 extension autoloader cache.
	 *
	 * If the contents of this file are out of sync with the filesystem — as it may happen if you restore a backup on
	 * top of an existing site — you might get fatal PHP errors.
	 *
	 * Regenerating this file solves this kind of PHP errors. Luckily, this file is regenerated automatically if it's
	 * missing when you visit any page of your site.
	 *
	 * Therefore, removing this file during the site's restoration addresses the broken site after restoration issue.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function removeAutoloadPsr4(): void
	{
		$paths    = $this->getContainer()->get('paths');
		$filePath = $paths->get('root') . '/administrator/cache/autoload_psr4.php';

		if (!@file_exists($filePath))
		{
			return;
		}

		if (@unlink($filePath))
		{
			return;
		}

		if (!$this->configModel->get('ftp_enable', 0))
		{
			return;
		}

		// Try with FTP
		$ftphost = $this->configModel->get('ftp_host', '');
		$ftpport = $this->configModel->get('ftp_port', '');
		$ftpuser = $this->configModel->get('ftp_user', '');
		$ftppass = $this->configModel->get('ftp_pass', '');
		$ftproot = $this->configModel->get('ftp_root', '');

		try
		{
			$ftp = Ftp::getInstance(
				$this->getContainer(), $ftphost, $ftpport, ['type' => FTP_AUTOASCII], $ftpuser, $ftppass
			);
			$ftp->chdir($ftproot);
			$ftp->delete('administrator/cache/autoload_psr4.php');
		}
		catch (Exception $exc)
		{
			return;
		}
	}

	/**
	 * Removes any user-defined PHP configuration files (.user.ini or php.ini)
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function removePhpini(): bool
	{
		if (!$this->hasPhpIni())
		{
			return true;
		}

		$paths        = $this->getContainer()->get('paths');
		$publicFolder = $this->getPublicFolder();

		// First of all let's remove any .bak file
		$files = [
			'.user.ini.bak',
			'php.ini.bak',
			'administrator/.user.ini.bak',
			'administrator/php.ini.bak',
		];

		foreach ($files as $file)
		{
			if (file_exists($paths->get('root') . '/' . $file))
			{
				// If I get any error during the deletion, let's stop here
				if (!@unlink($paths->get('root') . '/' . $file))
				{
					return false;
				}
			}

			if (file_exists($publicFolder . '/' . $file))
			{
				// If I get any error during the deletion, let's stop here
				if (!@unlink($publicFolder . '/' . $file))
				{
					return false;
				}
			}
		}

		$renameFiles = [
			$paths->get('root') . '/.user.ini',
			$paths->get('root') . '/php.ini',
			$paths->get('administrator') . '/.user.ini',
			$paths->get('administrator') . '/php.ini',
			$publicFolder . '/.user.ini',
			$publicFolder . '/php.ini',
			$publicFolder . '/administrator/.user.ini',
			$publicFolder . '/administrator/php.ini',
		];

		// Let's use the copy-on-write approach to rename those files.
		// Read the contents, create a new file, delete the old one
		foreach ($renameFiles as $origPath)
		{
			if (!file_exists($origPath))
			{
				continue;
			}

			$contents = file_get_contents($origPath);

			// If I can't create the file let's continue with the next one
			if (!file_put_contents($origPath . '.bak', $contents))
			{
				if (!empty($contents))
				{
					continue;
				}
			}

			unlink($origPath);
		}

		return true;
	}

	/**
	 * Replaces the current version of the web.config file with the default one provided by Joomla.
	 *
	 * The original contents are saved in a backup file named web.config.bak
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function replaceWebconfig(): bool
	{
		// If I don't have any web.config file there's no point on continuing
		if (!$this->hasWebconfig())
		{
			return true;
		}

		/**
		 * @see self::replaceHtaccess() for an explanation of what is going on here.
		 */
		$paths          = $this->getContainer()->get('paths');
		$literalVersion = trim($this->getSession()->get('jversion', '2.5.0')) ?: '2.5.0';
		$jVersion       = Version::create($literalVersion);

		$webConfigTxtLocations = [
			sprintf('https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s/web.config.txt', $literalVersion),
			sprintf(
				'https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s/web.config.txt',
				$jVersion->fullVersion()
			),
			sprintf(
				'https://raw.githubusercontent.com/joomla/joomla-cms/refs/tags/%s.0/web.config.txt',
				$jVersion->versionFamily()
			),
			$paths->get('platform.assets') . '/serverconfig/web.config.txt',
		];

		$contents = array_reduce(
			$webConfigTxtLocations,
			function ($carry, $item) {
				return $carry ?: @file_get_contents($item);
			},
			false
		);

		$publicFolder = $this->getPublicFolder();

		/**
		 * First of all let's remove any backup file. Then copy the current contents of the web.config file in a
		 * backup file. Finally, delete the web.config file and write a new one with the default contents
		 * If any of those steps fails we simply stop.
		 */
		if (!@unlink($publicFolder . '/web.config.bak'))
		{
			return false;
		}

		$orig = file_get_contents($publicFolder . '/web.config');

		if (!file_put_contents($publicFolder . '/web.config.bak', $orig))
		{
			return false;
		}

		if (!@unlink($publicFolder . '/web.config'))
		{
			return false;
		}

		if (!file_put_contents($publicFolder . '/web.config', $contents))
		{
			return false;
		}

		return true;
	}

	/**
	 * Save the Admin Tools configuration back to the database.
	 *
	 * @param   array  $config  The configuration to encode and save
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function saveAdminToolsConfig(array $config)
	{
		$jsonConfig = json_encode($config);

		try
		{
			$db    = $this->getDatabase();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__admintools_storage'))
				->set($db->quoteName('value') . ' = ' . $db->quote($jsonConfig))
				->where($db->quoteName('key') . ' = ' . $db->quote('cparams'));
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			return;
		}
	}

	/**
	 * Add the current domain name to the Allowed Domains feature, if necessary.
	 *
	 * This only takes place if all the following conditions are met:
	 * * The Allowed Domains feature was enabled (non-empty list of allowed domains) at backup time.
	 * * The server lets us know what is the current hostname.
	 * * The current hostname is not already explicitly allowed.
	 * * The current hostname does not resolve to 127.0.0.1 or ::1.
	 *
	 * If live_site is set then it is used INSTEAD OF the automatically detected hostname.
	 *
	 * @param   array  $adminToolsConfig  The original Admin Tools configuration
	 *
	 * @return  array  The modified Admin Tools configuration.
	 * @since   10.0
	 */
	private function updateAdminToolsAllowedDomains(array $adminToolsConfig): array
	{
		$allowedDomains = isset($adminToolsConfig['allowed_domains']) ? $adminToolsConfig['allowed_domains'] : null;

		// No explicitly set allowed domains? The feature is disabled, leave it like that!
		if (empty($allowedDomains))
		{
			return $adminToolsConfig;
		}

		// Try to decode the allowed domains.
		$isNewAdminTools = is_array($allowedDomains);

		if (!is_array($allowedDomains) && !is_string($allowedDomains))
		{
			// What the heck is this?! I have no idea what to do with this, mate.
			return $adminToolsConfig;
		}

		if (!$isNewAdminTools)
		{
			$allowedDomains = explode(',', $allowedDomains);
			$allowedDomains = array_map(
				function ($x) {
					return trim($x);
				}, $allowedDomains
			);
			$allowedDomains = array_filter(
				$allowedDomains, function ($x) {
				return !empty($x);
			}
			);

			// Yes, we check again if it's disabled
			if (empty($allowedDomains))
			{
				return $adminToolsConfig;
			}
		}

		// Work around newer Admin Tools versions allowing for descriptions.
		$allowedDomains = array_map(
			function ($x) {
				return is_array($x) ? array_shift($x) : $x;
			}, $allowedDomains
		);

		// Get the full list of explicitly allowed domain names
		$extraDomains = array_map(
			function ($x) {
				if (!is_string($x))
				{
					return '';
				}

				if (($x == 'localhost') || (substr($x, -6) === '.local') || (substr($x, -12) === '.localdomain'))
				{
					return '';
				}

				if (substr($x, 0, 4) === 'www.')
				{
					return substr($x, 4);
				}

				return 'www.' . $x;
			}, $allowedDomains
		);

		$extraDomains             = array_filter($extraDomains);
		$explicitlyAllowedDomains = array_merge($allowedDomains, $extraDomains);
		$explicitlyAllowedDomains = array_filter(
			$explicitlyAllowedDomains, function ($x) {
			return !empty($x);
		}
		);

		// Get the current hostname (or the one in $live_site, if a non-blank one was provided)
		$stateVars = $this->getStateVariables();
		$liveSite  = $stateVars->livesite;

		$uri  = empty($liveSite)
			? $this->getContainer()->get('uri')->instance()
			: $this->getContainer()
				->get('uri')
				->instance($liveSite);
		$host = $uri->getHost();

		// No host information passed from the server? Can't do anything.
		if (empty($host))
		{
			return $adminToolsConfig;
		}

		// Allowed Domains always stores host names in canonical, lowercase format.
		$host = strtolower($host);

		// Is the host already explicitly allowed?
		if (in_array($host, $explicitlyAllowedDomains))
		{
			return $adminToolsConfig;
		}

		// If the current hostname resolves to localhost it's always allowed, therefore we do not need to add it.
		$ip = gethostbyname($host);

		if (($ip === '127.0.0.1') || ($ip === '::1'))
		{
			return $adminToolsConfig;
		}

		// Remove any www. in front of the current hostname
		if (stripos($host, 'www.') === 0)
		{
			$host = substr($host, 4);
		}

		$allowedDomains[] = $host;

		if (!$isNewAdminTools)
		{
			$allowedDomains = implode(',', $allowedDomains);
		}

		$adminToolsConfig['allowed_domains'] = $allowedDomains;

		return $adminToolsConfig;
	}

	/**
	 * Update the Admin Tools server maker features with the new domain name.
	 *
	 * This updates the HTTP and HTTPS Host Name options, as well as the Base directory option for each of the .htaccess
	 * Maker, NginX Conf Maker and Web.Config Maker features in Admin Tools.
	 *
	 * @param   array  $adminToolsConfig  The original Admin Tools configuration
	 *
	 * @return  array  The modified Admin Tools configuration
	 * @since   10.0
	 */
	private function updateAdminToolsConfigMakerDomains(array $adminToolsConfig): array
	{
		// Get the current hostname (or the one in $live_site, if a non-blank one was provided)
		$stateVars = $this->getStateVariables();
		$liveSite  = $stateVars->livesite;
		$uri       = empty($liveSite)
			? $this->getContainer()->get('uri')->instance()
			: $this->getContainer()->get(
				'uri'
			)->instance($liveSite);
		$host      = strtolower($uri->getHost() ?: '');
		$sitePath  = $uri->getPath() ?: '/';
		$sitePath  = substr($sitePath, -9) === 'index.php' ? substr($sitePath, 0, -9) : $sitePath;
		$sitePath  = '' . trim($sitePath, '/');
		$sitePath  = substr($sitePath, 0, 12) === 'installation' ? substr($sitePath, 12) : $sitePath;
		$sitePath  = '' . trim($sitePath, '/');
		$sitePath  = $sitePath ?: '/';

		// No host information passed from the server? Can't do anything.
		if (empty($host))
		{
			return $adminToolsConfig;
		}

		// Loop through all three server configuration makers
		foreach (['htconfig', 'nginxconfig', 'wcconfig'] as $serverConfigKey)
		{
			// If the key is not set this feature is not being used; skip over it.
			if (!isset($adminToolsConfig[$serverConfigKey]))
			{
				continue;
			}

			// Try to decode the configuration. Skip over if this fails.
			$encodedConfig = $adminToolsConfig[$serverConfigKey];
			$jsonConfig    = function_exists('base64_encode') ? base64_decode($encodedConfig) : $encodedConfig;

			if ($jsonConfig === false)
			{
				continue;
			}

			try
			{
				$serverMakerConfig = @json_decode($jsonConfig, true);
			}
			catch (Exception $e)
			{
				$serverMakerConfig = [];
			}

			// If the decoded config is empty this feature is not being used. Skip over.
			if (empty($serverMakerConfig))
			{
				continue;
			}

			// Update the values.
			$serverMakerConfig['rewritebase'] = $sitePath;
			$serverMakerConfig['httphost']    = $host;
			$serverMakerConfig['httpshost']   = $host;

			// Re-encode and update the Admin Tools configuration.
			$jsonConfig    = json_encode($serverMakerConfig);
			$encodedConfig = function_exists('base64_encode') ? base64_encode($jsonConfig) : $jsonConfig;

			$adminToolsConfig[$serverConfigKey] = $encodedConfig;
		}

		return $adminToolsConfig;
	}

	/**
	 * Update Admin Tools for Joomla configuration parameters.
	 *
	 * This method does the following:
	 * * Updates the Allowed Domains if necessary
	 * * Updates the site's domain and path in the .htaccess / web.config / Nginx Conf Maker features
	 *
	 * This method supports all version of Admin Tools for Joomla from 3.0 onwards.
	 *
	 * For unsupported versions nothing bad will happen, it will just not do anything at all!
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function updateAdminToolsParams(): void
	{
		$config = $this->loadAdminToolsConfig();

		// If there's no config Admin Tools is not installed.
		if (empty($config))
		{
			return;
		}

		$originalHash = hash('md5', serialize($config));

		$config = $this->updateAdminToolsAllowedDomains($config);
		$config = $this->updateAdminToolsConfigMakerDomains($config);

		$newHash = hash('md5', serialize($config));

		if ($newHash === $originalHash)
		{
			return;
		}

		$this->saveAdminToolsConfig($config);
	}

	/**
	 * Update the encryption of the Joomla 4.2+ MFA settings with the new secret.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function updateJoomlaMFA()
	{
		$paths       = $this->getContainer()->get('paths');
		$serviceFile = $paths->get('site') . '/administrator/components/com_users/src/Service/Encrypt.php';

		// Only proceed when MFA actually exists.
		if (!@file_exists($serviceFile))
		{
			return;
		}

		// Make sure we can autoload Joomla's encryption service.
		global $autoloader;

		$autoloader->addPsr4('Joomla\\CMS\\Encrypt\\', $paths->get('site') . '/libraries/src/Encrypt');

		if (!defined('JPATH_PLATFORM'))
		{
			define('JPATH_PLATFORM', $paths->get('site') . '/libraries');
		}

		/**
		 * Check if the class exists.
		 *
		 * Some people mix up Joomla 3 and 4 files, leading to a broken restoration.
		 */
		if (!class_exists('\\Joomla\\CMS\\Encrypt\\Aes'))
		{
			return;
		}

		// Set up the AES objects
		$this->oldAes = new Aes('cbc');
		$this->newAes = new Aes('cbc');

		$this->oldAes->setPassword($this->oldSecret);
		$this->newAes->setPassword($this->newSecret);

		// Get the database connector
		try
		{
			$db = $this->getDatabase();
		}
		catch (Exception $exc)
		{
			return;
		}

		/**
		 * Make sure I have the #__user_mfa table.
		 *
		 * Some people restoring a Joomla 3 backup may have left Joomla 4 files behind, but wipe the tables clean.
		 *
		 * Moreover, it doesn't make sense wasting my time here if there are no MFA records.
		 */
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__user_mfa'));

		try
		{
			$totalCount = $db->setQuery($query)->loadResult() ?: 0;

			if ($totalCount <= 0)
			{
				return;
			}
		}
		catch (Exception $e)
		{
			return;
		}

		// Re-encrypt MFA records in batches of 500 records.
		$from  = 0;
		$limit = 500;

		while (true)
		{
			$query = $db->getQuery(true)
				->select(
					[
						$db->quoteName('id'),
						$db->quoteName('options'),
					]
				)
				->from($db->quoteName('#__user_mfa'))
				->order($db->quoteName('id') . ' ASC');

			try
			{
				$mfaRecords = $db->setQuery($query, $from, $limit)->loadObjectList();
			}
			catch (Exception $e)
			{
				break;
			}

			if (empty($mfaRecords))
			{
				break;
			}

			$db->transactionStart();

			foreach ($mfaRecords as $record)
			{
				$this->reencryptMFAOptions($record);

				$updateQuery = $db->getQuery(true)
					->update($db->quoteName('#__user_mfa'))
					->set($db->quoteName('options') . ' = ' . $db->quote($record->options))
					->where($db->quoteName('id') . ' = ' . $record->id);
				try
				{
					$db->setQuery($updateQuery)->execute();
				}
				catch (Exception $e)
				{
				}
			}

			$db->transactionCommit();

			$from += $limit;
		}
	}

	/**
	 * Re-encrypt the Joomla 4.2+ MFA options using the new site secret
	 *
	 * @param   object  $record  The record read from the database
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function reencryptMFAOptions(&$record)
	{
		if (!is_object($record) || !isset($record->options) || empty($record->options))
		{
			return;
		}

		$decrypted = @json_decode($this->decryptAes($record->options), true);

		if (is_string($decrypted))
		{
			$decrypted = @json_decode($decrypted, true);
		}

		// Fall back to legacy decryption
		if (!is_array($decrypted))
		{
			$decrypted = @json_decode($this->decryptAes($this->options, true), true);

			if (is_string($decrypted))
			{
				$decrypted = @json_decode($decrypted, true);
			}
		}

		if (empty($decrypted))
		{
			return;
		}

		$record->options = $this->encryptAes(json_encode($decrypted ?: []));
	}

	/**
	 * Decrypt an AES-encrypted string using the old site secret
	 *
	 * @param   string  $data    The data to decrypt
	 * @param   bool    $legacy  Should I use the legacy encryption scheme? Default: false
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function decryptAes($data, $legacy = false)
	{
		if (substr($data, 0, 12) != '###AES128###')
		{
			return $data;
		}

		$data = substr($data, 12);

		if (!is_object($this->oldAes))
		{
			return $data;
		}

		$this->oldAes->setPassword($this->oldSecret, $legacy);
		$decrypted = $this->oldAes->decryptString($data, true);

		// Decrypted data is null byte padded. We have to remove the padding before proceeding.
		return rtrim($decrypted, "\0") ?: '';
	}

	/**
	 * Generate a new secret, if necessary, and one is not already saved in the session.
	 *
	 * The provision to not regenerate a secret if one is already generated allows us to go back and reload the Site
	 * Setup page without messing up the re-encrypted data, e.g. the re-encrypted MFA options.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function conditionallyGenerateNewSecret()
	{
		$session = $this->getSession();

		// Get the old secret from the session, or the existing configuration.
		$oldSecret = $session->get('configuration.old_secret', $this->configModel->get('secret', ''));

		// ⚠️ DO NOT CHANGE THE DEFAULT VALUE. Getting the state of a CB *MUST* use the default value 0.
		$mustCreateNewSecret = $this->getState('newsecret', false, 'bool');

		// If I am told not to create a new secret, just use the old secret and return immediately.
		if (!$mustCreateNewSecret)
		{
			$this->configModel->set('secret', $oldSecret);

			return;
		}

		// Get the new secret from the session.
		$newSecret = $session->get('configuration.new_secret', null);

		// I had already generated a new secret. Use it, and return immediately.
		if (!is_null($newSecret))
		{
			$this->configModel->set('secret', $newSecret);

			return;
		}

		// Generate a new secret
		$this->oldSecret = $oldSecret;
		$this->newSecret = $this->genRandomPassword(32);

		$session->set('configuration.old_secret', $this->oldSecret);
		$session->set('configuration.new_secret', $this->newSecret);

		// Re-encrypt everything encrypted with the old secret.
		$this->updateJoomlaMFA();

		// Apply the new secret.
		$this->configModel->set('secret', $this->newSecret);
	}
}