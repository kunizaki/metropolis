<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\RestorationCheck;

defined('_AKEEBA') or die();

use Psr\Container\ContainerInterface;

/**
 * Pre-restoration check: Minimum PHP version for the backed up Joomla! version.
 *
 * @since  10.0
 */
class MinJoomlaPHPVersion extends AbstractRestorationCheck
{
	private $minPhpVersion;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$name = $this->getContainer()->get('language')->sprintf(
			'MAIN_LBL_CHECK_PHP_MINIMUM', $this->getMinimumPHPVersion()
		);

		parent::__construct($container, $name, true, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla();
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		$minPHPVersion = $this->getMinimumPHPVersion();

		return version_compare(PHP_VERSION, $minPHPVersion, 'ge');
	}

	/**
	 * Get the minimum PHP version required for the currently detected Joomla! version.
	 *
	 * The minimum required PHP version for each Joomla major version is drawn from the official Joomla! documentation.
	 *
	 * @return  string
	 * @since   10.0
	 * @link    https://manual.joomla.org/docs/next/get-started/technical-requirements/
	 */
	private function getMinimumPHPVersion(): string
	{
		if ($this->minPhpVersion !== null)
		{
			return $this->minPhpVersion;
		}

		$jVersion = $this->getContainer()->get('session')->get('jversion', '5.0.0');

		if (version_compare($jVersion, '3.2.0', 'lt'))
		{
			/**
			 * Joomla! 1.6 to 3.1
			 *
			 * @note Requirements for Joomla! 3.0, 3.1 are not documented separately, but where noted down when these
			 * versions were current.
			 */
			$minPHPVersion = '5.2.4';
		}
		elseif (version_compare($jVersion, '4.0.0', 'lt'))
		{
			/**
			 * Joomla! 3.2 to 3.10
			 */
			$minPHPVersion = '5.3.10';
		}
		elseif (version_compare($jVersion, '5.0.0', 'lt'))
		{
			/**
			 * Joomla! 4
			 */
			$minPHPVersion = '7.2.5';
		}
		else
		{
			/**
			 * Joomla! 5
			 */
			$minPHPVersion = '8.1.0';
		}

		return $this->minPhpVersion = $minPHPVersion;
	}
}