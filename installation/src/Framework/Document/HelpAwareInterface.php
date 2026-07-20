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

/**
 * Interface to a document that knows about providing help links to the user
 *
 * @since  10.0
 */
interface HelpAwareInterface
{
	/**
	 * Set the help URL for this document.
	 *
	 * @param   string|null  $url  The URL to the help page.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setHelpURL(?string $url): void;

	/**
	 * Get the help URL for this document.
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getHelpURL(): ?string;

	/**
	 * Set the video tutorial URL for this document.
	 *
	 * @param   string|null  $url  The URL to the video tutorial.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setVideoURL(?string $url): void;

	/**
	 * Get the video tutorial URL for this document.
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getVideoURL(): ?string;
}