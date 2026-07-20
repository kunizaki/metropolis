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
 * Pre-restoration check: Automatic Output Buffering disabled
 *
 * @since      10.0
 */
class NativeZip extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_ZIP_NATIVE', true, false);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla();
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		return function_exists('zip_open') && function_exists('zip_read');
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->sprintf('MAIN_ERR_CHECK_ZIP_NATIVE', PHP_VERSION);
	}
}