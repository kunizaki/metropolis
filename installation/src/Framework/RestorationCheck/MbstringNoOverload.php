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
 * Pre-restoration check: MBstring must not overload functions
 *
 * @since      10.0
 */
class MbstringNoOverload extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_MBSTRING_OVERLOAD', false, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla() &&
			$this->hasMbString();
	}

	private function hasMbString(): bool
	{
		if (function_exists('extension_loaded'))
		{
			return extension_loaded( 'mbstring' );
		}

		return function_exists('mb_language');
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		return (bool) ini_get('mbstring.func_overload');
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->text('MAIN_ERR_CHECK_MBSTRING_OVERLOAD');
	}
}