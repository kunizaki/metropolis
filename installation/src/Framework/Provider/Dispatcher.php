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
 * Dispatcher service provider.
 *
 * Registers the `dispatcher` service.
 *
 * @since  10.0
 */
class Dispatcher implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['dispatcher'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Dispatcher\Dispatcher($pimple);
		};
	}
}