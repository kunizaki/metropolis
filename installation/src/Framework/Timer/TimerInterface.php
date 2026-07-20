<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Timer;

use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Interface to the Akeeba timer object.
 *
 * @since  10.0
 */
interface TimerInterface
{
	/**
	 * Constructor.
	 *
	 * @param   int  $minExecTime  Minimum execution time
	 * @param   int  $maxExecTime  Maximum execution time
	 * @param   int  $runtimeBias  Runtime bias
	 *
	 * @since   10.0
	 */
	public function __construct(
		ContainerInterface $container, int $minExecTime = 0, int $maxExecTime = 5, int $runtimeBias = 75
	);

	/**
	 * Gets the number of seconds left, before we hit the "must break" threshold
	 *
	 * @return  float
	 * @since   10.0
	 */
	public function getTimeLeft(): float;

	/**
	 * Gets the time elapsed since object creation/unserialization, effectively how long this step is running.
	 *
	 * @return  float
	 * @since   10.0
	 */
	public function getRunningTime(): float;

	/**
	 * Reset the timer
	 *
	 * @since  10.0
	 */
	public function resetTime(): void;
}