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
 * Trait implementing the ButtonAwareInterface
 *
 * @since  10.0
 */
trait ButtonAwareTrait
{
	/**
	 * The buttons defined in this document.
	 *
	 * @var   array<Button>
	 * @since 10.0
	 */
	private $buttons = [];

	/** @inheritdoc */
	public function appendButton(
		string $message, string $type = 'btn-primary', string $icon = '', string $id = ''
	): void
	{
		$this->buttons[] = new Button($type, $id, $icon, $message);
	}

	/** @inheritdoc */
	public function prependButton(
		string $message, string $type = 'primary', string $icon = '', string $id = ''
	): void
	{
		array_unshift($this->buttons, new Button($type, $id, $icon, $message));
	}

	/** @inheritdoc */
	public function clearButtons(): void
	{
		$this->buttons = [];
	}

	/** @inheritdoc */
	public function getButtons(): array
	{
		return $this->buttons;
	}
}