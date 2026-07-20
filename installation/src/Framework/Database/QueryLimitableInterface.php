<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

defined('_AKEEBA') or die();

/**
 * Database Query Limitable Interface.
 * Adds bind/unbind methods as well as a getBounded() method
 * to retrieve the stored bounded variables on demand prior to
 * query execution.
 */
interface QueryLimitableInterface
{
	/**
	 * Modify a query to apply limits.
	 *
	 * This method is used automatically by the __toString() method if it detects that the query implements the
	 * QueryLimitableInterface.
	 *
	 * @param   string   $query   The query in string format
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  string
	 *
	 */
	public function processLimit(string $query, int $limit, int $offset = 0): string;

	/**
	 * Sets the offset and limit for the result set, if the database driver supports it.
	 *
	 * Usage:
	 * $query->setLimit(100, 0); (retrieve 100 rows, starting at first record)
	 * $query->setLimit(50, 50); (retrieve 50 rows, starting at 50th record)
	 *
	 * @param   integer  $limit   The limit for the result set
	 * @param   integer  $offset  The offset for the result set
	 *
	 * @return  AbstractQuery  Returns this object to allow chaining.
	 *
	 */
	public function setLimit(int $limit = 0, int $offset = 0): AbstractQuery;

}