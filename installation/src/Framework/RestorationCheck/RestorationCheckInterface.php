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

/**
 * Defines the interface for restoration checks.
 *
 * @since  10.0
 */
interface RestorationCheckInterface
{
	/**
	 * Is the current value valid?
	 *
	 * @return bool
	 * @since  10.0
	 */
	public function isValid(): bool;

	/**
	 * Is this a required check?
	 *
	 * @return bool
	 * @since  10.0
	 */
	public function isRequired(): bool;

	/**
	 * Get the check's name.
	 *
	 * @return string
	 * @since  10.0
	 */
	public function getName(): string;

	/**
	 * Get the expected value.
	 *
	 * @return mixed
	 * @since  10.0
	 */
	public function getExpected();

	/**
	 * Get the current value.
	 *
	 * @return mixed
	 * @since  10.0
	 */
	public function getCurrentValue();

	/**
	 * Is this check applicable to the current restoration?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function isApplicable(): bool;

	/**
	 * Get any relevant notice to display under a failing check.
	 *
	 * @return  string|null  A notice to print; NULL if there is nothing to print.
	 * @since   10.0
	 */
	public function getNotice(): ?string;
}