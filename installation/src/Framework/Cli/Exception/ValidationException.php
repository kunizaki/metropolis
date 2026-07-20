<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli\Exception;

defined('_AKEEBA') or die();

/**
 * Runtime exception thrown on validation error.
 *
 * @since  10.0
 */
class ValidationException extends \RuntimeException
{
}