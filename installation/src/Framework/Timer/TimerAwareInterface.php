<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Timer;

defined('_AKEEBA') or die();

/**
 * Interface for objects which have an Akeeba timer instance.
 *
 * @since  10.0
 */
interface TimerAwareInterface
{
	/**
	 * Assigns a Timer object.
	 *
	 * This should only be used internally by the constructor. The constructor itself should use explicit dependency
	 * injection.
	 *
	 * @param   TimerInterface  $timer  The timer object to assign
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setTimer(TimerInterface $timer): void;

	/**
	 * Returns a reference to the timer object. This should only be used internally.
	 *
	 * @return  TimerInterface
	 * @since   10.0
	 */
	public function getTimer(): TimerInterface;
}