<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

defined('_AKEEBA') or die();

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Breadcrumbs service provider.
 *
 * @since  10.0
 */
class Breadcrumbs implements ServiceProviderInterface
{
	/** @inheritdoc */
	public function register(Container $pimple)
	{
		$pimple['breadcrumbs'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Template\Breadcrumbs($pimple);
		};
	}
}