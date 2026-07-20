<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Console;

defined('_AKEEBA') or die();

final class Terminal
{
	/**
	 * Number of terminal columns (width, measured in characters).
	 *
	 * @var   null
	 * @since 10.0
	 */
	private static $columns = null;

	/**
	 * Number of terminal rows (height, measured in characters).
	 *
	 * @var   null
	 * @since 10.0
	 */
	private static $rows = null;

	/**
	 * An internal flag indicating whether the environment has the `stty` command.
	 *
	 * @var   null
	 * @since 10.0
	 */
	private static $hasStty = null;

	/**
	 * Does the terminal support color?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private static $hasColor = false;

	public function __construct()
	{
		$this->detectColor();
		$this->initialiseTerminalSize();
	}

	/**
	 * Does the terminal support color?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasColor(): bool
	{
		return self::$hasColor;
	}

	/**
	 * Number of terminal columns (terminal width, in characters).
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getColumns(): int
	{
		return self::$columns ?? 80;
	}

	/**
	 * Number of terminal rows (terminal height, in characters).
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function getRows(): int
	{
		return self::$columns ?? 40;
	}

	/**
	 * Detects the terminal's color support.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function detectColor(): void
	{
		self::$hasColor = false;

		$colorTerm = getenv('COLORTERM');
		$term      = getenv('TERM');

		if (
			in_array($colorTerm, ['truecolor', '24bit'])
			|| in_array(
				$term, [
					'iterm',
					'linux-truecolor',
					'screen-truecolor',
					'tmux-truecolor',
					'xterm-truecolor',
					'linux-256color',
					'screen-256color',
					'tmux-256color',
					'xterm-256color',
				]
			)
			|| substr($term, -10) === '-truecolor'
			|| substr($term, -9) === '-256color'
		)
		{
			self::$hasColor = true;
		}
	}

	/**
	 * Initialise the static variables with terminal columns and rows.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function initialiseTerminalSize(): void
	{
		// If already initialised, return.
		if (self::$columns !== null && self::$rows !== null)
		{
			return;
		}

		// The UNIX way
		self::$columns = getenv('COLUMNS') ?: null;
		self::$rows    = getenv('LINES') ?: null;

		if (self::$columns !== null && self::$rows !== null)
		{
			return;
		}

		if (substr(strtoupper(PHP_OS_FAMILY), 0, 3) === 'WIN')
		{
			// Windows: parse the ANSICON environment variable
			if (preg_match('/^(\d+)x(\d+)(?: \((\d+)x(\d+)\))?$/', trim(getenv('ANSICON')), $matches))
			{
				self::$columns = (int) $matches[1];
				self::$rows    = isset($matches[4]) ? (int) $matches[4] : (int) $matches[2];
			}

			if (self::$columns !== null && self::$rows !== null)
			{
				return;
			}

			// Windows: use stty if available, and the terminal doesn't support VT100 emulation.
			if (!$this->isWin10Vt100Compatible() && $this->hasStty())
			{
				$this->initWithStty();
			}

			if (self::$columns !== null && self::$rows !== null)
			{
				return;
			}

			// Windows: try `mode CON`
			$dimensions = $this->parseModeCon();

			if ($dimensions !== null)
			{
				self::$columns = (int) $dimensions[0];
				self::$rows    = (int) $dimensions[1];
			}
		}
		else
		{
			// UNIX-like systems: try using stty
			$this->initWithStty();
		}

		if (self::$columns !== null && self::$rows !== null)
		{
			return;
		}

		// Safe fallback
		self::$columns = 80;
		self::$rows    = 40;
	}

	/**
	 * Does the terminal support VT100 emulation?
	 *
	 * This is a Windows-only method, and it only works on Windows 10 or later.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function isWin10Vt100Compatible(): bool
	{
		return \function_exists('sapi_windows_vt100_support')
		       && sapi_windows_vt100_support(fopen('php://stdout', 'w'), true);
	}

	/**
	 * Is the `stty` command available in this environment?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function hasStty(): bool
	{
		if (self::$hasStty !== null)
		{
			return self::$hasStty;
		}

		if (!function_exists('exec'))
		{
			return self::$hasStty = false;
		}

		exec('stty 2>&1', $output, $exitcode);

		return self::$hasStty = $exitcode === 0;
	}

	/**
	 * Run `stty` to get the number of terminal columns and rows.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function initWithStty(): void
	{
		exec('stty -a', $output, $exitcode);

		if ($exitcode !== 0)
		{
			return;
		}

		$sttyString = implode(
			' ',
			array_filter(
				$output,
				function ($line) {
					return strpos($line, 'columns') !== 0;
				}
			)
		);

		if (!$sttyString)
		{
			return;
		}

		if (preg_match('/rows.(\d+);.columns.(\d+);/i', $sttyString, $matches))
		{
			self::$columns = (int) $matches[2];
			self::$rows    = (int) $matches[1];

			return;
		}

		if (preg_match('/;.(\d+).rows;.(\d+).columns/i', $sttyString, $matches))
		{
			self::$columns = (int) $matches[2];
			self::$rows    = (int) $matches[1];
		}
	}

	/**
	 * Use `mode CON` under Windows to get the number of terminal columns and rows.
	 *
	 * @return  null|array<int>
	 * @since   10.0
	 */
	private function parseModeCon(): ?array
	{
		exec('mode CON', $output, $exitcode);

		if ($exitcode !== 0)
		{
			return null;
		}

		$output = implode("\n", $output);
		$output = empty(trim($output)) ? null : $output;

		return $output === null || !preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $output, $matches)
			? null
			: [(int) $matches[2], (int) $matches[1]];
	}
}