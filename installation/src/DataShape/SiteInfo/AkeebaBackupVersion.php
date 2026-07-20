<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\DataShape\SiteInfo;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Version\Version;
use Psr\Container\ContainerInterface;

/**
 * Site Information Setting: Backup software version
 *
 * Warns the user if they're using a restoration script older than their backup software's version.
 *
 * @since  10.0
 */
class AkeebaBackupVersion extends AbstractSiteInfo
{
	/** @inheritdoc */
	public static function make(ContainerInterface $container)
	{
		$lang = $container->get('language');
		$info = $container->get('configuration')->extraInfo;

		$oldVersionText = $info->akeeba_version ?? null;
		$newVersionText = defined('AKEEBA_VERSION') ? AKEEBA_VERSION : null;
		$isChanged      = false;

		if ($oldVersionText !== null && $newVersionText !== null)
		{
			$oldVersion = new Version($oldVersionText);
			$newVersion = new Version($newVersionText);

			$isChanged = version_compare($oldVersion->shortVersion(true), $newVersion->shortVersion(true), '>');
		}

		return new self(
			$lang->text('MAIN_LBL_EXTRAINFO_AKEEBAVERSION'),
			$oldVersionText,
			$newVersionText,
			$isChanged ? $lang->text('MAIN_LBL_EXTRAINFO_AKEEBAVERSION_OLDER') : null
		);
	}

	/** @inheritdoc  */
	public function isChanged(): bool
	{
		return $this->changedInfo !== null;
	}
}