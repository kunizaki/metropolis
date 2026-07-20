<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Log;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;

/**
 * PSR-3 compliant log to file facility.
 *
 * @since 10.0
 */
final class Log implements LoggerInterface, ContainerAwareInterface
{
	use LoggerTrait;
	use ContainerAwareTrait;

	/**
	 * The absolute path to the log file.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $logFile;

	/**
	 * The file resource to the open log file, NULL/false if couldn't open.
	 *
	 * @var   false|resource|null
	 * @since 10.0
	 */
	private $fp;

	/**
	 * Minimum severity to log.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $minSeverity;

	/**
	 * Construct a logger
	 *
	 * @param   string|null  $logFile      The log file to open. NULL for default (/installation/log.txt)
	 * @param   string|null  $minSeverity  Minimum severity. NULL for default (DEBUG when AKEEBA_DEBUG enabled; ERROR
	 *                                     otherwise).
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, ?string $logFile = null, ?string $minSeverity = null)
	{
		$this->setContainer($container);

		$defaultMinSeverity = (defined('AKEEBA_DEBUG') && constant('AKEEBA_DEBUG')) ? LogLevel::DEBUG : LogLevel::ERROR;

		$this->logFile     = $logFile ?: ($container->get('paths')->get('installation') . '/log.txt');
		$this->fp          = @fopen($this->logFile, 'a');
		$this->fp          = $this->fp === false ? null : $this->fp;
		$this->minSeverity = $minSeverity ?: $defaultMinSeverity;
	}

	/**
	 * Destructor.
	 *
	 * Automatically closes the log file when the object instance is destroyed.
	 *
	 * @since   10.0
	 */
	public function __destruct()
	{
		if (@is_resource($this->fp))
		{
			@fclose($this->fp);
		}
	}

	/** @inheritDoc */
	public function log($level, $message, array $context = [])
	{
		if ($this->compareSeverity($level) < 0)
		{
			return;
		}

		$v = microtime(true);
		$timestring = gmdate('Y-m-d H:i:s.') . sprintf('%06u', intval(1000000 * ($v - floor($v))));
		$line       = str_pad($level, 10, ' ') . '| ' . $timestring . ' | '
		              . str_replace("\n", ' ', $message) . "\n";

		@fputs($this->fp, $line);

		if (!empty($context))
		{
			@fputs($this->fp, str_repeat(' ', 33) . print_r($context, true) . "\n");
		}
	}

	/**
	 * Set the minimum severity to log.
	 *
	 * @param   string  $severity  The minimum logged severity level to set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setMinimumSeverity(string $severity): void
	{
		if (empty($severity))
		{
			$severity = (defined('AKEEBA_DEBUG') && constant('AKEEBA_DEBUG'))
				? LogLevel::DEBUG
				: LogLevel::ERROR;
		}

		$this->minSeverity = $severity;
	}

	/**
	 * Clear the log file.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function reset()
	{
		if (@is_resource($this->fp))
		{
			@fclose($this->fp);
		}

		@unlink($this->logFile);

		$this->fp          = @fopen($this->logFile, 'a');
		$this->fp          = $this->fp === false ? null : $this->fp;
	}

	/**
	 * Compares two severities.
	 *
	 * @param   string       $severity1  The left hand severity to test.
	 * @param   string|null  $severity2  The right hand severity to test, empty/null for minimum severity.
	 *
	 * @return  int  -1 if left less than right, 0 if equal, 1 if left greater than right.
	 * @since   10.0
	 */
	protected function compareSeverity(string $severity1, ?string $severity2 = null): int
	{
		$s1 = $this->severityToInteger($severity1);
		$s2 = $this->severityToInteger($severity2 ?: $this->minSeverity);

		return $s1 <=> $s2;
	}

	/**
	 * Converts a PSR-3 severity to an integer for comparison purposes.
	 *
	 * @param   string|null  $severity  The severity string to convert.
	 *
	 * @return  int
	 * @since   10.0
	 */
	private function severityToInteger(?string $severity): int
	{
		switch ($severity)
		{
			default:
			case LogLevel::DEBUG:
				return 0;

			case LogLevel::INFO:
				return 10;

			case LogLevel::NOTICE:
				return 20;

			case LogLevel::WARNING:
				return 30;

			case LogLevel::ERROR:
				return 40;

			case LogLevel::CRITICAL:
				return 50;

			case LogLevel::ALERT:
				return 60;

			case LogLevel::EMERGENCY:
				return 70;
		}
	}
}