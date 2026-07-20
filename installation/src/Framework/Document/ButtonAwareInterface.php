<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Document;

use Akeeba\BRS\Framework\Template\Button;

defined('_AKEEBA') or die();

/**
 * Interface to documents which know about the existence of buttons.
 *
 * @since  10.0
 */
interface ButtonAwareInterface
{
	/**
	 * Adds a button to the end of the buttons list
	 *
	 * @param   string  $message  The translation string of the button message.
	 * @param   string  $type     Button classes, space separated.
	 * @param   string  $icon     Icon classes, space separated.
	 * @param   string  $id       The CSS ID.
	 *
	 * @since   10.0
	 * @since   10.0
	 */
	public function appendButton(
		string $message, string $type = 'btn-primary', string $icon = '', string $id = ''
	): void;

	/**
	 * Adds a button in the beginning of the buttons list.
	 *
	 * Yes, I know there is no such English word as "prepend", but "prefix" is even more confusing…
	 *
	 * @param   string  $message  The translation string of the button message.
	 * @param   string  $type     Button classes, space separated.
	 * @param   string  $icon     Icon classes, space separated.
	 * @param   string  $id       The CSS ID.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function prependButton(
		string $message, string $type = 'primary', string $icon = '', string $id = ''
	): void;

	/**
	 * Clear button definitions
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function clearButtons(): void;

	/**
	 * Return all button definitions
	 *
	 * @return  Button[]
	 * @since   10.0
	 */
	public function getButtons(): array;
}