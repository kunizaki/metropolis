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

final class Html extends AbstractDocument
	implements ButtonAwareInterface, ScriptAwareDocumentInterface, StyleAwareDocumentInterface, HelpAwareInterface
{
	use ButtonAwareTrait;
	use MediaFolderTrait;
	use ScriptAwareDocumentTrait;
	use StyleAwareDocumentTrait;
	use HelpAwareTrait;

	/** @inheritdoc  */
	public function render(): void
	{
		include $this->getContainer()->get('paths')->get('themes')
		        . '/'
		        . $this->getContainer()->get('application')->getTemplate()
		        . '/index.php';
	}
}