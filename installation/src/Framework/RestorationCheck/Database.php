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

use Akeeba\BRS\Framework\Database\Driver\Mysqli;
use Akeeba\BRS\Framework\Database\Driver\Pdomysql;
use Psr\Container\ContainerInterface;

/**
 * Pre-restoration check: Database support
 *
 * @since      10.0
 */
class Database extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_DATABASE_SUPPORT', true, true);
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		return Mysqli::isSupported() || Pdomysql::isSupported();
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		// Always required for Joomla! and WordPress restoration.
		if ($this->isJoomla() || !$this->isWordPress())
		{
			return true;
		}

		// For anything else it depends if we have any databases to restore.
		return count($this->getContainer()->get('configuration')->databases) >= 1;
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->sprintf('MAIN_ERR_CHECK_DATABASE_SUPPORT', PHP_VERSION);
	}


}