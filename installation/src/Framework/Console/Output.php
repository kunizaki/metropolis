<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Console;

use Akeeba\BRS\Framework\String\StringHelper;

defined('_AKEEBA') or die();

/**
 * Console output handler
 *
 * @method void none(array|string $messages)
 * @method void title(array|string $messages)
 * @method void copyright(array|string $messages)
 * @method void heading(array|string $messages)
 * @method void success(array|string $messages)
 * @method void error(array|string $messages)
 * @method void warning(array|string $messages)
 * @method void noneBlock(array|string $messages, int $padding = 2)
 * @method void titleBlock(array|string $messages, int $padding = 2)
 * @method void copyrightBlock(array|string $messages, int $padding = 2)
 * @method void headingBlock(array|string $messages, int $padding = 2)
 * @method void successBlock(array|string $messages, int $padding = 2)
 * @method void errorBlock(array|string $messages, int $padding = 2)
 * @method void warningBlock(array|string $messages, int $padding = 2)
 *
 * @since  10.0
 */
final class Output
{
	public const MAX_LINE_LENGTH = 120;

	/**
	 * Terminal information
	 *
	 * @var   Terminal
	 * @since 10.0
	 */
	private $terminal;

	/**
	 * Color styles
	 *
	 * @var   array<Color>
	 * @since 10.0
	 */
	private $styles = [];

	/**
	 * Stream resource to the standard error output.
	 *
	 * @var   resource
	 * @since 10.0
	 */
	private $stderr;

	/**
	 * Screen width.
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $width = 80;

	/**
	 * Constructor.
	 *
	 * @since   10.0
	 */
	public function __construct()
	{
		$this->terminal = new Terminal();
		$this->width    = $this->terminal->getColumns();
		$this->stderr   = defined('STDERR') ? \STDERR : (@fopen('php://stderr', 'w') ?: fopen('php://output', 'w'));
		$this->initStyles();
	}

	/**
	 * Destructor.
	 *
	 * @since   10.0
	 */
	public function __destruct()
	{
		try
		{
			@fclose($this->stderr);
		}
		catch (\Throwable $e)
		{
			// Discard, in case we accidentally try to close the resource pointed to by the STDERR constant.
		}
	}

	/**
	 * Write a message, or list of messages, to the standard output.
	 *
	 * @param   array|string  $messages  The messages to output
	 * @param   bool          $newLine   Add a new line character after each message?
	 * @param   string        $type      The color style to use.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function write($messages, bool $newLine = false, string $type = 'none'): void
	{
		$messages = is_iterable($messages) ? $messages : [$messages];
		$color    = $this->terminal->hasColor() ? $this->styles[$type] ?? $this->styles['none'] : $this->styles['none'];

		foreach ($messages as $message)
		{
			echo $color($message) . ($newLine ? \PHP_EOL : '');
		}
	}

	/**
	 * Write a message, or list of messages, to the standard output, with new lines at the end of each message.
	 *
	 * @param   array|string  $messages  The messages to output
	 * @param   string        $type      The color style to use.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function writeln($messages, string $type = 'none'): void
	{
		$this->write($messages, true, $type);
	}

	/**
	 * Write a message, or list of messages, to the standard error.
	 *
	 * @param   array|string  $messages  The messages to standard error.
	 * @param   bool          $newLine   Add a new line character after each message?
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function stderrWrite($messages, bool $newLine = false)
	{
		$messages = is_iterable($messages) ? $messages : [$messages];
		$stdErr   = $this->terminal->hasColor() ? $this->styles['stderr'] : $this->styles['none'];

		foreach ($messages as $message)
		{
			fputs($this->stderr, $stdErr($message) . ($newLine ? \PHP_EOL : ''));
		}
	}

	/**
	 * Write a message, or list of messages, to the standard error, with new lines at the end of each message.
	 *
	 * @param   array|string  $messages  The messages to output
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function stderrWriteln($messages)
	{
		$this->stderrWrite($messages, true);
	}

	/**
	 * Outputs one or more new line characters to the standard output.
	 *
	 * @param   int  $count
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function newLine(int $count = 1): void
	{
		$this->write(str_repeat(\PHP_EOL, $count));
	}

	/**
	 * Creates a block, for using styles with a background color
	 *
	 * @param   string|array  $messages  The messages to print in the block.
	 * @param   string        $title     Block title.
	 * @param   int           $padding   Amount of padding around the block, in characters.
	 *
	 * @return  string[]
	 * @since   10.0
	 */
	public function makeBlock($messages, string $title, int $padding = 2): array
	{
		$titleStyle = $this->terminal->hasColor() ? new Color('', '', ['bold', 'italic']) : $this->styles['none'];
		$messages   = is_iterable($messages) ? $messages : [$messages];
		$padChars   = str_repeat(' ', $padding);
		$maxWidth   = min($this->width - 2 * $padding, self::MAX_LINE_LENGTH);
		$toPrint    = [
			$padChars . $titleStyle(StringHelper::str_pad($title, $maxWidth)) . $padChars,
			$padChars . str_repeat(' ', $maxWidth) . $padChars,
		];

		foreach ($messages as $message)
		{
			$uncolored = $this->uncolor($message);

			if (StringHelper::strlen($uncolored) <= $maxWidth)
			{
				$colorWidth = StringHelper::strlen($message) - StringHelper::strlen($uncolored);
				$toPrint[]  = $padChars . StringHelper::str_pad($message, $maxWidth + $colorWidth) . $padChars;

				continue;
			}

			$toPrint = array_merge(
				$toPrint,
				array_map(
					function (string $line) use ($padChars, $maxWidth): string {
						$uncolored  = $this->uncolor($line);
						$colorWidth = StringHelper::strlen($line) - StringHelper::strlen($uncolored);

						return $padChars . StringHelper::str_pad($line, $maxWidth + $colorWidth) . $padChars;
					},
					explode("\n", StringHelper::wordwrap($message, $maxWidth, "\n", true))
				)
			);
		}

		if ($padding > 0)
		{
			$emptyLine = $padChars . str_repeat(' ', $maxWidth) . $padChars;

			for ($i = 0; $i < intdiv($padding, 2); $i++)
			{
				array_unshift($toPrint, $emptyLine);
				$toPrint[] = $emptyLine;
			}
		}

		return $toPrint;
	}

