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

use Akeeba\BRS\Framework\Uri\Factory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Uri factory service provider.
 *
 * Registers the `uri` service.
 *
 * @since  10.0
 */
class Uri implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['uri'] = function ($pimple) {
			return new Factory($pimple);
		};
	}
}