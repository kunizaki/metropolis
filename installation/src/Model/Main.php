<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\DataShape\SiteInfoInterface;
use Akeeba\BRS\Framework\CMSVersion\DetectionInterface;
use Akeeba\BRS\Framework\Configuration\Database;
use Akeeba\BRS\Framework\Mvc\Model;
use Akeeba\BRS\Framework\RestorationCheck;
use Akeeba\BRS\DataShape\SiteInfo;

class Main extends Model
{
	/**
	 * Pre-restoration checks to check.
	 *
	 * @var   RestorationCheck\RestorationCheckInterface[]
	 * @since 10.0
	 */
	private $checks = [];

	/**
	 * The site settings to display to the user.
	 *
	 * @var   array<SiteInfoInterface>
	 * @since 10.0
	 */
	private $siteInfo = [];

	/**
	 * Are all required settings met?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isRequiredMet(): bool
	{
		return array_reduce(
			$this->getRequired(),
			function (bool $carry, RestorationCheck\RestorationCheckInterface $check) {
				return $carry && $check->isValid();
			},
			true
		);
	}

	/**
	 * Are all recommended settings met?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isRecommendedMet(): bool
	{
		return array_reduce(
			$this->getRecommended(),
			function (bool $carry, RestorationCheck\RestorationCheckInterface $check) {
				return $carry && $check->isValid();
			},
			true
		);
	}

	/**
	 * Retrieve the list of checks.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getChecks(): array
	{
		$this->populateChecks();

		return $this->checks;
	}

	/**
	 * Get the required checks.
	 *
	 * @return  RestorationCheck\RestorationCheckInterface[]
	 * @since   10.0
	 */
	public function getRequired(): array
	{
		$this->populateChecks();

		return array_filter(
			$this->checks,
			function (RestorationCheck\RestorationCheckInterface $check) {
				return $check->isRequired();
			}
		);
	}

	/**
	 * Get the recommended (optional) checks.
	 *
	 * @return  RestorationCheck\RestorationCheckInterface[]
	 * @since   10.0
	 */
	public function getRecommended(): array
	{
		$this->populateChecks();

		return array_filter(
			$this->checks,
			function (RestorationCheck\RestorationCheckInterface $check) {
				return !$check->isRequired();
			}
		);
	}

	/**
	 * Detects the CMS version in use and stores it into the session.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function detectVersion(): void
	{
		$installerType = $this->getContainer()->get('configuration')->type;
		$className     = '\\Akeeba\\BRS\\Framework\\CMSVersion\\' . ucfirst($installerType) . 'Detection';

		if (!class_exists($className) || !in_array(DetectionInterface::class, class_implements($className)))
		{
			return;
		}

		/** @var DetectionInterface $o */
		$o = new $className($this->container);

