<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\View\Dbrestore;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Mvc\View;
use Throwable;

/**
 * The view controller for rendering specific error messages during the restoration.
 *
 * @since  10.0
 */
class Html extends View
{
	/**
	 * The exception we are displaying an error message for
	 *
	 * @var   Throwable
	 * @since 10.0
	 */
	public $exception;
}