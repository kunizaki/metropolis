<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Query;

use Akeeba\BRS\Framework\Database\AbstractQuery;
use Akeeba\BRS\Framework\Database\QueryLimitableInterface;

defined('_AKEEBA') or die();

/**
 * Common query class for MySQL database drivers.
 *
 * @since  10.0
 */
abstract class AbstractMySQLQuery extends AbstractQuery implements QueryLimitableInterface
{
	/**
	 * The offset for the result set.
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $offset;

	/**
	 * The limit for the result set.
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $limit;

	/** @inheritdoc */
	public function processLimit(string $query, int $limit, int $offset = 0): string
	{
		if ($limit > 0 || $offset > 0)
		{
			$query .= ' LIMIT ' . $offset . ', ' . $limit;
		}

		return $query;
	}

	/** @inheritdoc */
	public function concatenate(array $values, ?string $separator = null): string
	{
		if ($separator)
		{
			$concat_string = 'CONCAT_WS(' . $this->quote($separator);

			foreach ($values as $value)
			{
				$concat_string .= ', ' . $value;
			}

			return $concat_string . ')';
		}

		return 'CONCAT(' . implode(',', $values) . ')';
	}

	/** @inheritdoc  */
	public function setLimit(int $limit = 0, int $offset = 0): AbstractQuery
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}
}