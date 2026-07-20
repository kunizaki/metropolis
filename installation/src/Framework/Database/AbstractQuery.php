<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

use RuntimeException;

defined('_AKEEBA') or die();

/**
 * Query Building Class.
 *
 * @method      string  q()   q($text, $escape = true)  Alias for quote method
 * @method      string  qn()  qs($name, $as = null)     Alias for quoteName method
 * @method      string  e()   e($text, $extra = false)   Alias for escape method
 *
 * @since   10.0
 */
abstract class AbstractQuery
{
	/**
	 * The database driver.
	 *
	 * @var    AbstractDriver
	 * @since  10.0
	 */
	protected $db = null;

	/**
	 * The SQL query (if a direct query string was provided).
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $sql = null;

	/**
	 * The query type.
	 *
	 * @var    string
	 * @since  10.0
	 */
	protected $type = '';

	/**
	 * The query element for a generic query (type = null).
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $element = null;

	/**
	 * The select element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $select = null;

	/**
	 * The delete element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $delete = null;

	/**
	 * The update element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $update = null;

	/**
	 * The insert element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $insert = null;

	/**
	 * The from element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $from = null;

	/**
	 * The join element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $join = null;

	/**
	 * The set element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $set = null;

	/**
	 * The where element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $where = null;

	/**
	 * The group by element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $group = null;

	/**
	 * The having element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $having = null;

	/**
	 * The column list for an INSERT statement.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $columns = null;

	/**
	 * The values list for an INSERT statement.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $values = null;

	/**
	 * The order element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $order = null;

	/**
	 * The auto increment insert field element.
	 *
	 * @var   object
	 * @since  10.0
	 */
	protected $autoIncrementField = null;

	/**
	 * The call element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $call = null;

	/**
	 * The exec element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $exec = null;

	/**
	 * The union element.
	 *
	 * @var    QueryElement
	 * @since  10.0
	 */
	protected $union = null;

	/**
	 * Magic method to provide method alias support for quote() and quoteName().
	 *
	 * @param   string  $method  The called method.
	 * @param   array   $args    The array of arguments passed to the method.
	 *
	 * @return  string|null  The aliased method's return value or null.
	 * @since   10.0
	 */
	public function __call($method, $args)
	{
		if (empty($args))
		{
			return null;
		}

		switch ($method)
		{
			case 'q':
				return $this->quote($args[0], isset($args[1]) ? $args[1] : true);
				break;

			case 'qn':
				return $this->quoteName($args[0], isset($args[1]) ? $args[1] : null);
				break;

			case 'e':
				return $this->escape($args[0], isset($args[1]) ? $args[1] : false);
				break;
		}

		return null;
	}

	/**
	 * Class constructor.
	 *
	 * @param   AbstractDriver|null  $db  The database driver.
	 *
	 * @since   10.0
	 */
	public function __construct(?AbstractDriver $db = null)
	{
		$this->db = $db;
	}

