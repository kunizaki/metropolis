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
 * Path helper service provider.
 *
 * Registers the `path` service.
 *
 * @since  10.0
 */
class Path implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['path'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Filesystem\Path($pimple);
		};
	}
}