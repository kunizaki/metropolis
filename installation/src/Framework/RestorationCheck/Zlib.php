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
 * Pre-restoration check: Zlib compression support
 *
 * @since      10.0
 */
class Zlib extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_ZLIB', true, true);
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		if (extension_loaded('ini_get'))
		{
			return extension_loaded('zlib');
		}

		return function_exists('gzencode') && function_exists('gzdecode');
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla() || $this->isWordPress();
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->sprintf('MAIN_ERR_CHECK_ZLIB', PHP_VERSION);
	}
}