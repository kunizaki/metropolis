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

use Psr\Container\ContainerInterface;

/**
 * Site Information Setting: Date
 *
 * Warns the user if the current date is older than the backup date, or when the backup date is more than 6 months into
 * the past.
 *
 * @since  10.0
 */
class DateCheck extends AbstractSiteInfo
{
	/** @inheritdoc  */
	public static function make(ContainerInterface $container)
	{
		$lang = $container->get('language');
		$info = $container->get('configuration')->extraInfo;

		try
		{
			$oldDate = new \DateTime($info->backup_date . ' UTC');
		}
		catch (\Throwable $e)
		{
			$oldDate = null;
		}

		$newDate = (new \DateTime())->setTimezone(new \DateTimeZone('UTC'));
		$changedInfo = null;

		if ($oldDate !== null && $oldDate > $newDate)
		{
			$changedInfo = $lang->text('MAIN_LBL_EXTRAINFO_DATE_PAST');
		}
		elseif ($oldDate !== null && $oldDate->add(new \DateInterval('P6M')) < $newDate)
		{
			$changedInfo = $lang->text('MAIN_LBL_EXTRAINFO_DATE_FUTURE');
		}

		return new self(
			$lang->text('MAIN_LBL_EXTRAINFO_DATE'),
			$info->backup_date . ' UTC',
			$newDate->format('Y-m-d H:i:s T'),
			$changedInfo
		);
	}

	/** @inheritdoc  */
	public function isChanged(): bool
	{
		if (!parent::isChanged())
		{
			return false;
		}

		return $this->changedInfo !== null;
	}
}