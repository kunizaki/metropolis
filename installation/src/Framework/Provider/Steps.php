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

use Akeeba\BRS\Framework\Steps\StepQueue;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Steps service provider.
 *
 * @since  10.0
 */
class Steps implements ServiceProviderInterface
{
	/** @inheritdoc  */
	public function register(Container $pimple)
	{
		$pimple['steps'] = function ($pimple) {
			return new StepQueue($pimple);
		};
	}
}