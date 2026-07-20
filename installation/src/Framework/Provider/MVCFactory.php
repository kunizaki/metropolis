<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

use Akeeba\BRS\Framework\Mvc\Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

defined('_AKEEBA') or die();

/**
 * MVC object factory service provider.
 *
 * Registers the `mvcFactory` service.
 *
 * @since  10.0
 */
class MVCFactory implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['mvcFactory'] = function ($pimple) {
			return new Factory($pimple);
		};
	}
}