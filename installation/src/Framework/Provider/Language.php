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
 * Language service provider.
 *
 * Registers the `language` service which is an instance of Language.
 *
 * @since  10.0
 */
class Language implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['language'] = function ($pimple) {
			return new \Akeeba\BRS\Framework\Language\Language($pimple);
		};
	}
}