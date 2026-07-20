<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Configuration;

defined('_AKEEBA') or die();

/**
 * Database configuration.
 *
 * This represents one entry in the databases.json file.
 *
 * Properties coming from the databases.json file
 *
 * @property-read  string $dbtype                 The database driver.
 * @property-read  string $dbtech                 Database technology. Currently, only `mysql` is supported.
 * @property-read  string $dbname                 The name of the database.
 * @property-read  string $sqlfile                The name of the SQL file, e.g. `site.sql`.
 * @property-read  string $marker                 The marker separating consecutive SQL statements.
 * @property-read  string $dbhost                 Database server hostname.
 * @property-read  int    $dbport                 Database server TCP/IP port. Must be an integer 1 to 65535.
 * @property-read  string $dbsocket               The database UNIX socket path.
 * @property-read  string $dbuser                 The database user's username.
 * @property-read  string $dbpass                 The database user's password.
 * @property-read  string $prefix                 The common database table name prefix, e.g. `foo_`.
 * @property-read  bool   $dbencryption           Should I use an encrypted database connection?
 * @property-read  string $dbsslcipher            SSL/TLS ciphers to use for the connection.
 * @property-read  string $dbsslca                Database SSL/TLS Certification AAuthority file.
 * @property-read  string $dbsslkey               Database SSL/TLS key file.
 * @property-read  string $dbsslcert              Database SSL/TLS certificate file.
 * @property-read  bool   $dbsslverifyservercert  Should I verify the server's TLS/SSL certificate?
 * @property-read  int    $parts                  How many part files does the database dump consist of?
 * @property-read  array  $tables                 List of included tables in the database dump.
 *
 * Additional properties only defined at runtime
 *
 * @property-read  string $existing               What to do with existing tables.
 * @property-read  bool   $foreignkey             Suppress foreign key checks?
 * @property-read  bool   $noautovalue            No auto value on zero?
 * @property-read  bool   $replace                Use REPLACE instead of INSERT?
 * @property-read  bool   $utf8db                 Force UTF-8 collation on database?
 * @property-read  bool   $utf8tables             Force UTF-8 collation on tables?
 * @property-read  bool   $utf8mb4                Allow UTF8MB4 auto-detection?
 * @property-read  string $charset_conversion     Normalise character set? One of 'yes', 'auto', 'no'.
 * @property-read  bool   $break_on_failed_create Stop on CREATE error?
 * @property-read  bool   $break_on_failed_insert Stop on other error?
 *
 * @since  10.0
 */
final class Database extends AbstractConfiguration
{
	protected $dbtype = 'mysqli';

	protected $dbtech = 'mysql';

	protected $dbname = 'site';

	protected $sqlfile = 'site.sql';

	protected $marker = "\n/**ABDB**/";

	protected $dbhost = 'localhost';

	protected $dbport = null;

	protected $dbsocket = null;

	protected $dbuser = '';

	protected $dbpass = '';

	protected $prefix = 'new_';

	protected $dbencryption = false;

	protected $dbsslcipher = '';

	protected $dbsslca = '';

	protected $dbsslkey = '';

	protected $dbsslcert = '';

	protected $dbsslverifyservercert = false;

	protected $parts = 0;

	protected $tables = [];

	protected $existing = 'drop';

	protected $foreignkey = true;

	protected $noautovalue = true;

	protected $replace = false;

	protected $utf8db = false;

	protected $utf8tables = false;

	protected $utf8mb4 = true;

	protected $charset_conversion = 'auto';

	protected $break_on_failed_create = true;

	protected $break_on_failed_insert = true;

	protected $maxexectime = 10;

	protected $throttle = 250;


	/**
	 * Loads the databases.json file.
	 *
	 * @param   string  $filePath  The path to the databases.json file
	 *
	 * @return  array
	 * @since   10.0
	 */
	public static function loadDatabasesJson(string $filePath): array
	{
		$json = @file_get_contents($filePath);

		if ($json === false)
		{
			return [];
		}

		try
		{
			$data = @json_decode($json, true);
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!is_array($data))
		{
			return [];
		}

		// Make sure that the `site.sql` entry (main database for the site) is always first.
		if (isset($data['site.sql']) && count($data) > 1)
		{
			$temp = $data['site.sql'];

			unset($data['site.sql']);

			$data = ['site.sql' => $temp] + $data;
		}

		return $data;
	}

	public function setDbtype(?string $dbtype): void
	{
		$dbtype = strtolower($dbtype ?? 'mysqli');

		if (!in_array($dbtype, ['mysqli', 'pdomysql', 'postgresql', 'none']))
		{
			throw new \DomainException('Invalid database type');
		}

		$this->dbtype = $dbtype;
	}

