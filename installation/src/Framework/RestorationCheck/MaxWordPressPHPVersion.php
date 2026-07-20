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
 * Pre-restoration check: Maximum PHP version for the backed up WordPress version.
 *
 * @since  10.0
 */
class MaxWordPressPHPVersion extends AbstractRestorationCheck
{
	private $maxPhpVersion;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$name = $this->getContainer()->get('language')->sprintf(
			'MAIN_LBL_CHECK_PHP_MAX', $this->getMaximumPHPVersion()
		);

		parent::__construct($container, $name, true, false);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		if (!$this->isWordPress())
		{
			return false;
		}

		$wpVersion = $this->getContainer()->get('session')->get('version', '5.0.0');

		if (version_compare($wpVersion, '3.7', 'lt') || version_compare($wpVersion, '7.0', 'ge'))
		{
			return false;
		}

		return true;
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		$minPHPVersion = $this->getMaximumPHPVersion();

		return version_compare(PHP_VERSION, $minPHPVersion, 'le');
	}

	/**
	 * Get the minimum PHP version required for the currently detected WordPress version.
	 *
	 * The minimum required PHP version for each WordPress version is collected from its official source.
	 *
	 * @return  string
	 * @since   10.0
	 * @link    https://make.wordpress.org/hosting/handbook/compatibility/#wordpress-php-mysql-mariadb-versions
	 */
	private function getMaximumPHPVersion(): string
	{
		if ($this->maxPhpVersion !== null)
		{
			return $this->maxPhpVersion;
		}

		$wpVersion = $this->getContainer()->get('session')->get('version', '5.0.0');

		if (version_compare($wpVersion, '4.0', 'lt'))
		{
			// WordPress 3.7 to 3.9
			$minPHPVersion = '5.6';
		}
		elseif (version_compare($wpVersion, '4.4', 'lt'))
		{
			// WordPress 4.0 to 4.3
			$minPHPVersion = '7.0';
		}
		elseif (version_compare($wpVersion, '4.7', 'lt'))
		{
			// WordPress 4.4 to 4.6
			$minPHPVersion = '7.1';
		}
		elseif (version_compare($wpVersion, '4.9', 'lt'))
		{
			// WordPress 4.7 to 4.8
			$minPHPVersion = '7.2';
		}
		elseif (version_compare($wpVersion, '5.0', 'lt'))
		{
			// WordPress 4.9
			$minPHPVersion = '7.3';
		}
		elseif (version_compare($wpVersion, '5.3', 'lt'))
		{
			// WordPress 5.0 to 5.2
			$minPHPVersion = '7.4';
		}
		elseif (version_compare($wpVersion, '5.6', 'lt'))
		{
			// WordPress 5.3 to 5.5
			$minPHPVersion = '8.0';
		}
		elseif (version_compare($wpVersion, '5.9', 'lt'))
		{
			// WordPress 5.6 to 5.8
			$minPHPVersion = '8.1';
		}
		elseif (version_compare($wpVersion, '6.2', 'lt'))
		{
			// WordPress 5.9 to 6.1
			$minPHPVersion = '8.2';
		}
		elseif (version_compare($wpVersion, '6.5', 'lt'))
		{
			// WordPress 6.2 to 6.4
			$minPHPVersion = '8.3';
		}
		else
		{
			// WordPress 6.4 onwards
			$minPHPVersion = '8.4';
		}

		return $this->maxPhpVersion = $minPHPVersion;
	}
}