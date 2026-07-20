<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Timer;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Process timer class
 *
 * @since  10.0
 */
final class Timer implements ContainerAwareInterface, TimerInterface
{
	use ContainerAwareTrait;

	/**
	 * Minimum execution time for step
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $minExecTime = 0;

	/**
	 * Maximum execution time allowance per step
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $maxExecTime = null;

	/**
	 * Timestamp of execution start
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $startTime = null;

	/** @inheritdoc  */
	public function __construct(
		ContainerInterface $container, int $minExecTime = 0, int $maxExecTime = 5, int $runtimeBias = 75
	)
	{
		$this->setContainer($container);

		$this->getContainer()->get('log')->debug(__METHOD__ . '(' . $maxExecTime . ', ' . $runtimeBias . ')');

		// Initialize start time
		$this->startTime   = microtime(true);
		$this->maxExecTime = $maxExecTime * $runtimeBias / 100;
		$this->minExecTime = $minExecTime;
	}

	/**
	 * Wake-up function to reset the internal timer when we get unserialized
	 *
	 * @since   10.0
	 */
	public function __wakeup()
	{
		$this->startTime = microtime(true);
	}

	/** @inheritdoc  */
	public function getTimeLeft(): float
	{
		return $this->maxExecTime - $this->getRunningTime();
	}

	/** @inheritdoc  */
	public function getRunningTime(): float
	{
		return microtime(true) - $this->startTime;
	}

	/** @inheritdoc  */
	public function resetTime(): void
	{
		$this->startTime = microtime(true);
	}
}