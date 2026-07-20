<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Document;

defined('_AKEEBA') or die();

interface ScriptAwareDocumentInterface
{
	/**
	 * Adds a script, automatically finding the most appropriate file.
	 *
	 * The rules for finding the most appropriate file are:
	 *
	 * * Files in installation/platform/media have priority over those in installation/media
	 * * When AKEEBA_DEBUG is unset or false non-minified files have priority over minified files
	 * * When AKEEBA_DEBUG is set and true minified files have priority over non-minified files
	 *
	 * @param   string  $mediaRelative  The file name, e.g. `foo.js`
	 * @param   bool    $defer          Should this be loaded deferred?
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function addMediaScript(string $mediaRelative, bool $defer = true): DocumentInterface;

	/**
	 * Adds an external script to the page
	 *
	 * @param   string  $url    The URL of the script file
	 * @param   bool    $defer  Should this be loaded deferred?
	 *
	 * @return  DocumentInterface  Self, for chaining.
	 * @since   10.0
	 */
	public function addScript(string $url, bool $defer = true): DocumentInterface;

	/**
	 * Adds an inline script to the page's header
	 *
	 * @param   string  $content  The contents of the script (without the script tag)
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function addScriptDeclaration(string $content): DocumentInterface;

	/**
	 * Add script options.
	 *
	 * Script options will be encoded into an embedded JSON document in the HTML output.
	 *
	 * @param   string  $key        The script options key.
	 * @param   array   $options    The options to add.
	 * @param   bool    $overwrite  Set to true to replace existing script options.
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function addScriptOptions(string $key, array $options, bool $overwrite = false): DocumentInterface;

	/**
	 * Add a language key to be accessible by JavaScript.
	 *
	 * @param   string  $languageKey
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function addScriptLanguage(string $languageKey): DocumentInterface;

	/**
	 * Return the script definitions.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getScripts(): array;

	/**
	 * Return the inline script definitions.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getScriptDeclarations(): array;

	/**
	 * Return the script options.
	 *
	 * @param   string|null  $key  Which key to retrieve. NULL to get all defined script options.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getScriptOptions(?string $key = null): array;
}