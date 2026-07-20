<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

use Akeeba\BRS\Framework\Database\Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

defined('_AKEEBA') or die();

/**
 * Database object factory service provider.
 *
 * Registers the `db` service.
 *
 * @since  10.0
 */
class DatabaseFactory implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['db'] = function ($pimple) {
			return new Factory($pimple);
		};
	}
}