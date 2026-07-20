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

interface StyleAwareDocumentInterface
{
	/**
	 * Adds a stylesheet, automatically finding the most appropriate file.
	 *
	 * The rules for finding the most appropriate file are:
	 *
	 * * Files in installation/platform/media have priority over those in installation/media
	 * * When AKEEBA_DEBUG is unset or false non-minified files have priority over minified files
	 * * When AKEEBA_DEBUG is set and true minified files have priority over non-minified files
	 *
	 * @param   string       $mediaRelative  The file name, e.g. `foo.js`
	 * @param   string|null  $media          The media target of the stylesheet file
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function addMediaStyle(string $mediaRelative, ?string $media = null): DocumentInterface;

	/**
	 * Adds a stylesheet to the page
	 *
	 * @param   string       $url    The URL of the stylesheet file
	 * @param   string|null  $media  The media target of the stylesheet file
	 *
	 * @return  DocumentInterface  Self, for chaining
	 * @since   10.0
	 */
	public function addStyle(string $url, ?string $media = null): DocumentInterface;

	/**
	 * Adds an inline stylesheet to the page
	 *
	 * @param   string       $content  The style content
	 * @param   string|null  $media    The media target of the stylesheet file
	 *
	 * @return  DocumentInterface  Self, for chaining
	 * @since   10.0
	 */
	public function addStyleDeclaration(string $content, ?string $media = null): DocumentInterface;

	/**
	 * Get the defined stylesheets.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getStyles(): array;

	/**
	 * Get the defined inline styles.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getStyleDeclarations(): array;
}