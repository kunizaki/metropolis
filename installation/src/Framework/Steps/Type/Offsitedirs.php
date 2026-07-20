<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Steps\Type;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Configuration\Folders;
use Akeeba\BRS\Framework\Steps\StepItem;

/**
 * Step type handler for the `offsitedirs` step.
 *
 * @since  10.0
 */
final class Offsitedirs extends AbstractStepType
{
	/**
	 * @inheritDoc
	 */
	public function getStepData(): array
	{
		return array_map(
			function (Folders $folder) {
				return new StepItem($this->getContainer(), 'offsitedirs', $folder->virtual);
			},
			/**
			 * Remove special off-site folders which have been automatically added to the restoration:
			 *
			 * - `JPATH_PUBLIC` handled by the `publicfolder` view in the `joomla` platform add-on.
			 */
			array_filter(
				$this->getContainer()->get('configuration')->folders,
				function (Folders $folder) {
					return $folder->virtual != 'external_files/JPATH_PUBLIC';
				}
			)
		);
	}
}