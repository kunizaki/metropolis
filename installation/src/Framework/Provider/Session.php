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
 * Session service provider.
 *
 * @since  10.0
 */
class Session implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['session'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Session\Session($pimple);
		};
	}
}