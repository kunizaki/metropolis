<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\DataShape\SiteInfo;

use Akeeba\BRS\DataShape\SiteInfoInterface;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Am abstract class for a site setting whose values at backup and restoration time will be reported to the user.
 *
 * @since  10.0
 */
abstract class AbstractSiteInfo implements SiteInfoInterface
{
	/**
	 * The name of the setting.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $name;

	/**
	 * The setting value at backup time.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	protected $atBackup;

	/**
	 * The setting value at restoration time.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	protected $atRestore;

	/**
	 * Information to print if the value has changed.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	protected $changedInfo;

	/**
	 * Constructor method to initialize object properties.
	 *
	 * @param   string       $name         The name of the setting.
	 * @param   string|null  $atBackup     The setting value at backup time.
	 * @param   string|null  $atRestore    The setting value at restoration time.
	 * @param   string|null  $changedInfo  Information to print if the value has changed.
	 *
	 */
	public function __construct(
		string $name, ?string $atBackup = null, ?string $atRestore = null, ?string $changedInfo = null
	)
	{
		$this->name        = $name;
		$this->atBackup    = $atBackup;
		$this->atRestore   = $atRestore;
		$this->changedInfo = $changedInfo;
	}

	/**
	 * Static factory method.
	 *
	 * @param   ContainerInterface  $container  The application container.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	abstract public static function make(ContainerInterface $container);

	/** @inheritdoc */
	final public function getName(): string
	{
		return $this->name;
	}

	/** @inheritdoc */
	final public function getAtBackup(): ?string
	{
		return $this->atBackup;
	}

	/** @inheritdoc */
	final public function getAtRestore(): ?string
	{
		return $this->atRestore;
	}

	/** @inheritdoc */
	public function isChanged(): bool
	{
		// If we can't be sure, play it safe
		if ($this->atBackup === null || $this->atRestore === null)
		{
			return false;
		}

		return $this->atBackup !== $this->atRestore;
	}

	/** @inheritdoc */
	public function getChangedInfo(): ?string
	{
		if (!$this->isChanged())
		{
			return null;
		}

		return $this->changedInfo;
	}
}