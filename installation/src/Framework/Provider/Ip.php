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
 * Visitor IP helper service provider.
 *
 * @since  10.0
 */
class Ip implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['ip'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Ip\Ip($pimple);
		};
	}
}