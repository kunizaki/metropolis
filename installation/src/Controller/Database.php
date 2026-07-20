<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Controller;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\Controller;

class Database extends Controller
{
	public function onBeforeMain(): bool
	{
		$this->getThisView()->key = $this->getContainer()->get('input')->getRaw('substep', '') ?: 'site.sql';

		return true;
	}
}