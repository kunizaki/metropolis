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
 * Input service provider.
 *
 * Registers the `input` service with the default Input instance.
 *
 * @since  10.0
 */
class Input implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['input'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Input\Input();
		};
	}
}