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

use Akeeba\BRS\Framework\Registry\Registry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Paths service provider
 *
 * Registers the `paths` service which is a Registry object, with keys referring to various useful paths.
 *
 * @since  10.0
 */
class Paths implements ServiceProviderInterface
{
	/**
	 * Register the service into the container
	 *
	 * @param   Container  $pimple  The container to register the service into
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function register(Container $pimple)
	{
		$pimple['paths'] = function ($pimple) {
			return new Registry($this->getPaths());
		};
	}

	/**
	 * Returns the paths to add to the Registry object
	 *
	 * @return  array
	 * @since   10.0
	 */
	protected function getPaths(): array
	{
		$base = $this->getBRSBasePath();
		$root = $this->getSiteRootPath();

		return [
			'installation'  => $base ?: '/',
			'base'          => $base ?: '/',
			'language'      => $base . '/language',
			'viewtemplate'  => $base . '/ViewTemplate',
			'media'         => $base . '/media',
			'themes'        => $base . '/template',
			'platform'      => [
				'assets'       => $base . '/platform/assets',
				'base'         => $base . '/platform',
				'language'     => $base . '/platform/language',
				'viewtemplate' => $base . '/platform/ViewTemplate',
				'media'        => $base . '/platform/media',
				'src'          => $base . '/platform/src',
			],
			'root'          => $root ?: '/',
			'site'          => $root ?: '/',
			'configuration' => $root ?: '/',
			'administrator' => $root . '/administrator',
			'libraries'     => $root . '/libraries',
			'tempinstall'   => $base . '/tmp',
		];
	}

	/**
	 * Returns the absolute filesystem path to the installation directory.
	 *
	 * @return  string
	 * @since   10.0
	 */
	protected function getBRSBasePath(): string
	{
		$here      = getcwd();
		$isCorrect = array_reduce(
			[
				'language',
				'media',
				'src',
				'template',
				'tmp',
				'vendor',
				'ViewTemplates',
			],
			function ($carry, $item) use ($here) {
				return $carry && @is_dir($here . DIRECTORY_SEPARATOR . $item);
			},
			true
		);

		if ($isCorrect)
		{
			return $here;
		}

		$parts = explode(DIRECTORY_SEPARATOR, __DIR__);

		array_pop($parts);
		array_pop($parts);
		array_pop($parts);

		return implode(DIRECTORY_SEPARATOR, $parts);
	}

	/**
	 * Returns the absolute filesystem path to the site's root directory.
	 *
	 * @return  string
	 * @since   10.0
	 */
	protected function getSiteRootPath(): string
	{
		$parts = explode(DIRECTORY_SEPARATOR, $this->getBRSBasePath());

		array_pop($parts);

		return implode(DIRECTORY_SEPARATOR, $parts);
	}
}