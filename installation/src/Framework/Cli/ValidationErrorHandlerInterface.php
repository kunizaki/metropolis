<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli;

use Akeeba\BRS\Framework\Cli\Exception\ValidationException;

defined('_AKEEBA') or die();

/**
 * Interface to an invokable class which handles validation errors.
 *
 * @since  10.0
 * @see    InstallationQueue::validate
 */
interface ValidationErrorHandlerInterface
{
	public function __invoke(InstallationStepInterface $item, ValidationException $e);
}