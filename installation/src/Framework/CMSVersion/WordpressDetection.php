<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\CMSVersion;

defined('_AKEEBA') or die();

/**
 * Detect the WordPress version and store it into the database.
 *
 * @since  10.0
 */
final class WordpressDetection extends AbstractDetection
{
	/** @inheritdoc */
	public function detectVersion(): void
	{
		if ($this->getContainer()->get('configuration')->type !== 'wordpress')
		{
			return;
		}

		$session = $this->container->get('session');

		$session->set('version', $this->getVersion());
		$session->saveData();
	}

	/**
	 * Detect and return the WordPress version.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function getVersion(): string
	{
		$filename = $this->getContainer()->get('paths')->get('root') . '/wp-includes/version.php';

		if (!file_exists($filename))
		{
			return '3.0.0';
		}

		// Let's load the version file, there "shouldn't" be any problems
		include_once $filename;

		/** @var $wp_version */
		return $wp_version;
	}
}