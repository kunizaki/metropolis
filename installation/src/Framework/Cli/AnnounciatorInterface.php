<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli;

defined('_AKEEBA') or die();

/**
 * Interface to an invokable class which announces installation steps.
 *
 * @since  10.0
 * @see    InstallationQueue::execute
 */
interface AnnounciatorInterface
{
	public function __invoke(InstallationStepInterface $item);
}