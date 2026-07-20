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
 * Query class for the PostgreSQL database driver.
 *
 * @since  10.0
 */
final class Postgresql extends AbstractQuery implements QueryLimitableInterface
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
		if ($limit > 0)
		{
			$query .= ' LIMIT ' . $limit;
		}

		if ($offset > 0)
		{
			$query .= ' OFFSET ' . $offset;
		}

		return $query;
	}

	/** @inheritdoc */
	public function concatenate(array $values, ?string $separator = null): string
	{
		if ($separator)
		{
			return implode(' || ' . $this->quote($separator) . ' || ', $values);
		}

		return implode(' || ', $values);
	}

	/** @inheritdoc  */
	public function setLimit(int $limit = 0, int $offset = 0): AbstractQuery
	{
		$this->limit  = (int) $limit;
		$this->offset = (int) $offset;

		return $this;
	}

	/** @inheritdoc */
	public function currentTimestamp(): string
	{
		return 'CURRENT_TIMESTAMP';
	}

	/** @inheritdoc */
	public function year(string $date): string
	{
		return 'EXTRACT(YEAR FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function month(string $date): string
	{
		return 'EXTRACT(MONTH FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function day(string $date): string
	{
		return 'EXTRACT(DAY FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function hour(string $date): string
	{
		return 'EXTRACT(HOUR FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function minute(string $date): string
	{
		return 'EXTRACT(MINUTE FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function second(string $date): string
	{
		return 'EXTRACT(SECOND FROM ' . $date . ')';
	}

	/** @inheritdoc */
	public function charLength(string $field, ?string $operator = null, ?string $condition = null): string
	{
		return 'CHAR_LENGTH(' . $field . ')' .
		       (isset($operator) && isset($condition) ? ' ' . $operator . ' ' . $condition : '');
	}

	/** @inheritdoc */
	public function castAsChar(string $value): string
	{
		return $value . '::text';
	}
}
