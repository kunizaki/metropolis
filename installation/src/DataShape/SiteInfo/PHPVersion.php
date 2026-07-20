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
 * Site Information Setting: PHP version
 *
 * Warns the user if they're restoring to a different PHP version family than the one they backed up from.
 *
 * @since  10.0
 */
class PHPVersion extends AbstractSiteInfo
{
	public static function make(ContainerInterface $container)
	{
		$lang = $container->get('language');
		$info = $container->get('configuration')->extraInfo;

		$oldVersionText = $info->php_version ?? null;
		$newVersionText = PHP_VERSION;
		$isChanged      = $info->php_version ?? null;

		if ($oldVersionText !== null && $newVersionText !== null)
		{
			$oldVersion = new Version($oldVersionText);
			$newVersion = new Version($newVersionText);

			$isChanged = $oldVersion->versionFamily() != $newVersion->versionFamily();
		}

		return new self(
			$lang->text('MAIN_LBL_EXTRAINFO_PHPVERSION'),
			$oldVersionText,
			$newVersionText,
			$isChanged ? $lang->sprintf('MAIN_LBL_EXTRAINFO_PHPVERSION_DIFFERS', $newVersionText, $oldVersionText) : null
		);
	}

	/** @inheritdoc  */
	public function isChanged(): bool
	{
		return $this->changedInfo !== null;
	}
}