<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\Model;

defined('_AKEEBA') or die();

use Akeeba\BRS\Model\AbstractConfiguration;
use Akeeba\BRS\Platform\Parser\AbstractParser;
use Psr\Container\ContainerInterface;

/**
 * Joomla! configuration handling model.
 *
 * @since  10.0
 */
class Configuration extends AbstractConfiguration
{
	/**
	 * The detected Joomla! version.
	 *
	 * @var   mixed|string
	 * @since 10.0
	 */
	protected $joomlaVersion = '';

	/** @inheritDoc */
	public function __construct(?ContainerInterface $container = null, array $config = [])
	{
		$this->configFilename = 'configuration.php';

		parent::__construct($container, $config);

		// Get the Joomla! version from the configuration or the session
		$session  = $this->getContainer()->get('session');
		$jVersion = $session->get('jversion', '2.5.0');

		if (array_key_exists('jversion', $config))
		{
			$jVersion = $config['jversion'];
		}

		$this->joomlaVersion = $jVersion;

		// Load the configuration variables from the session or the default configuration shipped with BRS
		$this->configvars = (array) $session->get('cms.config') ?: [];

		// Initialise the configuration variables if they are not already in the session.
		if (empty($this->configvars))
		{
			$this->initConfigVars();
		}
	}

	/**
	 * Loads the configuration information from a PHP file
	 *
	 * The $useDirectInclude options controls how the configuration file will be parsed. When the option is true, the
	 * file is included directly. This is how Joomla! does it, but if the file contains executable code, or depends on
	 * constants set in a custom defines.php file this will cause the restoration script to die. When the option is
	 * false we use a safer method which analyses the file without loading it as executable code.
	 *
	 * @param   string  $file              The full path to the file
	 * @param   string  $className         The name of the configuration class
	 * @param   bool    $useDirectInclude  Should I include the .php file directly?
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function loadFromFile(string $file, string $className = 'JConfig', bool $useDirectInclude = false): array
	{
		if (!$useDirectInclude)
		{
			return $this->extractConfiguration($file);
		}

		$ret = [];

		include_once $file;

		if (class_exists($className))
		{
			foreach (get_class_vars($className) as $key => $value)
			{
				$ret[$key] = $value;
			}
		}

		return $ret;
	}

	/**
	 * Get the contents of the new `configuration.php` file.
	 *
	 * @param   string  $className  The name of the configuration class, by default it's JConfig
	 *
	 * @return  string  The contents of the `configuration.php` file
	 * @since   10.0
	 */
	public function getFileContents(string $className = 'JConfig'): string
	{
		$out = <<< PHP
<?php
/**
 * Joomla Global Configuration
 *
 * This file has been modified by the Akeeba Backup Restoration Script, when restoring or transferring your site.
 * 
 * This comment is removed whe you save the Global Configuration from Joomla's interface and/or when a third party
 * extension modifies your site's Global Configuration.
 * 
 * You can find the contents of the original file the Restoration Script read from your site in the 
 * configuration.bak.php file, located in the same directory as this file here. 
 */
class $className
{

PHP;

		// Sort the configuration values to give Yet Another Hint that this file is modified by BRS.
		ksort($this->configvars);

		foreach ($this->configvars as $name => $value)
		{
			// Object values must be converted to arrays – or get skipped if that's not possible.
			if (is_object($value))
			{
				try
				{
					$value = (array) $value;
				}
				catch (\Throwable $e)
				{
					continue;
				}
			}

			// Special handling of arrays
			if (is_array($value))
			{
				$pieces = [];

				foreach ($value as $key => $data)
				{
					// Array configuration values can only consist of scalars
					if (!is_scalar($data))
					{
						continue;
					}

					$data     = addcslashes($data, '\'\\');
					$pieces[] = "'" . $key . "' => '" . $data . "'";
				}

				if (empty($pieces))
				{
					continue;
				}

				$value = "array (\n" . implode(",\n", $pieces) . "\n)";
			}
			// Non-scalar, non-array values are ignored (they cannot be supported)
			elseif (!is_scalar($value))
			{
				continue;
			}
			// Scalar values
			else
			{
				// Log and temp paths in Windows systems will be forward-slash encoded
				if ($name === 'tmp_path' || $name === 'log_path')
				{
					$value = $this->TranslateWinPath($value);
				}

				/**
				 * Joomla! 4 renamed 'pdomysql' to 'mysql'. We still use 'pdomysql', so we need to translate.
				 *
				 * This is where we translate our BRS db driver name to Joomla's configuration name. The opposite
				 * takes place in extractConfiguration() in this class.
				 */
				if (
					$name === 'dbtype'
					&& $value === 'pdomysql'
				    && version_compare($this->joomlaVersion, '3.99999.99999', 'gt')
				)
				{
					$value = 'mysql';
				}

				$value = var_export($value, true);
			}

			$out .= "\tpublic $" . $name . " = " . $value . ";\n";
		}

		$out .= '}' . "\n";

		return $out;
	}

	/**
	 * Extracts the Joomla! Global Configuration from a configuration.php file without including the file.
	 *
	 * This works very well with most sites, as long as the configuration was not messed with by the user.
	 *
	 * @param   string  $filePath  The absolute path to the configuration.php file
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function extractConfiguration(string $filePath): array
	{
		$isJoomla4 = version_compare($this->joomlaVersion, '3.99999.99999', 'gt');

		$parser = AbstractParser::getParser();
		$ret    = $parser->parseFile($filePath, 'JConfig');

		/**
		 * Joomla! 4 renamed 'pdomysql' to 'mysql'. Internally we still use 'pdomysql' so I need to translate.
		 *
		 * This is where we translate Joomla's configuration to our BRS db driver name. The opposite takes
		 * place in getFileContents() in this class.
		 */
		if ($isJoomla4 && isset($ret['dbtype']) && $ret['dbtype'] = 'mysql')
		{
			$ret['dbtype'] = 'pdomysql';
		}

		return $ret;
	}

