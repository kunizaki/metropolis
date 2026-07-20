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
 * Pre-restoration check: Minimum PHP version for the backed up WordPress version.
 *
 * @since  10.0
 */
class MinWordPressPHPVersion extends AbstractRestorationCheck
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
		return $this->isWordPress();
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		$minPHPVersion = $this->getMinimumPHPVersion();

		return version_compare(PHP_VERSION, $minPHPVersion, 'ge');
	}

	/**
	 * Get the minimum PHP version required for the currently detected WordPress version.
	 *
	 * The minimum required PHP version for each WordPress version is collected from its official source.
	 *
	 * @return  string
	 * @since   10.0
	 * @link    https://make.wordpress.org/hosting/handbook/compatibility/#server-requirements
	 */
	private function getMinimumPHPVersion(): string
	{
		if ($this->minPhpVersion !== null)
		{
			return $this->minPhpVersion;
		}

		$wpVersion = $this->getContainer()->get('session')->get('version', '5.0.0');

		if (version_compare($wpVersion, '2.5.0', 'lt'))
		{
			// WordPress 2.0 to 2.4
			$minPHPVersion = '4.2.0';
		}
		elseif (version_compare($wpVersion, '3.2', 'lt'))
		{
			// WordPress 2.5 to 3.1
			$minPHPVersion = '4.3.0';
		}
		elseif (version_compare($wpVersion, '5.2', 'lt'))
		{
			// WordPress 3.2 to 5.1
			$minPHPVersion = '5.2.4';
		}
		elseif (version_compare($wpVersion, '6.3', 'lt'))
		{
			// WordPress 5.2 to 6.2
			$minPHPVersion = '5.6.20';
		}
		elseif (version_compare($wpVersion, '6.6', 'lt'))
		{
			// WordPress 6.3 to 6.5
			$minPHPVersion = '7.0';
		}
		else
		{
			// WordPress 6.6
			$minPHPVersion = '7.2.25';
		}

		return $this->minPhpVersion = $minPHPVersion;
	}
}