	/**
	 * Retrieves a style given its name.
	 *
	 * @param   string  $name  The name of the style to retrieve.
	 *
	 * @return  Color
	 * @since   10.0
	 */
	public function getStyle(string $name): Color
	{
		return $this->styles[$name] ?? $this->styles['none'];
	}

	public function columnar(array $items, ?Color $style1 = null, ?Color $style2 = null)
	{
		$col1   = array_reduce(
			array_keys($items),
			function (int $carry, string $string): int {
				return max($carry, StringHelper::strlen($string));
			},
			0
		);
		$col2   = min($this->terminal->getColumns(), self::MAX_LINE_LENGTH) - 4 - $col1;
		$style1 = $style1 ?? $this->styles['title'];
		$style2 = $style2 ?? $this->styles['none'];

		foreach ($items as $contentLeft => $contentRight)
		{
			$this->write('  ' . $style1(StringHelper::str_pad($contentLeft, $col1)) . '  ');

			if ($col2 < 20)
			{
				$this->newLine();
				$this->writeln('    ' . $contentRight);

				continue;
			}

			$chunks = explode("\n", StringHelper::wordwrap($contentRight, $col2, "\n", false));

			$this->writeln($style2($chunks[0]));

			for ($i = 1; $i < count($chunks); $i++)
			{
				$this->writeln(
					str_repeat(' ', $col1 + 4) . $style2($chunks[$i])
				);
			}
		}

	}

	/**
	 * Removes the color formatting from a string.
	 *
	 * @param   string  $string  The string to process.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function uncolor(string $string): string
	{
		return preg_replace("/\033\[[^m]*m/", '', $string ?? '');
	}


	/**
	 * Magic method for handling dynamic method calls.
	 *
	 * @param   string  $name       The name of the method being called.
	 * @param   array   $arguments  The arguments passed to the call.
	 *
	 * @return  void
	 * @throws  \BadMethodCallException If the method name is not recognized.
	 * @since   10.0
	 */
	public function __call($name, $arguments)
	{
		if (count($arguments) && substr($name, -5) === 'Block' && array_key_exists(substr($name, 0, -5), $this->styles))
		{
			$type = substr($name, 0, -5);
			$this->writeln('');
			$this->writeln(
				$this->makeBlock($arguments[0], strtoupper($type), $arguments[1] ?? 2),
				$type
			);
			$this->writeln('');

			return;
		}

		if (count($arguments) && array_key_exists($name, $this->styles))
		{
			$this->writeln($arguments[0], $name);

			return;
		}

		throw new \BadMethodCallException(sprintf('No such method %s::%s()', __CLASS__, $name));
	}

	/**
	 * Initialise the built-in color styles.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function initStyles(): void
	{
		$styles = [
			'none'      => ['', '', []],
			'title'     => ['bright-white', '', ['bold']],
			'copyright' => ['gray', '', []],
			'heading'   => ['green', '', ['bold', 'underscore']],
			'command'   => ['cyan', '', ['italic']],
			'stderr'    => ['red', '', []],
			'success'   => ['black', 'bright-green', []],
			'error'     => ['bright-white', 'red', []],
			'warning'   => ['black', 'yellow', []],
		];

		foreach ($styles as $name => $attribs)
		{
			$this->styles[$name] = new Color($attribs[0], $attribs[1], $attribs[2]);
		}
	}
}