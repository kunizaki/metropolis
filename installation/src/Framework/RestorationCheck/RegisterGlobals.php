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
 * Pre-restoration check: Register Globals must be disabled.
 *
 * @since      10.0
 * @deprecated 11.0 This is no longer supported by any PHP version we can restore to.
 */
class RegisterGlobals extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_REGISTER_GLOBALS', false, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		$jVersion = $this->getContainer()->get('session')->get('jversion', '5.0.0');

		/**
		 * Only applies to Joomla! 3.0 or later, on PHP up to and including 5.3.0.
		 *
		 * @link  https://www.php.net/manual/en/function.get-magic-quotes-gpc.php
		 */
		return $this->isJoomla()
		       && version_compare($jVersion, '3.0.0', 'ge')
		       && version_compare(PHP_VERSION, '5.4.0', 'lt');
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		if (function_exists('ini_get'))
		{
			return (bool) ini_get('register_globals');
		}

		return false;
	}
}