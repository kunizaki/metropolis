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
 * Log facility service provider.
 *
 * Registers the `log` service with the default LoggerInterface instance.
 *
 * @since  10.0
 */
class Log implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['log'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Log\Log($pimple);
		};
	}
}