	public function setDbtech(string $dbtech): void
	{
		$dbtech = strtolower($dbtech);

		if (!in_array($dbtech, ['mysql', 'postgresql', 'none']))
		{
			throw new \DomainException('Invalid database technology');
		}

		$this->dbtech = $dbtech;
	}

	public function setDbname(string $dbname): void
	{
		$this->dbname = $dbname;
	}

	public function setSqlfile(string $sqlfile): void
	{
		$this->sqlfile = $sqlfile;
	}

	public function setMarker(string $marker): void
	{
		$this->marker = $marker;
	}

	public function setDbhost(?string $dbhost): void
	{
		$this->dbhost = $dbhost ?? '';
	}

	public function setDbport($dbport): void
	{
		if (is_null($dbport ?: null))
		{
			$this->dbport = null;

			return;
		}

		$dbport = (int) $dbport ?: null;

		if ($dbport < 1 || $dbport > 65535)
		{
			throw new \OutOfRangeException('Invalid port number. Must be between 1 and 65535.');
		}

		$this->dbport = $dbport ?: null;
	}

	public function setDbsocket(?string $dbsocket): void
	{
		$this->dbsocket = $dbsocket;
	}

	public function setDbuser(?string $dbuser): void
	{
		$this->dbuser = $dbuser ?? '';
	}

	public function setDbpass(?string $dbpass): void
	{
		$this->dbpass = $dbpass ?? '';
	}

	public function setPrefix(?string $prefix): void
	{
		$this->prefix = $prefix ?? '';
	}

	public function setDbencryption(bool $dbencryption): void
	{
		$this->dbencryption = $dbencryption;
	}

	public function setDbsslcipher(?string $dbsslcipher): void
	{
		$this->dbsslcipher = $dbsslcipher ?? '';
	}

	public function setDbsslca(?string $dbsslca): void
	{
		$this->dbsslca = $dbsslca ?? '';
	}

	public function setDbsslkey(?string $dbsslkey): void
	{
		$this->dbsslkey = $dbsslkey ?? '';
	}

	public function setDbsslcert(?string $dbsslcert): void
	{
		$this->dbsslcert = $dbsslcert ?? '';
	}

	public function setDbsslverifyservercert(bool $dbsslverifyservercert): void
	{
		$this->dbsslverifyservercert = $dbsslverifyservercert;
	}

	public function setParts(int $parts): void
	{
		$this->parts = $parts;
	}

	public function setTables(array $tables): void
	{
		$this->tables = $tables;
	}

	public function setExisting(?string $existing): void
	{
		$existing = strtolower($existing ?? 'drop');

		if (!in_array($existing, ['drop', 'backup', 'dropall', 'dropprefix']))
		{
			$existing = 'drop';
		}

		$this->existing = $existing;
	}

	public function setForeignkey(bool $foreignkey): void
	{
		$this->foreignkey = $foreignkey;
	}

	public function setNoautovalue(bool $noautovalue): void
	{
		$this->noautovalue = $noautovalue;
	}

	public function setReplace(bool $replace): void
	{
		$this->replace = $replace;
	}

	public function setUtf8db(bool $utf8db): void
	{
		$this->utf8db = $utf8db;
	}

	public function setUtf8tables(bool $utf8tables): void
	{
		$this->utf8tables = $utf8tables;
	}

	public function setUtf8mb4(bool $utf8mb4): void
	{
		$this->utf8mb4 = $utf8mb4;
	}

	public function setCharsetConversion($charset_conversion): void
	{
		// Backward compat: coerce legacy bool/int values
		if ($charset_conversion === true || $charset_conversion === 1 || $charset_conversion === '1')
		{
			$charset_conversion = 'yes';
		}

		if ($charset_conversion === false || $charset_conversion === 0 || $charset_conversion === '0'
		    || $charset_conversion === '')
		{
			$charset_conversion = 'no';
		}

		if ($charset_conversion === null)
		{
			$charset_conversion = 'auto';
		}

		if (!in_array($charset_conversion, ['yes', 'auto', 'no'], true))
		{
			$charset_conversion = 'auto';
		}

		$this->charset_conversion = $charset_conversion;
	}

	public function setBreakOnFailedCreate(bool $break_on_failed_create): void
	{
		$this->break_on_failed_create = $break_on_failed_create;
	}

	public function setBreakOnFailedInsert(bool $break_on_failed_insert): void
	{
		$this->break_on_failed_insert = $break_on_failed_insert;
	}

	public function setMaxexectime(?int $maxexectime): void
	{
		$this->maxexectime = min(max(0, $maxexectime ?? 0), 3600);
	}

	public function setThrottle(?int $throttle): void
	{
		$this->throttle = min(max(0, $throttle ?? 0), 60000);
	}
}