<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Provider;

use Akeeba\BRS\Framework\Application\AbstractApplication;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

defined('_AKEEBA') or die();

/**
 * Application service provider
 *
 * @since  10.0
 */
class Application implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['application'] = function (Container $pimple) {
			if ($pimple->offsetExists('application_class')
			    && class_exists($pimple['application_class'])
			       && is_a($pimple['application_class'], AbstractApplication::class, true))
			{
				return new $pimple['application_class']($pimple);
			}

			if ($pimple->offsetExists('application_name'))
			{
				$className = '\\Akeeba\\' . $pimple['application_name'] . '\\Application\\Application';

				if (class_exists($className) && is_a($className, AbstractApplication::class, true))
				{
					return new $className($pimple);
				}
			}

			/** @noinspection PhpUndefinedClassInspection */
			$className = \Akeeba\BRS\Application\Application::class;

			if (class_exists($className) && is_a($className, AbstractApplication::class, true))
			{
				return new $className($pimple);
			}

			throw new \RuntimeException('Cannot find an Application class.');
		};
	}
}