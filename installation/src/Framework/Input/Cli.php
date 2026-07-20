<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Input;

defined('_AKEEBA') or die();

/**
 * CLI input handling.
 *
 * @since  10.0
 */
final class Cli
{
	/**
	 * Filter object to use.
	 *
	 * @var    InputFilter
	 * @since  10.0
	 */
	protected $filter;

	/**
	 * Input data.
	 *
	 * @var    array
	 * @since  10.0
	 */
	protected $data = [];

	/**
	 * The executable that was called to run the CLI script.
	 *
	 * @var    string
	 */
	private $executable;

	/**
	 * The additional arguments passed to the script that are not associated
	 * with a specific argument name.
	 *
	 * @var    array
	 */
	private $arguments = [];

	/**
	 * Constructor.
	 *
	 * @param   InputFilter|null  $filter  The filter instance to use. NULL for default InputFilter instance.
	 *
	 * @since   10.0
	 */
	public function __construct(?InputFilter $filter = null)
	{
		$this->filter = $filter ?? new InputFilter;
		$this->data   = $this->parseArguments();
	}

	/**
	 * Returns the executable used in the command line.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getExecutable(): string
	{
		return $this->executable;
	}

	/**
	 * Get all arguments passed to the script.
	 *
	 * Note that `example.php foo --bar baz` has two arguments: `foo`, and `baz`.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getArguments(): array
	{
		return $this->arguments;
	}

	/**
	 * Did the user provide a specific argument?
	 *
	 * @param   string  $argument  The argument to check. Case-sensitive.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasArgument(string $argument): bool
	{
		return in_array($argument, $this->arguments);
	}

	/**
	 * Retrieve the first argument.
	 *
	 * Typically used to locate the command to be executed.
	 *
	 * @param   mixed  $default  The default value to use if no argument has been provided.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function getFirstArgument($default = null)
	{
		if (empty($this->arguments))
		{
			return $default;
		}

		return reset($this->arguments);
	}

	/**
	 * Get the value of a named option.
	 *
	 * @param   string  $name     The name of the option whose value shall be retrieved
	 * @param   mixed   $default  The default value, if the option is not set
	 * @param   string  $filter   The type of filter; defaults to `raw`
	 *
	 * @return  mixed
	 * @since   10.0
	 * @see     InputFilter::clean()
	 */
	public function getOption(string $name, $default = null, string $filter = 'raw')
	{
		if ($filter === 'bool')
		{
			return $this->getBooleanOption($name, (bool) ($default ?? false));
		}

		return $this->hasOption($name) ? $this->filter->clean($this->data[$name], $filter) : $default;
	}

	/**
	 * Does an option exist in the CLI input?
	 *
	 * This method automatically checks for the presence of a short option, if the long name does not exist.
	 *
	 * @param   string  $name  The (long) name of the option to check.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasOption(string $name): bool
	{
		// Otherwise, check for the presence of the short option.
		return array_key_exists($name, $this->data);
	}

	/**
	 * Get a boolean option.
	 *
	 * Boolean options are handled in a special way. Given the name `foo`, the presence of `--foo` sets the value to
	 * true, whereas the presence of `--no-foo` (note the prefix `no-`) sets it to false. If neither is present, the
	 * value returned is `$default` which itself defaults to false.
	 *
	 * @param   string  $name     The name of the boolean option to retrieve.
	 * @param   bool    $default  The value to return in absence of user input. Default: false.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function getBooleanOption(string $name, bool $default = false): bool
	{
		if (substr($name, 0, '3') === 'no-')
		{
			$name = substr($name, 3);
		}

		$negativeName = 'no-' . $name;

		if ($this->hasOption($negativeName))
		{
			return false;
		}

		if ($this->hasOption($name))
		{
			return true;
		}

		return $default;
	}

	/**
	 * Parse the command line arguments, returning an array of options.
	 *
	 * At the same time, the executable and arguments arrays are populated.
	 *
	 * @return  array  The input data array
	 * @since   10.0
	 */
	private function parseArguments(): array
	{
		$argv             = $_SERVER['argv'];
		$this->executable = array_shift($argv);
		$this->arguments  = [];
		$out              = [];

		for ($i = 0, $j = count($argv); $i < $j; $i++)
		{
			$arg = $argv[$i];

			// --foo --bar=baz
			if (substr($arg, 0, 2) === '--')
			{
				$eqPos = strpos($arg, '=');

				// --foo
				if ($eqPos === false)
				{
					$key = substr($arg, 2);

					// --foo value
					if ($i + 1 < $j && $argv[$i + 1][0] !== '-')
					{
						$value = $argv[$i + 1];
						$i++;
					}
					else
					{
						$value = $out[$key] ?? true;
					}

					$out[$key] = $value;
				}
				// --bar=baz
				else
				{
					$key       = substr($arg, 2, $eqPos - 2);
					$value     = substr($arg, $eqPos + 1);
					$out[$key] = $value;
				}
			}
			elseif (substr($arg, 0, 1) === '-')
			{
				// -k=value
				if (substr($arg, 2, 1) === '=')
				{
					$key       = substr($arg, 1, 1);
					$value     = substr($arg, 3);
					$out[$key] = $value;
				}
				else
				{
					// -abc
					$chars = str_split(substr($arg, 1));

					foreach ($chars as $char)
					{
						$key       = $char;
						$value     = $out[$key] ?? true;
						$out[$key] = $value;
					}

					// -a a-value
					if ((count($chars) === 1) && ($i + 1 < $j) && ($argv[$i + 1][0] !== '-'))
					{
						$out[$key] = $argv[$i + 1];
						$i++;
					}
				}
			}
			else
			{
				// Argument
				$this->arguments[] = $arg;
			}
		}

		return $out;
	}
}