<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Language;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * The text translation service.
 *
 * Loosely based on Joomla's language handling in that it uses INI files and offers similar translation methods.
 *
 * @since  10.0
 */
final class Language implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The parsed language strings
	 *
	 * @var   array<string>
	 * @since 10.0
	 */
	private $strings = [];

	/**
	 * Callables used to post-process parsed strings
	 *
	 * @var   array<callable>
	 * @since 10.0
	 */
	private $iniProcessCallbacks = [];

	/**
	 * The language which has been loaded last.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $loadedLanguage = 'en-GB';

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Adds a language string post-processing callback to the stack.
	 *
	 * @param   callable  $callable  The processing callback to add
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function addIniProcessCallback(callable $callable): void
	{
		$this->iniProcessCallbacks[] = $callable;
	}

	/**
	 * Load a specific language
	 *
	 * @param   string|null  $langCode  The language code to load
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function loadLanguage(?string $langCode = null): void
	{
		$langCode = $langCode ?: $this->detectLanguage();

		$this->loadedLanguage = $langCode;

		// Always load the default language first
		if ($langCode !== 'en-GB')
		{
			$this->loadLanguage('en-GB');
		}

		$paths  = $this->getContainer()->get('paths');
		$parser = $this->getContainer()->get('iniParser');

		// Parse the main and platform language file (if they exist)
		foreach (
			[
				$paths->get('language') . '/' . $langCode . '.ini',
				$paths->get('platform.language') . '/' . $langCode . '.ini',
			] as $filename
		)
		{
			if (!file_exists($filename))
			{
				continue;
			}

			$this->strings = array_merge($this->strings, $parser->parseFile($filename));
		}

		// Performs callback on loaded strings
		foreach ($this->iniProcessCallbacks as $callback)
		{
			$ret = call_user_func($callback, $filename, $this->strings);

			if ($ret === false)
			{
				return;
			}

			if (!is_array($ret))
			{
				continue;
			}

			$this->strings = $ret;
		}
	}

	/**
	 * Detect the most desirable language available from the user's requested languages.
	 *
	 * This checks the Accept-Language header sent by the browser, and the language files available on the server. It
	 * returns the base name (minus the .ini extension) of the language file which is available, and satisfies the most
	 * desirable language of the user. If no user-requested language is available, we fall back to English (Great
	 * Britain).
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function detectLanguage(): string
	{
		$input     = $this->getContainer()->get('input');
		$languages = $input->server->getRaw('HTTP_ACCEPT_LANGUAGE', null);

		if (empty($languages))
		{
			return 'en-GB';
		}

		/**
		 * The Accept-Language HTTP header is sent by the browser in this format:
		 * fr-ch;q=0.3, da, en-us;q=0.8, en;q=0.5, fr;q=0.3
		 *
		 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
		 */
		$languageList     = explode(",", str_replace(' ', '', $languages));
		$weighedLanguages = [];

		foreach ($languageList as $langDef)
		{
			$parts    = explode(';', trim($langDef), 2);
			$langCode = $parts[0];
			$quality  = $this->parseLangQuality($parts[1] ?? 'q=1.0');

			$weighedLanguages[$langCode] = $quality;
		}

		asort($weighedLanguages);

		$userLanguages = [];

		foreach (array_keys($weighedLanguages) as $langCode)
		{
			$parts = explode('-', trim($langCode), 2);

			if (strpos($parts[0], '_') === false)
			{
				$parts = explode('_', trim($langCode), 2);
			}

			$lang            = $parts[0];
			$location        = $parts[1] ?? null;
			$fullLang        = strtolower($lang) . (!empty($location) ? ('-' . $location) : '');
			$userLanguages[] = [$fullLang, $lang];
		}

		$paths = $this->getContainer()->get('paths');

		$baseName = rtrim($paths->get('language'), DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR;

		foreach ($userLanguages as $languageStruct)
		{
			// Search for exact language
			$langFilename = $baseName . $languageStruct[0] . '.ini';

			if (!file_exists($langFilename))
			{
				$langFilename = '';

				if (function_exists('glob'))
				{
					$allFiles = glob($baseName . $languageStruct[1] . '-*.ini');

					if (count($allFiles))
					{
						$langFilename = array_shift($allFiles);
					}
				}
			}

			if (!empty($langFilename) && file_exists($langFilename))
			{
				return basename($langFilename, '.ini');
			}
		}

		return 'en-GB';
	}

	/**
	 * Translates a language key
	 *
	 * @param   string  $key  The language key
	 *
	 * @return  string  Its natural language translation
	 * @since   10.0
	 */
	public function text(string $key): string
	{
		if (empty($this->strings))
		{
			self::loadLanguage('en-GB');
			self::loadLanguage();
		}

		$key = strtoupper($key);

		return array_key_exists($key, $this->strings) ? $this->strings[$key] : $key;
	}

	/**
	 * Backwards compatibility shim for text().
	 *
	 * @param   string  $key
	 *
	 * @deprecated 11.0
	 * @return  string
	 * @since      10.0
	 * @see        self::text()
	 *
	 */
	public function _(string $key): string
	{
		return $this->text($key);
	}

	/**
	 * Passes a string through sprintf.
	 *
	 * @param   string  $key   The language string whose translation will be used as a sprintf() format string.
	 * @param   array   $args  The arguments to sprintf().
	 *
	 * @return  string  The translated strings
	 * @since   10.0
	 */
	public function sprintf(string $key, ...$args): string
	{
		if (!count($args))
		{
			return $this->text($key);
		}

		return sprintf($this->text($key), ...$args);
	}

	/**
	 * The language code of the last loaded language.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getLangCode(): string
	{
		return $this->loadedLanguage;
	}

	/**
	 * Parse the language quality from an Accept-Language entry.
	 *
	 * The Accept-Language has entries like `en-GB`, or `en-GB;q=0.8`. The `q` parameter is the language quality. If
	 * it's missing it's considered to be 1.0. If it exists, it's returned as a float. Higher quality languages are
	 * preferred in our best fit language selection algorithm.
	 *
	 * @param   string  $qualityDef
	 *
	 * @return  float
	 * @since   10.0
	 * @link    https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Accept-Language
	 */
	private function parseLangQuality(string $qualityDef): float
	{
		if (strpos($qualityDef, '=') === false)
		{
			return 1.0;
		}

		$parts = explode('=', $qualityDef, 2);

		if (trim($parts[0]) !== 'q' || !isset($parts[1]) || !is_numeric($parts[1]))
		{
			return 1.0;
		}

		$q = floatval(trim($parts[1]));

		return $q >= 0 ? $q : 1.0;
	}
}