	/**
	 * Magic function to convert the query to a string.
	 *
	 * @return  string    The completed query.
	 * @since   10.0
	 */
	public function __toString()
	{
		$query = '';

		if ($this->sql)
		{
			return $this->sql;
		}

		switch ($this->type)
		{
			case 'element':
				$query .= (string) $this->element;
				break;

			case 'select':
				$query .= (string) $this->select;
				$query .= (string) $this->from;
				if ($this->join)
				{
					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				if ($this->group)
				{
					$query .= (string) $this->group;
				}

				if ($this->having)
				{
					$query .= (string) $this->having;
				}

				if ($this->order)
				{
					$query .= (string) $this->order;
				}

				break;

			case 'union':
				$query .= (string) $this->union;
				break;

			case 'delete':
				$query .= (string) $this->delete;
				$query .= (string) $this->from;

				if ($this->join)
				{
					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				break;

			case 'update':
				$query .= (string) $this->update;

				if ($this->join)
				{
					// Special case for joins
					foreach ($this->join as $join)
					{
						$query .= (string) $join;
					}
				}

				$query .= (string) $this->set;

				if ($this->where)
				{
					$query .= (string) $this->where;
				}

				break;

			case 'insert':
				$query .= (string) $this->insert;

				// Set method
				if ($this->set)
				{
					$query .= (string) $this->set;
				}
				// Columns-Values method
				elseif ($this->values)
				{
					if ($this->columns)
					{
						$query .= (string) $this->columns;
					}

					$elements = $this->values->getElements();
					if (!($elements[0] instanceof $this))
					{
						$query .= ' VALUES ';
					}

					$query .= (string) $this->values;
				}

				break;

			case 'call':
				$query .= (string) $this->call;
				break;

			case 'exec':
				$query .= (string) $this->exec;
				break;
		}

		if ($this instanceof QueryLimitableInterface)
		{
			$query = $this->processLimit($query, $this->limit ?? 0, $this->offset ?? 0);
		}

		return $query;
	}

	/**
	 * Magic function to get protected variable value
	 *
	 * @param   string  $name  The name of the variable.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function __get($name)
	{
		return $this->$name ?? null;
	}

	/**
	 * Add a single column, or array of columns to the CALL clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The call method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->call('a.*')->call('b.id');
	 * $query->call(array('a.*', 'b.id'));
	 *
	 * @param   string|array  $columns  A string or an array of field names.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function call($columns)
	{
		$this->type = 'call';

		if (is_null($this->call))
		{
			$this->call = new QueryElement('CALL', $columns);
		}
		else
		{
			$this->call->append($columns);
		}

		return $this;
	}

	/**
	 * Casts a value to a char.
	 *
	 * Ensure that the value is properly quoted before passing to the method.
	 *
	 * Usage:
	 * $query->select($query->castAsChar('a'));
	 *
	 * @param   string  $value  The value to cast as a char.
	 *
	 * @return  string  Returns the cast value.
	 * @since   10.0
	 */
	public function castAsChar(string $value): string
	{
		return $value;
	}

	/**
	 * Gets the number of characters in a string.
	 *
	 * Note, use 'length' to find the number of bytes in a string.
	 *
	 * Usage:
	 * $query->select($query->charLength('a'));
	 *
	 * @param   string       $field      A value.
	 * @param   string|null  $operator   Comparison operator between charLength integer value and $condition
	 * @param   string|null  $condition  Integer value to compare charLength with.
	 *
	 * @return  string  The required char length call.
	 * @since   10.0
	 */
	public function charLength(string $field, ?string $operator = null, ?string $condition = null): string
	{
		return 'CHAR_LENGTH(' . $field . ')' .
		       (isset($operator) && isset($condition) ? ' ' . $operator . ' ' . $condition : '');
	}

	/**
	 * Clear data from the query or a specific clause of the query.
	 *
	 * @param   string|null  $clause  Optionally, the name of the clause to clear, or nothing to clear the whole query.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function clear(?string $clause = null): AbstractQuery
	{
		$this->sql = null;

		switch ($clause)
		{
			case 'select':
				$this->select = null;
				$this->type   = null;
				break;

			case 'delete':
				$this->delete = null;
				$this->type   = null;
				break;

			case 'update':
				$this->update = null;
				$this->type   = null;
				break;

			case 'insert':
				$this->insert             = null;
				$this->type               = null;
				$this->autoIncrementField = null;
				break;

			case 'from':
				$this->from = null;
				break;

			case 'join':
				$this->join = null;
				break;

			case 'set':
				$this->set = null;
				break;

			case 'where':
				$this->where = null;
				break;

			case 'group':
				$this->group = null;
				break;

			case 'having':
				$this->having = null;
				break;

			case 'order':
				$this->order = null;
				break;

			case 'columns':
				$this->columns = null;
				break;

			case 'values':
				$this->values = null;
				break;

			case 'exec':
				$this->exec = null;
				$this->type = null;
				break;

			case 'call':
				$this->call = null;
				$this->type = null;
				break;

			case 'limit':
				$this->offset = 0;
				$this->limit  = 0;
				break;

			case 'union':
				$this->union = null;
				break;

			default:
				$this->type               = null;
				$this->select             = null;
				$this->delete             = null;
				$this->update             = null;
				$this->insert             = null;
				$this->from               = null;
				$this->join               = null;
				$this->set                = null;
				$this->where              = null;
				$this->group              = null;
				$this->having             = null;
				$this->order              = null;
				$this->columns            = null;
				$this->values             = null;
				$this->autoIncrementField = null;
				$this->exec               = null;
				$this->call               = null;
				$this->union              = null;
				$this->offset             = 0;
				$this->limit              = 0;
				break;
		}

		return $this;
	}

	/**
	 * Adds a column, or array of column names that would be used for an INSERT INTO statement.
	 *
	 * @param   string|array  $columns  A column name, or array of column names.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function columns($columns): AbstractQuery
	{
		if (is_null($this->columns))
		{
			$this->columns = new QueryElement('()', $columns);
		}
		else
		{
			$this->columns->append($columns);
		}

		return $this;
	}

	/**
	 * Concatenates an array of column names or values.
	 *
	 * Usage:
	 * $query->select($query->concatenate(array('a', 'b')));
	 *
	 * @param   array        $values     An array of values to concatenate.
	 * @param   string|null  $separator  As separator to place between each value.
	 *
	 * @return  string  The concatenated values.
	 * @since   10.0
	 */
	public function concatenate(array $values, ?string $separator = null): string
	{
		if ($separator)
		{
			return 'CONCATENATE(' . implode(' || ' . $this->quote($separator) . ' || ', $values) . ')';
		}

		return 'CONCATENATE(' . implode(' || ', $values) . ')';
	}

	/**
	 * Gets the current date and time.
	 *
	 * Usage:
	 * $query->where('published_up < '.$query->currentTimestamp());
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function currentTimestamp(): string
	{
		return 'CURRENT_TIMESTAMP()';
	}

	/**
	 * Returns a PHP date() function compliant date format for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the getDateFormat method directly.
	 *
	 * @return  string  The format string.
	 * @since   10.0
	 */
	public function dateFormat(): string
	{
		if (!($this->db instanceof AbstractDriver))
		{
			throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_INVALID_DB_OBJECT'));
		}

		return $this->db->getDateFormat();
	}

	/**
	 * Creates a formatted dump of the query for debugging purposes.
	 *
	 * Usage:
	 * echo $query->dump();
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function dump()
	{
		return '<pre class="databaseQuery">' . str_replace('#__', $this->db->getPrefix(), $this) . '</pre>';
	}

	/**
	 * Add a table name to the DELETE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->delete('#__a')->where('id = 1');
	 *
	 * @param   string|null  $table  The name of the table to delete from.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function delete(?string $table = null): AbstractQuery
	{
		$this->type   = 'delete';
		$this->delete = new QueryElement('DELETE', null);

		if (!empty($table))
		{
			$this->from($table);
		}

		return $this;
	}

	/**
	 * Method to escape a string for usage in an SQL statement.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the escape method directly.
	 *
	 * Note that 'e' is an alias for this method as it is in ADatabaseDriver.
	 *
	 * @param   string  $text   The string to be escaped.
	 * @param   bool    $extra  Optional parameter to provide extra escaping.
	 *
	 * @return  string  The escaped string.
	 * @throws  RuntimeException if the internal db property is not a valid object.
	 * @since   10.0
	 */
	public function escape(string $text, bool $extra = false): string
	{
		if (!($this->db instanceof AbstractDriver))
		{
			throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_INVALID_DB_OBJECT'));
		}

		return $this->db->escape($text, $extra);
	}

	/**
	 * Add a single column, or array of columns to the EXEC clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The exec method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->exec('a.*')->exec('b.id');
	 * $query->exec(array('a.*', 'b.id'));
	 *
	 * @param   string|array  $columns  A string or an array of field names.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function exec($columns): AbstractQuery
	{
		$this->type = 'exec';

		if (is_null($this->exec))
		{
			$this->exec = new QueryElement('EXEC', $columns);
		}
		else
		{
			$this->exec->append($columns);
		}

		return $this;
	}

	/**
	 * Add a table to the FROM clause of the query.
	 *
	 * Note that while an array of tables can be provided, it is recommended you use explicit joins.
	 *
	 * Usage:
	 * $query->select('*')->from('#__a');
	 *
	 * @param   string|AbstractQuery  $tables         A string or array of table names.
	 *                                                This can be am AbstractQuery object (or a child of it) when used
	 *                                                as a subquery in FROM clause along with a value for
	 *                                                $subQueryAlias.
	 * @param   string|null           $subQueryAlias  Alias used when $tables is an AbstractQuery.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function from($tables, ?string $subQueryAlias = null): AbstractQuery
	{
		if (is_null($this->from))
		{
			if ($tables instanceof $this)
			{
				if (is_null($subQueryAlias))
				{
					throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_NULL_SUBQUERY_ALIAS'));
				}

				$tables = '( ' . (string) $tables . ' ) AS ' . $this->quoteName($subQueryAlias);
			}

			$this->from = new QueryElement('FROM', $tables);
		}
		else
		{
			$this->from->append($tables);
		}

		return $this;
	}

	/**
	 * Used to get a string to extract year from date column.
	 *
	 * Usage:
	 * $query->select($query->year($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing year to be extracted.
	 *
	 * @return  string  Returns string to extract year from a date.
	 * @since   10.0
	 */
	public function year(string $date): string
	{
		return 'YEAR(' . $date . ')';
	}

	/**
	 * Used to get a string to extract month from date column.
	 *
	 * Usage:
	 * $query->select($query->month($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing month to be extracted.
	 *
	 * @return  string  Returns string to extract month from a date.
	 * @since   10.0
	 */
	public function month(string $date): string
	{
		return 'MONTH(' . $date . ')';
	}

	/**
	 * Used to get a string to extract day from date column.
	 *
	 * Usage:
	 * $query->select($query->day($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing day to be extracted.
	 *
	 * @return  string  Returns string to extract day from a date.
	 * @since   10.0
	 */
	public function day(string $date): string
	{
		return 'DAY(' . $date . ')';
	}

	/**
	 * Used to get a string to extract hour from date column.
	 *
	 * Usage:
	 * $query->select($query->hour($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing hour to be extracted.
	 *
	 * @return  string  Returns string to extract hour from a date.
	 * @since   10.0
	 */
	public function hour(string $date): string
	{
		return 'HOUR(' . $date . ')';
	}

	/**
	 * Used to get a string to extract minute from date column.
	 *
	 * Usage:
	 * $query->select($query->minute($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing minute to be extracted.
	 *
	 * @return  string  Returns string to extract minute from a date.
	 * @since   10.0
	 */
	public function minute(string $date): string
	{
		return 'MINUTE(' . $date . ')';
	}

	/**
	 * Used to get a string to extract seconds from date column.
	 *
	 * Usage:
	 * $query->select($query->second($query->quoteName('dateColumn')));
	 *
	 * @param   string  $date  Date column containing second to be extracted.
	 *
	 * @return  string  Returns string to extract second from a date.
	 * @since   10.0
	 */
	public function second(string $date): string
	{
		return 'SECOND(' . $date . ')';
	}

	/**
	 * Add a grouping column to the GROUP clause of the query.
	 *
	 * Usage:
	 * $query->group('id');
	 *
	 * @param   string|array  $columns  A string or array of ordering columns.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function group($columns): AbstractQuery
	{
		if (is_null($this->group))
		{
			$this->group = new QueryElement('GROUP BY', $columns);
		}
		else
		{
			$this->group->append($columns);
		}

		return $this;
	}

	/**
	 * A conditions to the HAVING clause of the query.
	 *
	 * Usage:
	 * $query->group('id')->having('COUNT(id) > 5');
	 *
	 * @param   string|array  $conditions  A string or array of columns.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function having($conditions, string $glue = 'AND'): AbstractQuery
	{
		if (is_null($this->having))
		{
			$glue         = strtoupper($glue);
			$this->having = new QueryElement('HAVING', $conditions, " $glue ");
		}
		else
		{
			$this->having->append($conditions);
		}

		return $this;
	}

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->innerJoin('b ON b.id = a.id')->innerJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function innerJoin(string $condition): AbstractQuery
	{
		$this->join('INNER', $condition);

		return $this;
	}

	/**
	 * Add a table name to the INSERT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->insert('#__a')->set('id = 1');
	 * $query->insert('#__a)->columns('id, title')->values('1,2')->values->('3,4');
	 * $query->insert('#__a)->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   string|array  $table           The name of the table to insert data into.
	 * @param   bool          $incrementField  The name of the field to auto increment.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function insert($table, bool $incrementField = false): AbstractQuery
	{
		$this->type               = 'insert';
		$this->insert             = new QueryElement('INSERT INTO', $table);
		$this->autoIncrementField = $incrementField;

		return $this;
	}

	/**
	 * Add a table name to the REPLACE INTO clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->insert('#__a')->set('id = 1');
	 * $query->insert('#__a)->columns('id, title')->values('1,2')->values->('3,4');
	 * $query->insert('#__a)->columns('id, title')->values(array('1,2', '3,4'));
	 *
	 * @param   string|array  $table           The name of the table to insert data into.
	 * @param   bool          $incrementField  The name of the field to auto increment.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function replace($table, bool $incrementField = false): AbstractQuery
	{
		$this->type               = 'insert';
		$this->insert             = new QueryElement('REPLACE INTO', $table);
		$this->autoIncrementField = $incrementField;

		return $this;
	}

	/**
	 * Add a JOIN clause to the query.
	 *
	 * Usage:
	 * $query->join('INNER', 'b ON b.id = a.id);
	 *
	 * @param   string  $type        The type of join. This string is prepended to the JOIN keyword.
	 * @param   string  $conditions  A string or array of conditions.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function join(string $type, string $conditions): AbstractQuery
	{
		if (is_null($this->join))
		{
			$this->join = [];
		}
		$this->join[] = new QueryElement(strtoupper($type) . ' JOIN', $conditions);

		return $this;
	}

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->leftJoin('b ON b.id = a.id')->leftJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function leftJoin(string $condition): AbstractQuery
	{
		$this->join('LEFT', $condition);

		return $this;
	}

	/**
	 * Get the length of a string in bytes.
	 *
	 * Note, use 'charLength' to find the number of characters in a string.
	 *
	 * Usage:
	 * query->where($query->length('a').' > 3');
	 *
	 * @param   string  $value  The string to measure.
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function length(string $value): int
	{
		return 'LENGTH(' . $value . ')';
	}

	/**
	 * Get the null or zero representation of a timestamp for the database driver.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the nullDate method directly.
	 *
	 * Usage:
	 * $query->where('modified_date <> '.$query->nullDate());
	 *
	 * @param   bool  $quoted  Optionally wraps the null date in database quotes (true by default).
	 *
	 * @return  string  Null or zero representation of a timestamp.
	 * @since   10.0
	 */
	public function nullDate(bool $quoted = true): string
	{
		if (!($this->db instanceof AbstractDriver))
		{
			throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_INVALID_DB_OBJECT'));
		}

		$result = $this->db->getNullDate();

		if ($quoted)
		{
			return $this->db->quote($result);
		}

		return $result;
	}

	/**
	 * Add a ordering column to the ORDER clause of the query.
	 *
	 * Usage:
	 * $query->order('foo')->order('bar');
	 * $query->order(array('foo','bar'));
	 *
	 * @param   string|array  $columns  A string or array of ordering columns.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function order($columns): AbstractQuery
	{
		if (is_null($this->order))
		{
			$this->order = new QueryElement('ORDER BY', $columns);
		}
		else
		{
			$this->order->append($columns);
		}

		return $this;
	}

	/**
	 * Add an OUTER JOIN clause to the query.
	 *
	 * Usage:
	 * $query->outerJoin('b ON b.id = a.id')->outerJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function outerJoin(string $condition): AbstractQuery
	{
		$this->join('OUTER', $condition);

		return $this;
	}

	/**
	 * Method to quote and optionally escape a string to database requirements for insertion into the database.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quote method directly.
	 *
	 * Note that 'q' is an alias for this method as it is in ADatabaseDriver.
	 *
	 * Usage:
	 * $query->quote('fulltext');
	 * $query->q('fulltext');
	 *
	 * @param   string  $text    The string to quote.
	 * @param   bool    $escape  True to escape the string, false to leave it unchanged.
	 *
	 * @return  string  The quoted input string.
	 * @throws  RuntimeException if the internal db property is not a valid object.
	 * @since   10.0
	 */
	public function quote(string $text, bool $escape = true): string
	{
		if (!($this->db instanceof AbstractDriver))
		{
			throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_INVALID_DB_OBJECT'));
		}

		return $this->db->quote(($escape ? $this->db->escape($text) : $text));
	}

	/**
	 * Wrap an SQL statement identifier name such as column, table or database names in quotes to prevent injection
	 * risks and reserved word conflicts.
	 *
	 * This method is provided for use where the query object is passed to a function for modification.
	 * If you have direct access to the database object, it is recommended you use the quoteName method directly.
	 *
	 * Note that 'qn' is an alias for this method as it is in ADatabaseDriver.
	 *
	 * Usage:
	 * $query->quoteName('#__a');
	 * $query->qn('#__a');
	 *
	 * @param   string|array  $name  The identifier name to wrap in quotes, or an array of identifier names to wrap in
	 *                               quotes. Each type supports dot-notation name.
	 * @param   string|array  $as    The AS query part associated to $name. It can be string or array, in latter case
	 *                               it has to be same length of $name; if is null there will not be any AS part for
	 *                               string or array element.
	 *
	 * @return  string|array  The quote wrapped name, same type of $name.
	 * @throws  RuntimeException if the internal db property is not a valid object.
	 * @since   10.0
	 */
	public function quoteName($name, $as = null)
	{
		if (!($this->db instanceof AbstractDriver))
		{
			throw new RuntimeException($this->db->getContainer()->get('language')->text('BRS_DATABASE_ERROR_INVALID_DB_OBJECT'));
		}

		return $this->db->quoteName($name, $as);
	}

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * Usage:
	 * $query->rightJoin('b ON b.id = a.id')->rightJoin('c ON c.id = b.id');
	 *
	 * @param   string  $condition  The join condition.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function rightJoin(string $condition): AbstractQuery
	{
		$this->join('RIGHT', $condition);

		return $this;
	}

	/**
	 * Add a single column, or array of columns to the SELECT clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 * The select method can, however, be called multiple times in the same query.
	 *
	 * Usage:
	 * $query->select('a.*')->select('b.id');
	 * $query->select(array('a.*', 'b.id'));
	 *
	 * @param   string|array  $columns  A string or an array of field names.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function select($columns): AbstractQuery
	{
		$this->type = 'select';

		if (is_null($this->select))
		{
			$this->select = new QueryElement('SELECT', $columns);
		}
		else
		{
			$this->select->append($columns);
		}

		return $this;
	}

	/**
	 * Add a single condition string, or an array of strings to the SET clause of the query.
	 *
	 * Usage:
	 * $query->set('a = 1')->set('b = 2');
	 * $query->set(array('a = 1', 'b = 2');
	 *
	 * @param   string|array  $conditions  A string or array of string conditions.
	 * @param   string        $glue        The glue by which to join the condition strings. Defaults to ,.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function set($conditions, string $glue = ','): AbstractQuery
	{
		if (is_null($this->set))
		{
			$glue      = strtoupper($glue);
			$this->set = new QueryElement('SET', $conditions, "\n\t$glue ");
		}
		else
		{
			$this->set->append($conditions);
		}

		return $this;
	}

	/**
	 * Allows a direct query to be provided to the database
	 * driver's setQuery() method, but still allow queries
	 * to have bounded variables.
	 *
	 * Usage:
	 * $query->setQuery('select * from #__users');
	 *
	 * @param   string|self  $sql  An SQL Query
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function setQuery($sql): AbstractQuery
	{
		$this->sql = $sql;

		return $this;
	}

	/**
	 * Add a table name to the UPDATE clause of the query.
	 *
	 * Note that you must not mix insert, update, delete and select method calls when building a query.
	 *
	 * Usage:
	 * $query->update('#__foo')->set(...);
	 *
	 * @param   string  $table  A table to update.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function update(string $table): AbstractQuery
	{
		$this->type   = 'update';
		$this->update = new QueryElement('UPDATE', $table);

		return $this;
	}

	/**
	 * Adds a tuple, or array of tuples that would be used as values for an INSERT INTO statement.
	 *
	 * Usage:
	 * $query->values('1,2,3')->values('4,5,6');
	 * $query->values(array('1,2,3', '4,5,6'));
	 *
	 * @param   string  $values  A single tuple, or array of tuples.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function values(string $values): AbstractQuery
	{
		if (is_null($this->values))
		{
			$this->values = new QueryElement('()', $values, '),(');
		}
		else
		{
			$this->values->append($values);
		}

		return $this;
	}

	/**
	 * Add a single condition, or an array of conditions to the WHERE clause of the query.
	 *
	 * Usage:
	 * $query->where('a = 1')->where('b = 2');
	 * $query->where(array('a = 1', 'b = 2'));
	 *
	 * @param   string|array  $conditions  A string or array of where conditions.
	 * @param   string        $glue        The glue by which to join the conditions. Defaults to AND.
	 *                                     Note that the glue is set on first use and cannot be changed.
	 *
	 * @return  self  Returns this object to allow chaining.
	 * @since   10.0
	 */
	public function where($conditions, string $glue = 'AND'): AbstractQuery
	{
		if (is_null($this->where))
		{
			$glue        = strtoupper($glue);
			$this->where = new QueryElement('WHERE', $conditions, " $glue ");
		}
		else
		{
			$this->where->append($conditions);
		}

		return $this;
	}

	/**
	 * Method to provide deep copy support to nested objects and
	 * arrays when cloning.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __clone()
	{
		foreach ($this as $k => $v)
		{
			// The database driver holds a Pimple container with Closure-based factories; skip it.
			if ($k === 'db')
			{
				continue;
			}

			if (is_object($v) || is_array($v))
			{
				$this->{$k} = unserialize(serialize($v));
			}
		}
	}

	/**
	 * Add a query to UNION with the current query.
	 * Multiple unions each require separate statements and create an array of unions.
	 *
	 * Usage:
	 * $query->union('SELECT name FROM  #__foo')
	 * $query->union('SELECT name FROM  #__foo','distinct')
	 * $query->union(array('SELECT name FROM  #__foo','SELECT name FROM  #__bar'))
	 *
	 * @param   AbstractQuery|string  $query     The AbstractQuery object or string to union.
	 * @param   bool                  $distinct  True to only return distinct rows from the union.
	 * @param   string                $glue      The glue by which to join the conditions.
	 *
	 * @return  AbstractQuery|string  The AbstractQuery object on success or bool false on failure.
	 * @since   10.0
	 */
	public function union($query, bool $distinct = false, string $glue = '')
	{

		// Clear any ORDER BY clause in UNION query
		// See http://dev.mysql.com/doc/refman/5.0/en/union.html
		if (!is_null($this->order))
		{
			$this->clear('order');
		}

		// Set up the DISTINCT flag, the name with parentheses, and the glue.
		if ($distinct)
		{
			$name = 'UNION DISTINCT ()';
			$glue = ')' . PHP_EOL . 'UNION DISTINCT (';
		}
		else
		{
			$glue = ')' . PHP_EOL . 'UNION (';
			$name = 'UNION ()';

		}
		// Get the QueryElement if it does not exist
		if (is_null($this->union))
		{
			$this->union = new QueryElement($name, $query, "$glue");
		}
		// Otherwise append the second UNION.
		else
		{
			$glue = '';
			$this->union->append($query);
		}

		return $this;
	}

	/**
	 * Add a query to UNION DISTINCT with the current query. Simply a proxy to Union with the Distinct clause.
	 *
	 * Usage:
	 * $query->unionDistinct('SELECT name FROM  #__foo')
	 *
	 * @param   AbstractQuery|string  $query  The AbstractQuery object or string to union.
	 * @param   string                $glue   The glue by which to join the conditions.
	 *
	 * @return  AbstractQuery|string   The AbstractQuery object on success or bool false on failure.
	 * @since   10.0
	 */
	public function unionDistinct($query, string $glue = '')
	{
		$distinct = true;

		// Apply the distinct flag to the union.
		return $this->union($query, $distinct, $glue);
	}
}