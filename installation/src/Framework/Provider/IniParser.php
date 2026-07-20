<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

use Akeeba\BRS\Framework\IniFiles\Parser;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

defined('_AKEEBA') or die();

/**
 * INI data parser service provider.
 *
 * Registers the `iniParser` service with the default Parser instance.
 *
 * @since  10.0
 */
class IniParser implements ServiceProviderInterface
{
	/** @inheritDoc */
	public function register(Container $pimple)
	{
		$pimple['iniParser'] = function ($pimple) {
			return new Parser();
		};
	}
}