	/**
	 * Initialise the configuration variables for the currently detected Joomla! version.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function initConfigVars(): void
	{
		// Get default configuration based on the Joomla! version. The default is Joomla! 5.x.
		$v = '50';

		// Check for Joomla! 4.x
		if (version_compare($this->joomlaVersion, '3.999999.999999', 'gt')
		    && version_compare(
			    $this->joomlaVersion, '4.999999.999999', 'lt'
		    ))
		{
			$v = '40';
		}
		// Check for Joomla! 3.0 to 3.10
		elseif (version_compare($this->joomlaVersion, '2.9999.9999', 'gt') && version_compare($this->joomlaVersion, '3.9999.9999', 'lt'))
		{
			$v = '30';
		}
		// Check for Joomla! 2.5 or earlier (covers 1.6, 1.7, 2.5)
		elseif (version_compare($this->joomlaVersion, '2.5.0', 'ge') && version_compare($this->joomlaVersion, '3.0.0', 'lt'))
		{
			$v = '25';
		}
		// Check for Joomla! 1.5.x
		elseif (version_compare($this->joomlaVersion, '1.4.99999', 'ge') && version_compare($this->joomlaVersion, '1.6.0', 'lt'))
		{
			$v = '15';
		}
		// Check for Joomla! 1.0.x
		elseif (version_compare($this->joomlaVersion, '1.4.99999', 'lt'))
		{
			die('Woah! Joomla! 1.0 is way too old for this restoration script. You need to use JoomlaPack - the Akeeba Backup predecessor the development of which we discontinued back in 2009.');
		}

		$paths            = $this->getContainer()->get('paths');
		$className        = 'J' . $v . 'Config';
		$filename         = $paths->get('platform.assets') . '/jconfig/j' . $v . '.php';
		$this->configvars = $this->loadFromFile($filename, $className, true);

		if (!empty($this->configvars))
		{
			$this->saveToSession();
		}
	}

	/** @inheritDoc */
	public function __toString()
	{
		return $this->getFileContents();
	}
}