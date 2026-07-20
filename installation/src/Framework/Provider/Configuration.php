<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

defined('_AKEEBA') or die();

/**
 * Provides the Configuration service.
 *
 * @since  10.0
 */
class Configuration implements ServiceProviderInterface
{
	/** @inheritdoc  */
	public function register(Container $pimple)
	{
		$pimple['configuration'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Configuration\Configuration($pimple);
		};
	}
}