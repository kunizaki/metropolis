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

class Dbname extends Dberror
{
	public function __construct($message = "", $code = 500, ?Throwable $previous = null)
	{
		$message = $message ?: 'Cannot connect to the database: the database name is incorrect or the database user does not have access to this database.';

		parent::__construct($message, $code, $previous);
	}
}