<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Restore\Exception;

defined('_AKEEBA') or die();

use Throwable;

class Dbuser extends Dberror
{
	public function __construct($message = "", $code = 500, ?Throwable $previous = null)
	{
		$message = $message ?: 'Cannot connect to the database: one or more of the database host name, user name and password you have provided is incorrect.';

		parent::__construct($message, $code, $previous);
	}
}