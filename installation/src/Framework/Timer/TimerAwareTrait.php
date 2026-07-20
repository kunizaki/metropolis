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
 * Trait implementing the TimerAwareInterface.
 *
 * @since  10.0
 */
trait TimerAwareTrait
{
	/**
	 * The timer object
	 *
	 * @var   TimerInterface
	 * @since 10.0
	 */
	protected $timer = null;

	/**
	 * Returns a reference to the timer object. This should only be used internally.
	 *
	 * @return  TimerInterface
	 * @since   10.0
	 */
	public function getTimer(): TimerInterface
	{
		return $this->timer;
	}

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
	public function setTimer(TimerInterface $timer): void
	{
		$this->timer = $timer;
	}
}