		$o->detectVersion();
	}

	/**
	 * Returns the Site settings to report.
	 *
	 * @return  array<SiteInfoInterface>
	 * @since   10.0
	 */
	public function getSiteInfo(): array
	{
		return $this->siteInfo = $this->siteInfo ?: [
			SiteInfo\HostName::make($this->getContainer()),
			SiteInfo\DateCheck::make($this->getContainer()),
			SiteInfo\AkeebaBackupVersion::make($this->getContainer()),
			SiteInfo\PHPVersion::make($this->getContainer()),
			SiteInfo\RootDirectory::make($this->getContainer()),
		];
	}

	/**
	 * Has any site information setting changed from backup to restoration time ?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isSiteInfoChanged(): bool
	{
		return array_reduce($this->getSiteInfo(), function (bool $carry, SiteInfoInterface $info) {
			return $carry || $info->isChanged();
		}, false);
	}

	/**
	 * Parses extra info stored while taking the backup.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getExtraInfo()
	{
		$lang = $this->getContainer()->get('language');
		$info = $this->getContainer()->get('configuration')->extraInfo;
		$ret  = [];

		if ($info->host ?? null)
		{
			$ret['host'] = [
				'label'   => $lang->text('MAIN_LBL_EXTRAINFO_HOST'),
				'current' => $info->host,
			];
		}

		if ($info->backup_date ?? null)
		{
			$ret['backup_date'] = [
				'label'   => $lang->text('MAIN_LBL_EXTRAINFO_BACKUPDATE'),
				'current' => $info->backup_date . ' UTC',
			];
		}

		if ($info->akeeba_version ?? null)
		{
			$ret['akeeba_version'] = [
				'label'   => $lang->text('MAIN_LBL_EXTRAINFO_AKEEBAVERSION'),
				'current' => $info->akeeba_version,
			];
		}

		if ($info->php_version ?? null)
		{
			$ret['php_version'] = [
				'label'   => $lang->text('MAIN_LBL_EXTRAINFO_PHPVERSION'),
				'current' => $info->php_version,
			];
		}

		if ($info->root ?? null)
		{
			$ret['root'] = [
				'label'   => $lang->text('MAIN_LBL_EXTRAINFO_ROOT'),
				'current' => $info->root,
			];
		}

		return $ret;
	}

	/**
	 * Am I restoring to a different host?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isRestoringToDifferentHost(): bool
	{
		$extraInfo = $this->getContainer()->get('configuration')->extraInfo;
		$oldHost   = $extraInfo->host;
		$newHost   = $this->getContainer()->get('uri')->instance()->getHost();
		$oldRoot   = rtrim($extraInfo->root ?? '', '/\\');
		$newRoot   = rtrim($this->getContainer()->get('paths')->get('root') ?? '', '/\\');

		return strtolower($oldHost ?? '') != strtolower($newHost ?? '') || $oldRoot !== $newRoot;
	}

	/**
	 * Resets the database connection information of all databases
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function resetDatabaseConnectionInformationOnNewHost(): void
	{
		// Make sure we only ever run this once.
		$session = $this->getContainer()->get('session');

		if ($session->get('main.resetdbinfo'))
		{
			return;
		}

		$session->set('main.resetdbinfo', true);

		// Check if we are indeed restoring to a different host
		if (!$this->isRestoringToDifferentHost())
		{
			return;
		}

		// Remove the existing database connection information.
		$config = $this->getContainer()->get('configuration');
		$config->setDatabases(
			array_map(
				function ($database) {
					$data           = $database->toArray();
					$data['dbhost'] = '';
					$data['dbuser'] = '';
					$data['dbpass'] = '';
					$data['dbname'] = '';

					return new Database($data);
				},
				$config->databases
			)
		);
	}

	/**
	 * Populate the pre-restoration checks.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function populateChecks(): void
	{
		if (!empty($this->checks))
		{
			return;
		}

		// Instantiate all checks
		$this->checks = array_map(
			function ($checkClass) {
				return new $checkClass($this->container);
			},
			[
				RestorationCheck\MinJoomlaPHPVersion::class,
				RestorationCheck\MinWordPressPHPVersion::class,
				RestorationCheck\MaxWordPressPHPVersion::class,
				RestorationCheck\Database::class,
				RestorationCheck\IniParser::class,
				RestorationCheck\Json::class,
				RestorationCheck\Xml::class,
				RestorationCheck\Zlib::class,
				RestorationCheck\MbstringDefaultLang::class,
				RestorationCheck\MbstringNoOverload::class,
				RestorationCheck\MagicQuotesGPC::class,
				RestorationCheck\RegisterGlobals::class,
				RestorationCheck\SafeMode::class,
				RestorationCheck\DisplayErrors::class,
				RestorationCheck\FileUploads::class,
				RestorationCheck\MagicQuotesRuntime::class,
				RestorationCheck\OutputBuffering::class,
				RestorationCheck\SessionAutostart::class,
				RestorationCheck\NativeZip::class,
			]
		);

		// Remove non-applicable checks
		$this->checks = array_filter(
			$this->checks,
			function ($check) {
				return $check->isApplicable();
			}
		);
	}
}