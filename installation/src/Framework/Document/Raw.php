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

final class Raw extends AbstractDocument
{
	/**
	 * @inheritDoc
	 */
	public function render(): void
	{
		echo $this->getBuffer();
	}
}