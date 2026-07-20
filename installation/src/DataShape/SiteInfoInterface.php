<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\DataShape;

defined('_AKEEBA') or die();

/**
 * Interface defining the contract for SiteInfoItem.
 *
 * @since 10.0
 */
interface SiteInfoInterface
{
	/**
	 * Returns the name of the setting.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getName(): string;

	/**
	 * Returns the setting value at backup time.
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getAtBackup(): ?string;

	/**
	 * Returns the setting value at restoration time.
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getAtRestore(): ?string;

	/**
	 * Has the value changed in any significant way between backup and restoration?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isChanged(): bool;

	/**
	 * Returns the information to show for changed state.
	 *
	 * @return  null|string
	 * @since   10.0
	 */
	public function getChangedInfo(): ?string;
}
