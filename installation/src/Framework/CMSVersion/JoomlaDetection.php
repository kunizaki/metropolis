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
 * Detect the Joomla! version and store it into the database.
 *
 * @since  10.0
 */
final class JoomlaDetection extends AbstractDetection
{
	/** @inheritdoc */
	public function detectVersion(): void
	{
		if ($this->getContainer()->get('configuration')->type !== 'joomla')
		{
			return;
		}

		$session = $this->container->get('session');

		$session->set('jversion', $this->getVersion());
		$session->saveData();
	}

	/**
	 * Detect and return the Joomla! version.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function getVersion(): string
	{
		$libPath = $this->getContainer()->get('paths')->get('libraries');

		defined('_JEXEC') || define('_JEXEC', 1);
		defined('JPATH_PLATFORM') || define('JPATH_PLATFORM', 1);

		foreach (
			[
				$libPath . '/cms/version/version.php',
				$libPath . '/src/Version.php',
			] as $file
		)
		{
			if (file_exists($file))
			{
				include_once $file;
			}
		}

		foreach (
			[
				'JVersion',
				'Joomla\CMS\Version',
			] as $class
		)
		{
			if (class_exists($class))
			{
				return (new $class())->getShortVersion();
			}
		}

		return '2.5.0';
	}
}