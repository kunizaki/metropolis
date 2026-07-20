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
 * Site Information Setting: Hostname
 *
 * Warns the user if they're restoring to a new domain or subdomain.
 *
 * @since  10.0
 */
class HostName extends AbstractSiteInfo
{
	/** @inheritdoc  */
	public static function make(ContainerInterface $container)
	{
		$lang = $container->get('language');
		$info = $container->get('configuration')->extraInfo;
		$type = $container->get('configuration')->type;

		return new self(
			$lang->text('MAIN_LBL_EXTRAINFO_HOST'),
			$info->host ?? null,
			$container->get('uri')->instance()->getHost(),
			$lang->text('MAIN_LBL_EXTRAINFO_HOST_DIFFERS_' . $type)
		);
	}
}