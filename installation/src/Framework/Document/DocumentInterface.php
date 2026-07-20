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
 * An interface to a document object.
 *
 * @since  10.0
 */
interface DocumentInterface
{
	/**
	 * Sets the response buffer.
	 *
	 * @param   string  $buffer
	 *
	 * @return  static  Self, for chaining.
	 * @since   10.0
	 */
	public function setBuffer(string $buffer): DocumentInterface;

	/**
	 * Returns the contents of the buffer
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getBuffer(): string;

	/**
	 * Renders the document.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function render(): void;


}