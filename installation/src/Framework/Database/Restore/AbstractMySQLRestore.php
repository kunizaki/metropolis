<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database\Restore;

use Akeeba\BRS\Framework\Database\AbstractDriver;
use Akeeba\BRS\Framework\Database\AbstractRestore;
use Akeeba\BRS\Framework\Database\Restore\Exception\Dbname;
use Akeeba\BRS\Framework\Database\Restore\Exception\Dbuser;
use Exception;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Common database restoration class for MySQL database drivers.
 *
 * @since  10.0
 */
class AbstractMySQLRestore extends AbstractRestore
{
	/** @var bool|null Cached result of shouldAutoConvert(). Null means not yet computed. */
	private $autoConvertCache = null;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container, string $dbkey, array $dbjsonValues)
	{
		parent::__construct($container, $dbkey, $dbjsonValues);

		// Set up allowed error codes
		$this->allowedErrorCodes = [
			1262,   // Truncated row when importing CSV (should ever occur)
			1263,   // Data truncated, NULL for NOT NULL...
			1264,   // Out of range value for column
			1265,   // "Data truncated" warning
			1266,   // Table created with MyISAM instead of InnoDB
			1287,   // Deprecated syntax
			1299,   // Invalid TIMESTAMP column value
			// , 1406	// "Data too long" error
		];

		// Set up allowed comment delimiters
		$this->comment = [
			'#',
			'\'-- ',
			'---',
			'/*!',
		];

		// Connect to the database
		$this->getDatabase();

		// Suppress foreign key checks
		if ($this->dbjsonValues['foreignkey'])
		{
			$this->executeQueryWithoutFailing('SET FOREIGN_KEY_CHECKS = 0');
		}

		// Suppress auto value on zero
		if ($this->dbjsonValues['noautovalue'])
		{
			$this->executeQueryWithoutFailing('SET @@SESSION.sql_mode = \'NO_AUTO_VALUE_ON_ZERO\'');
		}
	}

	/** @inheritdoc */
	protected function getDatabase(bool $selectDatabase = true): AbstractDriver
	{
		if (is_object($this->db))
		{
			return $this->db;
		}

		try
		{
			$db = parent::getDatabase(false);

			if (!$db->connected())
			{
				$db->connect();
			}
		}
		catch (Exception $e)
		{
			throw new Dbuser('', 500, $e);
		}

		$db->setUtf8Mb4AutoDetection($this->dbjsonValues['utf8mb4']);

		$previousException = null;

		try
		{
			$db->select($this->dbjsonValues['dbname']);
		}
		catch (Exception $exc)
		{
			$previousException = $exc;
		}

		if ($previousException)
		{
			try
			{
				// We couldn't connect to the database. Maybe we have to create it first. Let's see...
				$options = (object) [
					'db_name' => $this->dbjsonValues['dbname'],
					'db_user' => $this->dbjsonValues['dbuser'],
				];
				$db->createDatabase($options, true);
				$db->select($this->dbjsonValues['dbname']);
			}
			catch (Exception $e)
			{
				throw new Dbname('', 500, $previousException);
			}
		}

		// Try to change the database collation, if requested
		if ($this->dbjsonValues['utf8db'])
		{
			try
			{
				$db->alterDbCharacterSet($this->dbjsonValues['dbname']);
			}
			catch (Exception $exc)
			{
				// Ignore any errors
			}
		}

		return $this->db;
	}

	/** @inheritdoc */
	protected function processQueryLine(string $query): bool
	{
		$forceutf8     = $this->dbjsonValues['utf8tables'];
		$downgradeUtf8 = $forceutf8
		                 && (
			                 !$this->dbjsonValues['utf8mb4']
			                 || ($this->dbjsonValues['utf8mb4'] && !$this->db->supportsUtf8mb4())
		                 );

		$changeEncoding = false;
		$query          = trim($query);

		/**
		 * If there is a multiline comment at the beginning of the query remove it now.
		 */
		if (substr($query, 0, 2) == '/*')
		{
			$endCommentPos = strpos($query, '*/');

			if ($endCommentPos !== false)
			{
				$query = substr($query, $endCommentPos + 2);
			}
		}

		// CREATE TABLE query pre-processing
		if (substr($query, 0, 12) == 'CREATE TABLE')
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			// If the table has a prefix, back it up (if requested). In any case, drop the table. before attempting to create it.
			$tableName = $this->getCreateTableName($query);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($tableName, 'table'))
			{
				return true;
			}

			$this->dropOrRenameTable($tableName);
			$query          = $this->replaceEngineType($query);
			$query          = $this->removeTableOptions($query);
			$query          = $this->normalizeCollationsInCreateStatement($query);
			$changeEncoding = $forceutf8;
		}
		// CREATE VIEW query pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, ' VIEW ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			// In any case, drop the view before attempting to create it. (Views can't be renamed)
			$tableName = $this->getViewName($query);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($tableName, 'view'))
			{
				return true;
			}

			$this->dropView($tableName);

			$query = $this->normalizeCollationsInCreateStatement($query);
		}
		// CREATE PROCEDURE pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'PROCEDURE ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entity_keyword = ' PROCEDURE ';

			// Drop the entity (it cannot be renamed)
			$entity_name = $this->getEntityName($query, $entity_keyword);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($entity_name, 'procedure'))
			{
				return true;
			}

			$this->dropEntity($entity_keyword, $entity_name);

			$query = $this->normalizeCollationsInCreateStatement($query);
		}
		// CREATE FUNCTION pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'FUNCTION ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entity_keyword = ' FUNCTION ';

			// Drop the entity (it cannot be renamed)
			$entity_name = $this->getEntityName($query, $entity_keyword);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($entity_name, 'function'))
			{
				return true;
			}

			$this->dropEntity($entity_keyword, $entity_name);

			$query = $this->normalizeCollationsInCreateStatement($query);
		}
		// CREATE TRIGGER pre-processing
		elseif ((substr($query, 0, 7) == 'CREATE ') && (strpos($query, 'TRIGGER ') !== false))
		{
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}

			$entity_keyword = ' TRIGGER ';

			// Drop the entity (it cannot be renamed)
			$entity_name = $this->getEntityName($query, $entity_keyword);

			// Should we really restore it or ignore it?
			if (!$this->shouldRestoreEntity($entity_name, 'trigger'))
			{
				return true;
			}

			$this->dropEntity($entity_keyword, $entity_name);

			$query = $this->normalizeCollationsInCreateStatement($query);
		}
		elseif (substr($query, 0, 6) == 'INSERT')
		{
			if (!$this->inTransaction)
			{
				try
				{
					$this->db->transactionStart();

					$this->inTransaction = true;
				}
				catch (Exception $e)
				{
				}
			}

			$tableName = $this->getInsertTableName($query);

			/**
			 * Should we really restore it or ignore it?
			 *
			 * Note: We can only ever INSERT data into tables, that's why the entity type is always set to `table`.
			 */
			if (!$this->shouldRestoreEntity($tableName, 'table'))
			{
				return true;
			}

			$query = $this->applyReplaceInsteadofInsert($query);
		}
		else
		{
			// Maybe a DROP statement from the extensions filter? Just close the current transaction.
			if ($this->inTransaction)
			{
				try
				{
					$this->db->transactionCommit();

					$this->inTransaction = false;
				}
				catch (Exception $e)
				{
				}
			}
		}

		if (empty($query))
		{
			return true;
		}

		/**
		 * Automatically downgrade Unicode multibyte collations as necessary.
		 *
		 * NB! This has to run on ALL queries, including INSERTs.
		 *
		 * Each version family of MySQL supports different possible Unicode multibyte encodings.
		 *
		 * MySQL 8 supports the utf8mb4_0900_*_ci (Unicode 9.0.0), utf8mb4_unicode_520_ci (Unicode 5.2.0), the
		 * utf8mb4_unicode_ci (Unicode 4.0.0) and the other language-specific utf8mb4_*_ci encodings. It needs no
		 * conversion.
		 *
		 * MySQL 5.6 does not support the utf8mb4_0900_*_ci (Unicode 9.0.0) encodings. They need to be squashed to
		 * utf8mb4_unicode_520_ci.
		 *
		 * MySQL 5.5 does not support the utf8mb4_unicode_520_ci encoding. Both utf8mb4_0900_*_ci and
		 * utf8mb4_unicode_520_ci encodings need to be squashed into the legacy utf8mb4_unicode_ci encoding.
		 *
		 * Finally, we may be asked to downgrade the query to plain old UTF-8.
		 *
		 * The following code deals with the necessary downgrades.
		 *
		 * @see https://mysqlserverteam.com/new-collations-in-mysql-8-0-0/
		 *
		 * If the MySQL version is lower than 5.6.0 switch utf8mb4_unicode_520_ci to utf8mb4_unicode_ci. We need to do
		 * this regardless of UTF8MB4 support and/or downgrade. The idea is that utf8mb4_unicode_520_ci would be
		 * downgraded to utf8_unicode_520_ci which is an invalid collation. By converting it to utf8mb4_unicode_ci first
		 * we let MySQL 5.5 with utf8mb4 support work correctly AND ALSO the downgrade to plain UTF8 to work fine (by
		 * having the downgradeQueryToUtf8 convert the collation to the valid utf8_unicode_ci value).
		 */
		if ($downgradeUtf8)
		{
			/**
			 * Downgrade to UTF-8 requested.
			 *
			 * Convert all UTF8MB4 encoding references to UTF8. Convert four-byte characters to "Unicode replacement
			 * character" (U+FFFD). Data loss will result.
			 */
			$query = $this->downgradeQueryToUtf8($query);
		}

		/**
		 * A note about MySQL, PHP, stored procedures and the delimiter keyword. First read this:
		 * @see https://stackoverflow.com/questions/5311141/how-to-execute-mysql-command-delimiter
		 *
		 * In a traditional MySQL client defining a stored procedure, function, trigger etc usually requires doing:
		 * DELIMITER //
		 * CREATE TRIGGER foo.bar BEFORE INSERT ON bat_baz FOR EACH ROW
		 * BEGIN
		 * <body>
		 * EOT //
		 * DELIMITER ;
		 *
		 * If you tried executing this query one line at a time the DELIMITER lines would give you an error. So how do
		 * you define a stored procedure in PHP? Simply by executing the CREATE PROCEDURE / FUNCTION / TRIGGER without
		 * using the DELIMITER lines. PHP's mysql, mysqli and mysqlnd simply send a raw query to MySQL server for
		 * execution. They do not parse the text trying to figure out what is a "query" (unless using mysqli_multi_query
		 * which we don't actually use in our code). As a result MySQL is pretty darned happy executing your multi-line
		 * definition query without batting an eyelid. Simple, huh?
		 */
		$this->execute($query);

		// Do we have to forcibly apply UTF8 encoding?
		if (isset($tableName) && $changeEncoding)
		{
			$this->forciblyApplyTableEncoding($tableName);
		}

		return true;
	}

	/**
	 * Detect what database platform the SQL dump was created on.
	 *
	 * Inspects the first 64 KiB of the SQL file for platform-specific collation names. Returns:
	 * - `'mariadb1010'` if `uca1400` is found (MariaDB 10.10.2+)
	 * - `'mysql8'`      if `utf8mb4_0900` is found but not `uca1400`
	 * - `'unknown'`     otherwise (MySQL 5.x / MariaDB < 10.10.2 / undetectable)
	 *
	 * @return  string
	 * @since   10.1.0
	 */
	private function detectSourcePlatform(): string
	{
		$path = $this->getContainer()->get('paths')->get('installation')
		        . '/sql/' . $this->dbjsonValues['sqlfile'];

		$sample = @file_get_contents($path, false, null, 0, 65536);

		if ($sample === false)
		{
			return 'unknown';
		}

		if (stripos($sample, 'uca1400') !== false)
		{
			return 'mariadb1010';
		}

		if (stripos($sample, 'utf8mb4_0900') !== false)
		{
			return 'mysql8';
		}

		return 'unknown';
	}

	/**
	 * Detect what database platform the target (current) server is running.
	 *
	 * Returns one of:
	 * - `'mariadb1010'` – MariaDB 10.10.2+
	 * - `'mariadb'`     – MariaDB (any earlier version)
	 * - `'mysql8'`      – MySQL 8.0+
	 * - `'mysql56'`     – MySQL 5.6 / 5.7
	 * - `'mysql55'`     – MySQL 5.5 or older / undetectable
	 *
	 * @return  string
	 * @since   10.1.0
	 */
	private function detectTargetPlatform(): string
	{
		static $targetPlatform = null;

		if ($targetPlatform !== null)
		{
			return $targetPlatform;
		}

		$isMariaDB = method_exists($this->db, 'isMariaDB') ? $this->db->isMariaDB() : false;

		if ($isMariaDB)
		{
			$mariaDBVersion = preg_replace('/^5\.5\.5-/', '', $this->db->getVersion());

			if (version_compare($mariaDBVersion, '10.10.2', 'ge'))
			{
				return $targetPlatform = 'mariadb1010';
			}

			return $targetPlatform = 'mariadb';
		}

		if (version_compare($this->db->getVersion(), '7.999.999', 'ge'))
		{
			return $targetPlatform = 'mysql8';
		}

		if (version_compare($this->db->getVersion(), '5.6', 'lt'))
		{
			return $targetPlatform = 'mysql55';
		}

		return $targetPlatform = 'mysql56';
	}

	/**
	 * Determine whether collation conversion should be applied automatically.
	 *
	 * Compares the dump's source platform signature against the live target server to decide if
	 * a mismatch exists that would cause SQL errors without conversion.
	 *
	 * The result is cached in an instance variable because each AJAX call creates a fresh engine
	 * instance, so a single file scan per batch is acceptable.
	 *
	 * @return  bool
	 * @since   10.1.0
	 */
	private function shouldAutoConvert(): bool
	{
		if ($this->autoConvertCache !== null)
		{
			return $this->autoConvertCache;
		}

		$source = $this->detectSourcePlatform();
		$target = $this->detectTargetPlatform();

		if ($source === 'mariadb1010')
		{
			// uca1400 collations are MariaDB 10.10.2+ only; any other target needs conversion.
			$this->autoConvertCache = ($target !== 'mariadb1010');

			return $this->autoConvertCache;
		}

		if ($source === 'mysql8')
		{
			// utf8mb4_0900_* collations require MySQL 8+; MariaDB and older MySQL need conversion.
			$this->autoConvertCache = ($target !== 'mysql8');

			return $this->autoConvertCache;
		}

		// Unknown/old source: no platform-specific collations to worry about.
		$this->autoConvertCache = false;

		return $this->autoConvertCache;
	}

	/**
	 * Normalise the character collations in CREATE statements to utf8mb4 or utf8, depending on options.
	 *
	 * @param   string  $query  The SQL query to process.
	 *
	 * @return  string
	 * @since   9.10.0
	 */
	protected function normalizeCollationsInCreateStatement(string $query): string
	{
		$targetPlatform  = $this->detectTargetPlatform();
		$MySQL55         = ($targetPlatform === 'mysql55');
		$MariaDB         = in_array($targetPlatform, ['mariadb', 'mariadb1010'], true);
		$MariaDB_10_10_2 = ($targetPlatform === 'mariadb1010');
		$MySQL8          = ($targetPlatform === 'mysql8');

		$forceutf8     = $this->dbjsonValues['utf8tables'];
		$downgradeUtf8 = $forceutf8
		                 && (
			                 !$this->dbjsonValues['utf8mb4']
			                 || ($this->dbjsonValues['utf8mb4'] && !$this->db->supportsUtf8mb4())
		                 );

		$charsetConversion = $this->dbjsonValues['charset_conversion'] ?? 'auto';

		// Backward compat: coerce old bool values
		if ($charsetConversion === true || $charsetConversion === 1)
		{
			$charsetConversion = 'yes';
		}
		if ($charsetConversion === false || $charsetConversion === 0 || $charsetConversion === '')
		{
			$charsetConversion = 'no';
		}

		if (!$downgradeUtf8)
		{
			if ($charsetConversion === 'no')
			{
				return $query;
			}

			if ($charsetConversion === 'auto' && !$this->shouldAutoConvert())
			{
				return $query;
			}
		}

		/**
		 * Find the best possible collation for the current MySQL version.
		 *
		 * Each version family of MySQL supports different possible Unicode multibyte collations.
		 *
		 * - MariaDB 10.10.2 or later supports uca1400_* encodings.
		 * - MySQL 8 supports utf8mb4_0900_* (Unicode 9.0.0) encodings.
		 * - MySQL 5.6 supports utf8mb4_unicode_520_* encodings.
		 * - MySQL 5.5 only supports the utf8mb4_unicode_ci encoding.
		 *
		 * If we were asked to downgrade to plain old UTF8 (a.k.a. UTF8MB3) we use the utf8_general_ci collation. Note
		 * that on newer MySQL / MariaDB versions this will essentially become an alias of the corresponding UTF8MB4
		 * collation. However, we have code which squashes the database contents' character set to fit the UTF8MB3
		 * character set, meaning the downgrade is always lossy. This is why the downgrade must either be enforced by
		 * using a truly ancient database server version, or explicitly selected by the user.
		 *
		 * @link  https://dev.mysql.com/blog-archive/new-collations-in-mysql-8-0-0/
		 */
		if ($downgradeUtf8)
		{
			$defaultCollation = 'utf8_general_ci';
		}
		// MariaDB 10.10.2 or later: UCA 14.0.0 collation
		elseif ($MariaDB_10_10_2)
		{
			/** @link https://jira.mariadb.org/browse/MDEV-27009 */
			$defaultCollation = 'uca1400_ai_ci';
		}
		// MariaDB 10.10.1 or earlier: standard UTF8MB4 collation (Joomla! default)
		elseif ($MariaDB && !$MariaDB_10_10_2)
		{
			$defaultCollation = 'utf8mb4_unicode_ci';
		}
		// MySQL 8 or later: UCA 9.0.0 collation
		elseif ($MySQL8)
		{
			$defaultCollation = 'utf8mb4_0900_ai_ci';
		}
		// MySQL 5.6, 5.7: UCA 5.2.0 collation
		elseif (!$MySQL55)
		{
			$defaultCollation = 'utf8mb4_unicode_520_ci';
		}
		// MySQL 5.5, or anything we cannot be sure about: standard UTF8MB4 collation (Joomla! default)
		else
		{
			$defaultCollation = 'utf8mb4_unicode_ci';
		}

		/**
		 * Switch uca1400_* collations to default. Only applies when not MariaDB, or on MariaDB <= 10.10.2.
		 *
		 * @link https://jira.mariadb.org/browse/MDEV-27009
		 */
		if (!$MariaDB_10_10_2)
		{
			$query = preg_replace('/uca1400(_[a-z0-9]+)+_(ci|cs)/i', $defaultCollation, $query);
		}

		// Change utf*_bin collations to the default collation.
		$query = preg_replace('/(utf(8|16|16le|32|8mb3|8mb4)|ucs2)_bin/i', $defaultCollation, $query);

		// Change utf*/ucs2 collations to the default collation
		$query = preg_replace('/(utf(8|16|16le|32|8mb3|8mb4)|ucs2)(_[a-z0-9]+)+_c[is]/i', $defaultCollation, $query);

		// Replace any CHARACTER SET statements which might be in conflict with the collation
		$isTableWithCharset = preg_match(
			'/CREATE\s*TABLE\s+.*\(.*\).*(CHARACTER SET|CHARSET)\s*=?\s*([a-z0-9]+)/is', $query
		);
		$hasCollate         = preg_match('/(DEFAULT)?.*COLLATE\s*=?\s*((([a-z0-9]+)_)+[a-z0-9]+)/i', $query, $matches);
		$charset            = explode('_', $defaultCollation)[0];

		// -- Special case: uca1400* collations' charset is utf8mb4
		if ($charset === 'uca1400')
		{
			$charset = 'utf8mb4';
		}

		if ($isTableWithCharset && $hasCollate)
		{
			$query = preg_replace('/(CHARACTER SET|CHARSET)\s?=\s?([a-z0-9]+)(\b|$)/i', 'CHARSET=' . $charset, $query);
		}

		$query = preg_replace('/CHARACTER SET\s?([a-z0-9]+)(\b|$)/i', 'CHARACTER SET ' . $charset, $query);

		/**
		 * An automatic downgrade of Unicode multibyte encoding as necessary.
		 *
		 * Each version family of MySQL supports different possible Unicode multibyte encodings.
		 *
		 * MySQL 8 supports the utf8mb4_0900_*_ci (Unicode 9.0.0), utf8mb4_unicode_520_ci (Unicode 5.2.0), the
		 * utf8mb4_unicode_ci (Unicode 4.0.0) and the other language-specific utf8mb4_*_ci encodings. It needs no
		 * conversion.
		 *
		 * MySQL 5.6 does not support the utf8mb4_0900_*_ci (Unicode 9.0.0) encodings. They need to be squashed to
		 * utf8mb4_unicode_520_ci.
		 *
		 * MySQL 5.5 does not support the utf8mb4_unicode_520_ci encoding. Both utf8mb4_0900_*_ci and
		 * utf8mb4_unicode_520_ci encodings need to be squashed into the legacy utf8mb4_unicode_ci encoding.
		 *
		 * Finally, we may be asked to downgrade the query to plain old UTF-8.
		 *
		 * The following code deals with the necessary downgrades.
		 *
		 * @see https://dev.mysql.com/blog-archive/new-collations-in-mysql-8-0-0/
		 *
		 * If the MySQL version is lower than 5.6.0 switch utf8mb4_unicode_520_ci to utf8mb4_unicode_ci. We need to do
		 * this regardless of UTF8MB4 support and/or downgrade. The idea is that utf8mb4_unicode_520_ci would be
		 * downgraded to utf8_unicode_520_ci which is an invalid collation. By converting it to utf8mb4_unicode_ci first
		 * we let MySQL 5.5 with utf8mb4 support work correctly AND ALSO the downgrade to plain UTF8 to work fine (by
		 * having the downgradeQueryToUtf8 convert the collation to the valid utf8_unicode_ci value).
		 */
		if ($downgradeUtf8)
		{
			/**
			 * Downgrade to UTF-8 requested.
			 *
			 * Convert all UTF8MB4 encoding references to UTF8. Convert four-byte characters to "Unicode replacement
			 * character" (U+FFFD). Data loss will result.
			 */
			return $this->downgradeQueryToUtf8($query);
		}

		if ($MySQL55)
		{
			/**
			 * MySQL 5.5
			 *
			 * Convert all utf8mb4 character encodings to utf8mb4_unicode_ci.
			 *
			 * No data loss is expected but ordering issues will most definitely occur.
			 */
			return preg_replace('/utf8mb4_([a-z0-9]+_)+ci/i', 'utf8mb4_unicode_ci', $query);
		}

		if (!$MySQL8)
		{
			/**
			 * MySQL 5.6 and 5.7. All MariaDB versions.
			 *
			 * Convert utf8mb4_0900_* character encodings to the more generic utf8mb4_unicode_520_ci encoding.
			 *
			 * No data loss is expected but ordering issues are likely to occur.
			 *
			 * Note: this does not return immediately, as I have one more check to perform below.
			 */
			$query = preg_replace('/utf8mb4_0900_(([a-z0-9]+)_)+ci/i', 'utf8mb4_unicode_520_ci', $query);
		}

		return $query;
	}

	/**
	 * Extract the table name from a CREATE TABLE command
	 *
	 * @param   string  $query  The SQL query for the CREATE TABLE
	 *
	 * @return  string
	 */
	protected function getCreateTableName(string $query): string
	{
		/// Rest of query, after CREATE TABLE
		$restOfQuery = trim(substr($query, 12, strlen($query) - 12));

		// Is there a backtick?
		if (substr($restOfQuery, 0, 1) == '`')
		{
			// There is a backtick. Iterate character-by-character to find the ending backtick.
			$pos = 0;

			while (true)
			{
				$pos++;

				// We need visibility in both of the next characters to find escaped backticks.
				$thisChar = substr($restOfQuery, $pos, 1);
				$nextChar = substr($restOfQuery, $pos + 1, 1);

				// Did we reach the end of the string?
				if ($thisChar === false || $thisChar === '')
				{
					break;
				}

				// Two backticks side-by-side is an escaped backtick; skip over it
				if ($thisChar === '`' && $nextChar === '`')
				{
					$pos++;
					continue;
				}

				// Current char is a backtick, the next one is not, we found the ending backtick.
				if ($thisChar === '`' && $nextChar !== '`')
				{
					break;
				}
			}

			$entityName = substr($restOfQuery, 1, $pos - 1);
		}
		else
		{
			// No backtick. Let's assume the entity name ends in the next blank character.
			$pos        = strpos($restOfQuery, ' ', 1);
			$entityName = substr($restOfQuery, 0, $pos);
		}

		return $entityName;
	}

	/**
	 * Extract the table name from a INSERT INTO command
	 *
	 * @param   string  $query  The SQL query for the INSERT INTO
	 *
	 * @return  string
	 */
	protected function getInsertTableName(string $query): string
	{
		// Rest of query, after INSERT INTO
		$restOfQuery = trim(substr($query, 11, strlen($query) - 11));

		// Is there a backtick?
		if (substr($restOfQuery, 0, 1) == '`')
		{
			// There is a backtick. Iterate character-by-character to find the ending backtick.
			$pos = 0;

			while (true)
			{
				$pos++;

				// We need visibility in both of the next characters to find escaped backticks.
				$thisChar = substr($restOfQuery, $pos, 1);
				$nextChar = substr($restOfQuery, $pos + 1, 1);

				// Did we reach the end of the string?
				if ($thisChar === false || $thisChar === '')
				{
					break;
				}

				// Two backticks side-by-side is an escaped backtick; skip over it
				if ($thisChar === '`' && $nextChar === '`')
				{
					$pos++;
					continue;
				}

				// Current char is a backtick, the next one is not, we found the ending backtick.
				if ($thisChar === '`' && $nextChar !== '`')
				{
					break;
				}
			}

			$entityName = substr($restOfQuery, 1, $pos - 1);
		}
		else
		{
			// No backtick. Let's assume the entity name ends in the next blank character.
			$pos        = strpos($restOfQuery, ' ', 1);
			$entityName = substr($restOfQuery, 0, $pos);
		}

		return $entityName;
	}

	/**
	 * Drop or rename a table (with a bak_ prefix), depending on the user options
	 *
	 * @param   string  $tableName  The table name to drop or rename
	 *
	 * @return  void
	 */
	protected function dropOrRenameTable(string $tableName): void
	{
		$db = $this->getDatabase();

		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		// Should I back the table up?
		if (($prefix != '') && ($existing == 'backup') && (strpos($tableName, '#__') == 0))
		{
			// It's a table with a prefix, a prefix IS specified and we are asked to back it up.
			// Start by dropping any existing backup tables
			$backupTable = str_replace('#__', 'bak_', $tableName);
			try
			{
				$db->dropTable($backupTable);

				$db->renameTable($tableName, $backupTable);
			}
			catch (Exception $exc)
			{
				// We can't rename the table. Fall-through to the final line to delete it.
			}
		}

		// Try to drop the table anyway
		$db->dropTable($tableName);
	}

	/**
	 * Extract the View name from a CREATE VIEW query
	 *
	 * @param   string  $query  The SQL query
	 *
	 * @return  string
	 */
	protected function getViewName(string $query): string
	{
		$view_pos    = strpos($query, ' VIEW ');
		$restOfQuery = trim(substr($query, $view_pos + 6)); // Rest of query, after VIEW string

		// Is there a backtick?
		if (substr($restOfQuery, 0, 1) == '`')
		{
			// There is a backtick. Iterate character-by-character to find the ending backtick.
			$pos = 0;

			while (true)
			{
				$pos++;

				// We need visibility in both of the next characters to find escaped backticks.
				$thisChar = substr($restOfQuery, $pos, 1);
				$nextChar = substr($restOfQuery, $pos + 1, 1);

				// Did we reach the end of the string?
				if ($thisChar === false || $thisChar === '')
				{
					break;
				}

				// Two backticks side-by-side is an escaped backtick; skip over it
				if ($thisChar === '`' && $nextChar === '`')
				{
					$pos++;
					continue;
				}

				// Current char is a backtick, the next one is not, we found the ending backtick.
				if ($thisChar === '`' && $nextChar !== '`')
				{
					break;
				}
			}

			$entityName = substr($restOfQuery, 1, $pos - 1);
		}
		else
		{
			// No backtick. Let's assume the entity name ends in the next blank character.
			$pos        = strpos($restOfQuery, ' ', 1);
			$entityName = substr($restOfQuery, 0, $pos);
		}

		return $entityName;
	}

	/**
	 * Drops a View (VIEWs cannot be renamed)
	 *
	 * @param   string  $tableName
	 *
	 * @return  void
	 */
	protected function dropView(string $tableName): void
	{
		$db        = $this->getDatabase();
		$dropQuery = 'DROP VIEW IF EXISTS ' . $db->quoteName($tableName) . ';';
		$db->setQuery(trim($dropQuery));
		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
		}
	}

	/**
	 * Extracts the name of an entity (procedure, trigger, function) from a CREATE query
	 *
	 * @param   string  $query    The SQL query
	 * @param   string  $keyword  The entity type, uppercase (e.g. "PROCEDURE")
	 *
	 * @return  string
	 */
	protected function getEntityName(string $query, string $keyword): string
	{
		$entity_pos  = strpos($query, $keyword);
		$restOfQuery =
			trim(substr($query, $entity_pos + strlen($keyword))); // Rest of query, after entity key string

		// Is there a backtick?
		if (substr($restOfQuery, 0, 1) == '`')
		{
			// There is a backtick. Iterate character-by-character to find the ending backtick.
			$pos = 0;

			while (true)
			{
				$pos++;

				// We need visibility in both of the next characters to find escaped backticks.
				$thisChar = substr($restOfQuery, $pos, 1);
				$nextChar = substr($restOfQuery, $pos + 1, 1);

				// Did we reach the end of the string?
				if ($thisChar === false || $thisChar === '')
				{
					break;
				}

				// Two backticks side-by-side is an escaped backtick; skip over it
				if ($thisChar === '`' && $nextChar === '`')
				{
					$pos++;
					continue;
				}

				// Current char is a backtick, the next one is not, we found the ending backtick.
				if ($thisChar === '`' && $nextChar !== '`')
				{
					break;
				}
			}

			$entityName = substr($restOfQuery, 1, $pos - 1);
		}
		else
		{
			// No backtick. Let's assume the entity name ends in the next blank character.
			$pos        = strpos($restOfQuery, ' ', 1);
			$entityName = substr($restOfQuery, 0, $pos);
		}

		return $entityName;
	}

	/**
	 * Drops an entity (procedure, trigger, function)
	 *
	 * @param   string  $keyword     Entity type, e.g. "PROCEDURE"
	 * @param   string  $entityName  Entity name
	 *
	 * @return  void
	 */
	protected function dropEntity(string $keyword, string $entityName): void
	{
		$db        = $this->getDatabase();
		$dropQuery = 'DROP' . $keyword . 'IF EXISTS ' . $db->quoteName($entityName) . ';';
		$db->setQuery(trim($dropQuery));
		$db->execute();
	}

	/**
	 * Switches an INSERT INTO query into a REPLACE INTO query if the user has so specified
	 *
	 * @param   string  $query  The query to switch
	 *
	 * @return  string  The switched query
	 */
	protected function applyReplaceInsteadofInsert(string $query): string
	{
		$replacesql = $this->dbjsonValues['replace'];

		if ($replacesql)
		{
			// Use REPLACE instead of INSERT selected
			return 'REPLACE ' . substr($query, 7);
		}

		return $query;
	}

	/**
	 * Downgrade a query from UTF8MB4 to plain old UTF8
	 *
	 * @param   string  $query  The query to downgrade
	 *
	 * @return  string  The downgraded query
	 */
	protected function downgradeQueryToUtf8(string $query): string
	{
		// Squash all possible utf8mb4_*_ci collations into the legacy utf8_unicode_ci
		$query = preg_replace('/utf8mb4_([a-z0-9]+_)+ci/i', 'utf8_unicode_ci', $query);
		// Failsafe for any utf8mb4_ encodings we didn't catch above
		$query = str_ireplace('utf8mb4_', 'utf8_', $query);
		// In case the encoding and character set are defined separately
		$query = str_ireplace('utf8mb4', 'utf8', $query);

		// Squash UTF8MB4 characters to "Unicode replacement character" (U+FFFD). Slow and reliable.

		/** @noinspection RegExpUnnecessaryNonCapturingGroup */
		return preg_replace(
			'%(?:\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})%xs', '�', $query
		);
	}

	/**
	 * Forcibly apply a new table encoding (UTF8 or UTF8MB4 depending on user selections and execution environment)
	 *
	 * @param   string  $tableName  The table name to apply the encoding to
	 *
	 * @return  void
	 */
	protected function forciblyApplyTableEncoding(string $tableName): void
	{
		$db = $this->getDatabase();

		// Get a list of columns
		$columns = $db->getTableColumns($tableName);
		$mods    = []; // array to hold individual MODIFY COLUMN commands

		foreach ($columns as $field => $column)
		{
			// Make sure we are redefining only columns which do support a collation
			$col = (object) $column;

			if (empty($col->Collation))
			{
				continue;
			}

			$null    = $col->Null == 'YES' ? 'NULL' : 'NOT NULL';
			$default = is_null($col->Default) ? '' : "DEFAULT '" . $db->escape($col->Default) . "'";

			$collation = $this->db->supportsUtf8mb4() ? 'utf8mb4_unicode_ci' : 'utf8_general_ci';

			$mods[] = "MODIFY COLUMN " . $db->qn($field) . " {$col->Type} $null $default COLLATE $collation";
		}

		// Begin the modification statement
		$sql = "ALTER TABLE " . $db->qn($tableName) . " ";

		// Add commands to modify columns
		if (!empty($mods))
		{
			$sql .= implode(', ', $mods) . ', ';
		}

		// Add commands to modify the table collation
		$charset   = $this->db->supportsUtf8mb4() ? 'utf8mb4' : 'utf8';
		$collation = $this->db->supportsUtf8mb4() ? 'utf8mb4_unicode_ci' : 'utf8_general_ci';
		$sql       .= 'DEFAULT CHARACTER SET ' . $charset . ' COLLATE ' . $collation . ';';
		$db->setQuery($sql);

		try
		{
			$db->execute();
		}
		catch (Exception $exc)
		{
			// Don't fail if the collation could not be changed
		}
	}

	/**
	 * Execute a database query, ignoring any failures
	 *
	 * @param   string  $sql  The SQL query to execute
	 *
	 * @return  void
	 */
	protected function executeQueryWithoutFailing(string $sql): void
	{
		$this->db->setQuery($sql);

		try
		{
			$this->db->execute();
		}
		catch (Exception $exc)
		{
			// Do nothing if that fails. Maybe we can continue with the restoration.
		}
	}

	/**
	 * Replaces the engine type in a CREATE TABLE query when restoring from Percona or MariaDB to MySQL. Basically, it
	 * assumes that any kind of database storage engine it cannot recognize has to be replaced with MyISAM.
	 *
	 * @param   string  $query  The CREATE TABLE SQL query that you want modified
	 *
	 * @return  string  The modified CREATE TABLE query
	 */
	protected function replaceEngineType(string $query): string
	{
		static $supportedEngines = null;
		static $defaultEngine = 'MyISAM';

		if (is_null($supportedEngines))
		{
			// Get the supported database engines and convert them to all uppercase
			$supportedEngines = $this->getSupportedDatabaseEngines();
			$supportedEngines = array_map('strtoupper', $supportedEngines);

			// The server's default engine is the first one listed (see getSupportedDatabaseEngines)
			$defaultEngine = reset($supportedEngines);

			// However, InnoDB + UTF8MB4 = lots of pain if the developer hadn't expected it. So we shall always try
			// to use MyISAM whenever possible. In fact, in most of the cases we're trying to convert Aria tables back
			// to MyISAM when you transfer between MariaDB and MySQL.
			if (in_array('MYISAM', $supportedEngines))
			{
				$defaultEngine = 'MyISAM';
			}
		}

		// Get the engine in the CREATE TABLE command
		$engine          = $this->getCreateTableEngine($query);
		$engineUppercase = strtoupper($engine);

		// Check if the engine is supported. Otherwise use the default engine instead.
		if (!in_array($engineUppercase, $supportedEngines))
		{
			$replacements = [
				'ENGINE=' . $engine,
				'ENGINE =' . $engine,
				'ENGINE= ' . $engine,
				'ENGINE = ' . $engine,
				'TYPE=' . $engine,
				'TYPE =' . $engine,
				'TYPE= ' . $engine,
				'TYPE = ' . $engine,
			];

			foreach ($replacements as $find)
			{
				$replaceWith = (substr($find, 0, 4) == 'TYPE') ? 'TYPE=' : 'ENGINE=';
				$query       = str_ireplace($find, $replaceWith . $defaultEngine, $query);
			}
		}

		return $query;
	}

	/**
	 * Postprocess a CREATE TABLE query in the same way the backup engine does.
	 *
	 * This is here to deal with backups taken with a version of the backup engine older than this version of BRS. It
	 * allows us to retroactively address restoration issues caused at backup time by the database server returning too
	 * much information about the table which makes the CREATE TABLE incompatible with the new host.
	 *
	 * @param   string  $query  The CREATE TABLE query to post-process
	 *
	 * @return  string The post-processed query
	 */
	protected function removeTableOptions(string $query): string
	{
		// Translate TYPE= to ENGINE=
		$query = str_replace('TYPE=', 'ENGINE=', $query);

		/**
		 * Remove the TABLESPACE option.
		 *
		 * The format of the TABLESPACE table option is:
		 * TABLESPACE tablespace_name [STORAGE {DISK|MEMORY}]
		 * where tablespace_name can be a quoted or unquoted identifier.
		 */
		[$validCharRegEx, $unicodeFlag] = $this->getMySQLIdentifierCharacterRegEx();
		$tablespaceName = "((($validCharRegEx){1,})|(`.*`))";
		$suffix         = 'STORAGE\s{1,}(DISK|MEMORY)';
		$regex          = "#TABLESPACE\s+$tablespaceName\s*($suffix)?#i" . $unicodeFlag;
		$query          = preg_replace($regex, '', $query);

		// Remove table options {DATA|INDEX} DIRECTORY
		$regex = "#(DATA|INDEX)\s+DIRECTORY\s*=?\s*'.*'#i";
		$query = preg_replace($regex, '', $query);

		// Remove table options ROW_FORMAT=whatever (this can be realyl problematic with InnoDB)
		$regex = "#ROW_FORMAT\s*=\s*[A-Z]+#i";
		$query = preg_replace($regex, '', $query);

		// Abstract the names of table constraints and indices
		$prefix = $this->db->getPrefix();
		$regex  = "#(CONSTRAINT|KEY|INDEX)\s+`{$prefix}#i";
		$query  = preg_replace($regex, '$1 `#__', $query);

		return $query;
	}

	/**
	 * Get a regular expression and its options for valid characters of an unquoted MySQL identifier.
	 *
	 * This is used wherever we need to detect an arbitrary, unquoted MySQL identifier per
	 * https://dev.mysql.com/doc/refman/5.7/en/identifiers.html
	 *
	 * Normally, we can use a pretty simple regular expression that makes use of the \X property (extended grapheme
	 * cluster) to describe the supported characters outside the 0-9, a-Z, A-Z, dollar and underscore ASCII ranges.
	 *
	 * HOWEVER! We discovered that Ubuntu 18.04 ships with a version of PCRE which does not support the \X property
	 * in character classes (the stuff between brackets). In this case we have to fall back to a long-winded regex
	 * that explicitly adds the \u0080 - \uFFFF range as an alternative.
	 *
	 * Also what if Unicode support is not compiled in PCRE? In this case we will fall back to a much simpler regex
	 * which only supports the ASCII subset of the allowed characters. In this case your database dump will be wrong
	 * if you use table names with non-ASCII characters.
	 *
	 * Since the detection is horribly slow we cache its results in an internal static variable.
	 *
	 * @return  array  In the format [$regex, $flags]
	 * @since   7.0.0
	 */
	protected function getMySQLIdentifierCharacterRegEx(): array
	{
		static $validCharRegEx = null;
		static $unicodeFlag = null;

		if (is_null($validCharRegEx) || is_null($unicodeFlag))
		{
			$brokenPCRE     = @preg_match('/[0-9a-zA-Z$_\X]/u', 's') === false;
			$noUnicode      = @preg_match('/\p{L}/u', 'σ') !== 1;
			$unicodeFlag    = $noUnicode ? '' : 'u';
			$validCharRegEx = $noUnicode
				? '[0-9a-zA-Z$_]'
				: ($brokenPCRE ? '[0-9a-zA-Z$_]|[\x{0080}-\x{FFFF}]'
					: '[0-9a-zA-Z$_\X]');
		}

		return [$validCharRegEx, $unicodeFlag];
	}

	/**
	 * Ask the database to return a list of the supported database storage engines.
	 *
	 * @return  array
	 */
	protected function getSupportedDatabaseEngines(): array
	{
		// Default database engines
		$defaultEngines = ['MyISAM', 'BLACKHOLE', 'MEMORY', 'ARCHIVE', 'InnoDB'];

		$db  = $this->getDatabase();
		$sql = 'SHOW ENGINES';

		try
		{
			$engineMatrix = $db->setQuery($sql)->loadAssocList();
		}
		catch (Exception $e)
		{
			return $defaultEngines;
		}

		$engines = [];

		foreach ($engineMatrix as $engineItem)
		{
			if (!isset($engineItem['Engine']))
			{
				continue;
			}

			if (!isset($engineItem['Support']))
			{
				continue;
			}

			$support = strtoupper($engineItem['Support']);

			if (!in_array($support, ['YES', 'DEFAULT', 'TRUE', '1']))
			{
				continue;
			}

			// The default engine goes on top
			if ($support == 'DEFAULT')
			{
				array_unshift($engines, $engineItem['Engine']);

				continue;
			}

			// Other engines go to the bottom of the list
			$engines[] = $engineItem['Engine'];
		}

		if (empty($engines))
		{
			return $defaultEngines;
		}

		return $engines;
	}

	protected function getCreateTableEngine(string $query): string
	{
		// Fallback...
		$engine = 'MyISAM';

		// This is what MySQL should be using.
		$engine_keys = ['ENGINE=', 'TYPE=', 'ENGINE =', 'TYPE ='];

		foreach ($engine_keys as $engine_key)
		{
			$start_pos = strrpos($query, $engine_key);

			if ($start_pos !== false)
			{
				// Advance the start position just after the position of the ENGINE keyword
				$start_pos += strlen($engine_key);
				// Try to locate the space after the engine type
				$end_pos = stripos($query, ' ', $start_pos);

				if ($end_pos === false)
				{
					// Uh... maybe it ends with ENGINE=EngineType;
					$end_pos = stripos($query, ';', $start_pos);
				}

				if ($end_pos !== false)
				{
					// Grab the string
					$engine = substr($query, $start_pos, $end_pos - $start_pos);

					break;
				}
			}
		}

		return $engine;
	}

	/** @inheritdoc */
	protected function conditionallyDropTables(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('table_name'))
			->from($db->qn('information_schema') . '.' . $db->qn('tables'))
			->where($db->qn('table_schema') . ' = ' . $db->q($this->dbjsonValues['dbname']));

		if ($existing == 'dropprefix')
		{
			$query->where($db->qn('table_name') . ' LIKE ' . $db->q($prefix . '%'));
		}

		$tables = $db->setQuery($query)->loadColumn();

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->execute();

		foreach ($tables as $table)
		{
			try
			{
				$db->dropTable($table, true);
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->execute();
	}

	/** @inheritdoc */
	protected function conditionallyDropViews(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('table_name'))
			->from($db->qn('information_schema') . '.' . $db->qn('views'))
			->where($db->qn('table_schema') . ' = ' . $db->q($this->dbjsonValues['dbname']));

		if ($existing == 'dropprefix')
		{
			$query->where($db->qn('table_name') . ' LIKE ' . $db->q($prefix . '%'));
		}

		$views = $db->setQuery($query)->loadColumn();

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->execute();

		foreach ($views as $view)
		{
			try
			{
				$sql = 'DROP VIEW IF EXISTS ' . $db->q($view);

				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->execute();
	}

	/** @inheritdoc */
	protected function conditionallyDropTriggers(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('TRIGGER_NAME'))
			->from($db->qn('information_schema') . '.' . $db->qn('triggers'))
			->where($db->qn('TRIGGER_SCHEMA') . ' = ' . $db->q($this->dbjsonValues['dbname']));

		if ($existing == 'dropprefix')
		{
			$query->where($db->qn('TRIGGER_NAME') . ' LIKE ' . $db->q($prefix . '%'));
		}

		$triggers = $db->setQuery($query)->loadColumn();

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->execute();

		foreach ($triggers as $trigger)
		{
			try
			{
				$sql = 'DROP TRIGGER IF EXISTS ' . $db->q($trigger);

				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->execute();
	}

	/** @inheritdoc */
	protected function conditionallyDropFunctions(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('ROUTINE_NAME'))
			->from($db->qn('information_schema') . '.' . $db->qn('ROUTINES'))
			->where($db->qn('ROUTINE_SCHEMA') . ' = ' . $db->q($this->dbjsonValues['dbname']))
			->where($db->qn('ROUTINE_TYPE') . ' = ' . $db->q('FUNCTION'));

		if ($existing == 'dropprefix')
		{
			$query->where($db->qn('ROUTINE_NAME') . ' LIKE ' . $db->q($prefix . '%'));
		}

		$functions = $db->setQuery($query)->loadColumn();

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->execute();

		foreach ($functions as $function)
		{
			try
			{
				$sql = 'DROP FUNCTION IF EXISTS ' . $db->q($function);

				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->execute();
	}

	/** @inheritdoc */
	protected function conditionallyDropProcedures(): void
	{
		$db       = $this->getDatabase();
		$prefix   = $this->dbjsonValues['prefix'];
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		$query = $db->getQuery(true)
			->select($db->qn('ROUTINE_NAME'))
			->from($db->qn('information_schema') . '.' . $db->qn('ROUTINES'))
			->where($db->qn('ROUTINE_SCHEMA') . ' = ' . $db->q($this->dbjsonValues['dbname']))
			->where($db->qn('ROUTINE_TYPE') . ' = ' . $db->q('PROCEDURE'));

		if ($existing == 'dropprefix')
		{
			$query->where($db->qn('ROUTINE_NAME') . ' LIKE ' . $db->q($prefix . '%'));
		}

		$procedures = $db->setQuery($query)->loadColumn();

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 0');
		$db->execute();

		foreach ($procedures as $procedure)
		{
			try
			{
				$sql = 'DROP FUNCTION IF EXISTS ' . $db->q($procedure);

				$db->setQuery($sql)->execute();
			}
			catch (Exception $e)
			{
				// We can safely ignore errors at this stage
			}
		}

		$db->setQuery('SET FOREIGN_KEY_CHECKS = 1');
		$db->execute();
	}
}