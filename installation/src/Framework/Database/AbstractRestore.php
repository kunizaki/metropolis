<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Database;

use Akeeba\BRS\Framework\Container\Container;
use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Language\Language;
use Akeeba\BRS\Framework\Timer\Timer;
use Exception;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Throwable;

defined('_AKEEBA') or die();

/**
 * Abstract database restoration class.
 *
 * @since  10.0
 */
abstract class AbstractRestore implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	private const ENTITY_TYPES = ['table', 'view', 'procedure', 'function', 'trigger', 'event'];

	/**
	 * A list of error codes (numbers) which should not block cause the
	 * restoration to halt. Used for soft errors and warnings which do not cause
	 * problems with the restored site.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $allowedErrorCodes = [];

	/**
	 * A list of comment line delimiters. Lines starting with these strings are
	 * skipped during restoration.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $comment = [];

	/**
	 * A list of the part files of the database dump we are importing
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $partsMap = [];

	/**
	 * The total size of all database dump files
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $totalSize = 0;

	/**
	 * The part file currently being processed
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $curpart = null;

	/**
	 * The offset into the part file being processed
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $foffset = 0;

	/**
	 * The total size of the file being processed
	 *
	 * @var   false|int
	 * @since 10.0
	 */
	protected $filesize;

	/**
	 * The total size of all database dump files processed so far
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $runSize = 0;

	/**
	 * The file pointer to the SQL file currently being restored
	 *
	 * @var   resource
	 * @since 10.0
	 */
	protected $file = null;

	/**
	 * The filename of the SQL file currently being restored
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $filename = null;

	/**
	 * The starting line number of processing the current file
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $start = null;

	/**
	 * The Timer object used to guard against timeouts
	 *
	 * @var   Timer
	 * @since 10.0
	 */
	protected $timer = null;

	/**
	 * The database file key used to determine which dump we're restoring
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $dbkey = null;

	/**
	 * Cached copy of the up-to-date databases.json values of the database dump
	 * we are currently restoring.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $dbjsonValues = null;

	/**
	 * The database driver used to connect to this database
	 *
	 * @var   AbstractDriver
	 * @since 10.0
	 */
	protected $db = null;

	/**
	 * Total queries run so far
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $totalqueries = null;

	/**
	 * Line number in the current file being processed
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $linenumber = null;

	/**
	 * Number of queries run in this restoration step
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $queries = null;

	/**
	 * The full path to a log file which contains failed queries which were ignored
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $logFile;

	/**
	 * Should I halt the restoration when a CREATE query fails?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $breakOnFailedCreate = true;

	/**
	 * Should I halt the restoration when an INSERT (or other non-CREATE) query fails?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	protected $breakOnFailedInsert = true;

	/**
	 * How many SQL queries resulted in an error during the restoration
	 *
	 * @var   int
	 * @since 10.0
	 */
	protected $errorcount = 0;

	/**
	 * Marker denoting a new line has started
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected $marker = "\n";

	/**
	 * @var   int
	 * @since 10.0
	 */
	protected $totalsizeread;

	/**
	 * List of specific entities that we want to restore. Leave empty to restore all entities in the databse
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected $specific_entities = [];

	/**
	 * Am I inside a transaction?
	 *
	 * @var    bool
	 * @since  9.8.1
	 */
	protected $inTransaction = false;

	/**
	 * Constructor.
	 *
	 * @param   string     $dbkey         The databases.json key of the current database
	 * @param   array      $dbjsonValues  The databases.json configuration variables for the current database
	 * @param   Container  $container     Application container
	 *
	 * @throws  Exception
	 */
	public function __construct(ContainerInterface $container, string $dbkey, array $dbjsonValues)
	{
		$this->setContainer($container);
		$paths = $this->getContainer()->get('paths');

		$this->dbkey        = $dbkey;
		$this->dbjsonValues = $dbjsonValues;

		$this->populatePartsMap();

		$this->logFile             = $paths->get('tempinstall') . '/' .
		                             ($this->dbjsonValues['failed_query_log'] ??
		                              sprintf('failed_queries_%s.log', $this->sanitizeDBKey($dbkey)));
		$this->breakOnFailedCreate = $this->dbjsonValues['break_on_failed_create'] ?? true;
		$this->breakOnFailedInsert = $this->dbjsonValues['break_on_failed_insert'] ?? true;
		$this->marker              = $this->dbjsonValues['marker'] ?? "\n";
		$this->timer               = new Timer(
			$this->getContainer(),
			0,
			(int) ($this->dbjsonValues['maxexectime'] ?? 5),
			(int) ($this->dbjsonValues['runtimebias'] ?? 75)
		);
	}

	/**
	 * Destructor.
	 *
	 * Closes open handlers.
	 *
	 * @since   10.0
	 */
	public function __destruct()
	{
		if (($this->db instanceof AbstractDriver))
		{
			try
			{
				$this->db->disconnect();
			}
			catch (Exception $exc)
			{
				// Nothing. We just never want to fail when closing the
				// database connection.
			}
		}

		if (is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}
	}

	/**
	 * Setter to specify only a subset of entities that should be restored.
	 *
	 * @param   array  $specificEntities
	 *
	 * @since   10.0
	 */
	public function setSpecificEntities(array $specificEntities)
	{
		$this->specific_entities = $this->preProcessSpecificEntitiesArray($specificEntities);

		$this->setToStorage('specific_entities', $this->specific_entities);
		$this->getContainer()->get('session')->saveData();
	}

	/**
	 * Pre-process the specific entities array.
	 *
	 * If we have typed entities the incoming array will have a flat array of entries in the format `type.name`. I need
	 * to make this into a two-dimensional array.
	 *
	 * If I have non-typed entities I need to detect that, and return the flat list of entity names as-is. This is
	 * necessary to be able to use the new installer with older backups taken with Akeeba Backup 9.x.
	 *
	 * @param   array  $specificEntities
	 *
	 * @return  array|array[]
	 * @since   10.0
	 */
	private function preProcessSpecificEntitiesArray(array $specificEntities): array
	{
		// Typed entities list will be in the format `<type>.name`.
		$ret = array_combine(
			self::ENTITY_TYPES,
			array_fill(0, count(self::ENTITY_TYPES), [])
		);

		foreach ($specificEntities as $name)
		{
			// A name without a dot means that we have a legacy, non-typed list. Return it as-is.
			if (strpos($name, '.') === false)
			{
				return $specificEntities;
			}

			[$type, $entityName] = explode('.', $name, 2);

			// If the stuff before the first dot is not a known entity type we will assume it's a non-typed list.
			if (!in_array($type, self::ENTITY_TYPES))
			{
				return $specificEntities;
			}

			$ret[$type][] = $entityName;
		}

		return $ret;
	}

	/**
	 * Am I allowed to restore the named entity?
	 *
	 * @param   string  $entityName
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function shouldRestoreEntity(string $entityName, string $entityType = '*'): bool
	{
		/**
		 * Modern: if nothing is chosen, restore everything.
		 *
		 * This also covers the legacy use case where `$this->specific_entities === []`.
		 */
		if ($this->isSpecificEntitiesFunctionallyEmpty())
		{
			return true;
		}

		// Legacy: specific entity name explicitly allowed
		if (in_array($entityName, $this->specific_entities))
		{
			return true;
		}

		// Modern: specific entity type and name
		if (empty($this->specific_entities[$entityType] ?? []) || !is_array($this->specific_entities[$entityType]))
		{
			return false;
		}

		return in_array($entityName, $this->specific_entities[$entityType]);
	}

	/**
	 * Is `$this->specific_entities` functionally empty, i.e. the user selected nothing to restore?
	 *
	 * This covers two distinct use cases.
	 *
	 * A. LEGACY. In the legacy use case we only have entity names, without types. Therefore, if the user has selected
	 *    no database entities to restore, `$this->specific_entities` is an empty array.
	 *
	 * B. MODERN. In the modern use case we have both entity types, and entity names. The `$this->specific_entities`
	 *    array has a number of keys whose names can be found in self::ENTITY_TYPES, each one representing an entity
	 *    type such as tables, views, etc. Each of these keys contains an array of the named entities of this type
	 *    selected by the user. The array is _functionally_ empty when all of these keys contain an empty array,
	 *    meaning that the user has selected nothing.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	private function isSpecificEntitiesFunctionallyEmpty(): bool
	{
		// Degenerate case: the table is completely empty.
		if (empty($this->specific_entities))
		{
			return true;
		}

		/**
		 * Criterion 1: we must have a modern structure of specific database entities to restore.
		 *
		 * This requires that $this->specific_entities only has keys which can be found in self::ENTITY_TYPES.
		 *
		 * Why does this exist? Because if we have the legacy use case we will have one or more _numeric_ keys in the
		 * array, each one representing an entity name (without specifying its type). However, if this is the case,
		 * the array is NOT functionally empty, therefore I have to immediately return false.
		 */
		$unknownKeys = array_diff(array_keys($this->specific_entities), self::ENTITY_TYPES);

		if (!empty($unknownKeys))
		{
			return false;
		}

		/**
		 * Criterion 2: All specific entity type arrays are empty.
		 *
		 * If this is the case then array_filter against this table will yield an empty array. We use this instead of
		 * direct array iteration for performance reasons.
		 */
		return empty(array_filter($this->specific_entities));
	}

	/**
	 * Remove all cached information from the session storage
	 *
	 * @since   10.0
	 */
	public function removeInformationFromStorage(): void
	{
		$variables = [
			'start',
			'foffset',
			'totalqueries',
			'curpart',
			'partsmap',
			'totalsize',
			'runsize',
			'errorcount',
			'specific_entities',
		];

		$session = $this->container->get('session');

		foreach ($variables as $var)
		{
			$session->remove('restore.' . $this->dbkey . '.' . $var);
		}
	}

	/**
	 * Runs a restoration step and returns an array to be used in the response.
	 *
	 * @return  array
	 * @throws  Exception
	 * @since   10.0
	 */
	public function stepRestoration(): array
	{
		$parts = $this->getParam('parts', 1);

		$this->openFile();

		$this->linenumber    = $this->start;
		$this->totalsizeread = 0;
		$this->queries       = 0;

		// In the beginning of the restoration drop all tables, if the user has selected to do so.
		if (($this->curpart == 0) && ($this->foffset == 0))
		{
			$this->conditionallyDropAll();
		}

		try
		{
			$this->db->transactionStart();
			$this->inTransaction = true;
		}
		catch (Exception $e)
		{
		}

		while ($this->timer->getTimeLeft() > 0)
		{
			// Get the next query line
			try
			{
				$query = $this->readNextLine();
			}
			catch (Exception $exc)
			{
				if ($exc->getCode() == 200)
				{
					break;
				}
				elseif ($exc->getCode() == 201)
				{
					continue;
				}
			}

			// Process the query line, running drop/rename queries as necessary
			$this->processQueryLine($query);

			// Update variables
			$this->totalsizeread += strlen($query);
			$this->totalqueries++;
			$this->queries++;
			$query = "";
			$this->linenumber++;
		}

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

		// Get the current file position
		$current_foffset = ftell($this->file);

		if ($current_foffset === false)
		{
			if (is_resource($this->file))
			{
				@fclose($this->file);
				$this->file = null;
			}

			throw new Exception($this->getContainer()->get('language')->text('FRAMEWORK_DBRESTORE_ERR_CANTREADPOINTER'));
		}

		if (is_null($this->foffset))
		{
			$this->foffset = 0;
		}

		$bytes_in_step = $current_foffset - $this->foffset;
		$this->runSize = (is_null($this->runSize) ? 0 : $this->runSize) + $bytes_in_step;
		$this->foffset = $current_foffset;

		// Return statistics
		$bytes_togo = $this->totalSize - $this->runSize;

		// Check for global EOF
		if (($this->curpart >= ($parts - 1)) && feof($this->file))
		{
			$bytes_togo = 0;
		}

		// Save variables in storage
		$this->setToStorage('start', $this->start);
		$this->setToStorage('foffset', $this->foffset);
		$this->setToStorage('totalqueries', $this->totalqueries);
		$this->setToStorage('runsize', $this->runSize);
		$this->setToStorage('errorcount', $this->errorcount);

		if ($bytes_togo == 0)
		{
			// Clear stored variables if we're finished
			$lines_togo   = '0';
			$lines_tota   = $this->linenumber - 1;
			$queries_togo = '0';
			$queries_tota = $this->totalqueries;
			$this->removeInformationFromStorage();
		}

		$this->container->get('session')->saveData();

		// Calculate estimated time
		$bytesPerSecond = $bytes_in_step / $this->timer->getRunningTime();

		if ($bytesPerSecond <= 0.01)
		{
			$remainingSeconds = 120;
		}
		else
		{
			$remainingSeconds = round($bytes_togo / $bytesPerSecond, 0);
		}

		// Close the file if it is still open at this point
		if (!empty($this->file) && is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}

		// Return meaningful data
		return [
			'percent'          => round(100 * ($this->runSize / $this->totalSize), 1),
			'restored'         => $this->sizeformat($this->runSize),
			'total'            => $this->sizeformat($this->totalSize),
			'queries_restored' => $this->totalqueries,
			'errorcount'       => $this->errorcount,
			'errorlog'         => $this->getLogPath(),
			'current_line'     => $this->linenumber,
			'current_part'     => $this->curpart,
			'total_parts'      => $parts,
			'eta'              => $this->etaformat($remainingSeconds),
			'error'            => '',
			'done'             => ($bytes_togo == 0) ? '1' : '0',
		];
	}

	/**
	 * Returns the cached total size of the SQL dump.
	 *
	 * @param   boolean  $useUnits  Should I automatically figure out and use
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getTotalSize(bool $useUnits = false): string
	{
		$size = $this->totalSize;

		if ($useUnits)
		{
			$size = $this->sizeformat($size);
		}

		return $size;
	}

	/**
	 * Returns the timer used by this object.
	 *
	 * @return  Timer|null
	 * @since   10.0
	 */
	public function getTimer(): ?Timer
	{
		return $this->timer;
	}

	/**
	 * Remove the failed query log. You need to call this at the beginning of the restoration.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function removeLog(): void
	{
		if (empty($this->logFile))
		{
			return;
		}

		if (@file_exists($this->logFile))
		{
			@unlink($this->logFile);
		}
	}

	/**
	 * Returns the full filesystem path of the failed query log file.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getLogPath(): string
	{
		return $this->logFile;
	}

	/**
	 * Return a value from the session storage.
	 *
	 * @param   string  $var      The name of the variable
	 * @param   mixed   $default  The default value (null if omitted)
	 *
	 * @return  mixed  The variable's value
	 * @since   10.0
	 */
	protected function getFromStorage(string $var, $default = null)
	{
		$session = $this->container->get('session');

		return $session->get('restore.' . $this->dbkey . '.' . $var, $default);
	}

	/**
	 * Sets a value to the session storage
	 *
	 * @param   string  $var    The name of the variable
	 * @param   mixed   $value  The value to store
	 *
	 * @since   10.0
	 */
	protected function setToStorage(string $var, $value): void
	{
		$session = $this->container->get('session');

		$session->set('restore.' . $this->dbkey . '.' . $var, $value);
	}

	/**
	 * Gets a database configuration variable, as cached in the $dbjsonValues array
	 *
	 * @param   string  $key      The name of the variable to get
	 * @param   mixed   $default  Default value (null if skipped)
	 *
	 * @return  mixed  The configuration variable's value
	 * @since   10.0
	 */
	protected function getParam(string $key, $default = null)
	{
		if (is_array($this->dbjsonValues))
		{
			if (array_key_exists($key, $this->dbjsonValues))
			{
				return $this->dbjsonValues[$key];
			}

			return $default;
		}

		return $default;
	}

	/**
	 * Populates the map of SQL dump files to restore.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function populatePartsMap(): void
	{
		// Nothing to do if it's already populated, right?
		if (!empty($this->partsMap))
		{
			return;
		}

		// First, try to fetch from the session storage
		$this->totalSize         = $this->getFromStorage('totalsize', 0);
		$this->runSize           = $this->getFromStorage('runsize', 0);
		$this->partsMap          = $this->getFromStorage('partsmap', []);
		$this->curpart           = $this->getFromStorage('curpart', 0);
		$this->foffset           = $this->getFromStorage('foffset', 0);
		$this->start             = $this->getFromStorage('start', 0);
		$this->totalqueries      = $this->getFromStorage('totalqueries', 0);
		$this->errorcount        = $this->getFromStorage('errorcount', 0);
		$this->specific_entities = $this->recursiveToArray($this->getFromStorage('specific_entities', []));

		// If that didn't work try a full initalisation
		if (empty($this->partsMap))
		{
			$sqlfile = $this->dbjsonValues['sqlfile'];

			$parts = $this->getParam('parts', 1);

			$this->partsMap   = [];
			$path             = $this->getContainer()->get('paths')->get('installation') . '/sql';
			$this->totalSize  = 0;
			$this->runSize    = 0;
			$this->curpart    = 0;
			$this->foffset    = 0;
			$this->errorcount = 0;

			for ($index = 0; $index <= $parts; $index++)
			{
				if ($index == 0)
				{
					$basename = $sqlfile;
				}
				else
				{
					$basename = substr($sqlfile, 0, -4) . '.s' . sprintf('%02u', $index);
				}

				$file = $path . '/' . $basename;
				if (!file_exists($file))
				{
					$file = 'sql/' . $basename;
				}
				$filesize         = @filesize($file);
				$this->totalSize  += intval($filesize);
				$this->partsMap[] = $file;
			}

			$this->setToStorage('totalsize', $this->totalSize);
			$this->setToStorage('runsize', $this->runSize);
			$this->setToStorage('partsmap', $this->partsMap);
			$this->setToStorage('curpart', $this->curpart);
			$this->setToStorage('foffset', $this->foffset);
			$this->setToStorage('start', $this->start);
			$this->setToStorage('totalqueries', $this->totalqueries);
			$this->setToStorage('errorcount', $this->errorcount);

			$this->container->get('session')->saveData();
		}
	}

	/**
	 * Proceeds to opening the next SQL part file
	 *
	 * @return  bool  True on success
	 * @throws  Exception
	 * @since   10.0
	 */
	protected function getNextFile(): bool
	{
		$parts = $this->getParam('parts', 1);

		if ($this->curpart >= ($parts - 1))
		{
			return false;
		}

		$this->curpart++;
		$this->foffset = 0;

		$this->setToStorage('curpart', $this->curpart);
		$this->setToStorage('foffset', $this->foffset);

		$this->container->get('session')->saveData();

		// Close an already open file (if one was indeed already open)
		if (!empty($this->file) && is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}

		return $this->openFile();
	}

	/**
	 * Opens the SQL part file whose ID is specified in the $curpart variable.
	 *
	 * It also updates the $file, $start and $foffset variables.
	 *
	 * @return  bool  True on success
	 * @throws  Exception
	 * @since   10.0
	 */
	protected function openFile(): bool
	{
		/** @var Language $lang */
		$lang = $this->getContainer()->get('language');

		// If there is an already open file, close it before proceeding
		if (!empty($this->file) && is_resource($this->file))
		{
			@fclose($this->file);
			$this->file = null;
		}

		if (!is_numeric($this->curpart))
		{
			$this->curpart = 0;
		}

		$this->filename = $this->partsMap[$this->curpart];

		if (!$this->file = @fopen($this->filename, "r"))
		{
			throw new Exception($lang->sprintf('FRAMEWORK_DBRESTORE_ERR_CANTOPENDUMPFILE', $this->filename));
		}

		// Get the file size
		if (fseek($this->file, 0, SEEK_END) == 0)
		{
			$this->filesize = ftell($this->file);
		}
		else
		{
			throw new Exception($lang->text('FRAMEWORK_DBRESTORE_ERR_UNKNOWNFILESIZE'));
		}

		// Check start and foffset are numeric values
		if (!is_numeric($this->start) || !is_numeric($this->foffset))
		{
			throw new Exception($lang->text('FRAMEWORK_DBRESTORE_ERR_INVALIDPARAMETERS'));
		}

		$this->start   = floor($this->start);
		$this->foffset = floor($this->foffset);

		// Check $foffset upon $filesize
		if ($this->foffset > $this->filesize)
		{
			throw new Exception($lang->text('FRAMEWORK_DBRESTORE_ERR_AFTEREOF'));
		}

		// Set file pointer to $foffset
		if (fseek($this->file, $this->foffset) != 0)
		{
			throw new Exception($lang->text('FRAMEWORK_DBRESTORE_ERR_CANTSETOFFSET'));
		}

		return true;
	}

	/**
	 * Returns the instance of the database driver, creating it if it doesn't exist.
	 *
	 * @param   bool  $selectDatabase  Should I change the active database?
	 *
	 * @return  AbstractDriver
	 * @since   10.0
	 */
	protected function getDatabase(bool $selectDatabase = true): AbstractDriver
	{
		if (!is_object($this->db))
		{
			$options = [
				'driver'   => $this->dbjsonValues['dbtype'],
				'database' => $this->dbjsonValues['dbname'],
				'select'   => 0,
				'host'     => $this->dbjsonValues['dbhost'],
				'user'     => $this->dbjsonValues['dbuser'],
				'password' => $this->dbjsonValues['dbpass'],
				'prefix'   => $this->dbjsonValues['prefix'],
				'ssl'      => [
					'enable'             => (bool) $this->dbjsonValues['dbencryption'],
					'cipher'             => $this->dbjsonValues['dbsslcipher'] ?: null,
					'ca'                 => $this->dbjsonValues['dbsslca'] ?: null,
					'key'                => $this->dbjsonValues['dbsslkey'] ?: null,
					'cert'               => $this->dbjsonValues['dbsslcert'] ?: null,
					'verify_server_cert' => (bool) $this->dbjsonValues['dbsslverifyservercert'],
				],
			];

			if (!$selectDatabase)
			{
				unset($options['database']);
			}

			try
			{
				$this->db = $this->getContainer()->get('db')->driver(strtolower($options['driver']), $options);;
				$this->db->setUTF();
			}
			catch (RuntimeException $e)
			{
				throw new RuntimeException(sprintf('Unable to connect to the Database: %s', $e->getMessage()));
			}
		}

		return $this->db;
	}

	/**
	 * Executes a SQL statement, ignoring errors in the $allowedErrorCodes list.
	 *
	 * @param   string  $sql  The SQL statement to execute
	 *
	 * @return  mixed  A database cursor on success, false on failure
	 * @throws  Exception
	 * @since   10.0
	 */
	protected function execute($sql)
	{
		$db = $this->getDatabase();

		try
		{
			$db->setQuery($sql);
			$result = $db->execute();
		}
		catch (Exception $exc)
		{
			$result = false;

			// Let's replace the prefix with the current one so users can easily copy/paste the queries
			$sql = $db->replacePrefix($sql);

			$this->handleFailedQuery($sql, $exc);
		}

		return $result;
	}

	/**
	 * Read the next line from the database dump
	 *
	 * @return  string  The query string
	 * @throws  Exception
	 * @since   10.0
	 */
	protected function readNextLine(): string
	{
		$parts = $this->getParam('parts', 1);

		$query = "";

		while (is_resource($this->file) && !feof($this->file) && (strpos($query, $this->marker) === false))
		{
			$query .= fgets($this->file, DATA_CHUNK_LENGTH);
		}

		// An empty query is most likely EOF. Are we done or should I skip to the next file?
		if (empty($query))
		{
			if ($this->curpart >= ($parts - 1))
			{
				throw new Exception('All done', 200);
			}

			// Register the bytes read
			$current_foffset = @ftell($this->file);

			if (is_null($this->foffset))
			{
				$this->foffset = 0;
			}

			$this->runSize = (is_null($this->runSize) ? 0 : $this->runSize) + ($current_foffset - $this->foffset);

			// Get the next file
			$this->getNextFile();

			// Rerun the fetcher
			throw new Exception('Continue', 201);
		}

		/**
		 * If we have not reached EOF and the query does not end with our marker we have read too much data. We need to
		 * locate the marker, roll back the file pointer to this point and only keep our query up to the marker.
		 */
		if (!feof($this->file) && substr($query, strlen($this->marker)) != $this->marker)
		{
			$rollback = strlen($query) - strpos($query, $this->marker);
			$query    = substr($query, 0, -$rollback);

			fseek($this->file, -$rollback + 1, SEEK_CUR);
		}

		// Handle DOS linebreaks
		$query = str_replace("\r\n", "\n", $query);
		$query = str_replace("\r", "\n", $query);

		// Skip comments and blank lines only if NOT in parents
		$skipline = false;
		reset($this->comment);

		foreach ($this->comment as $comment_value)
		{
			if (trim($query) == "" || strpos($query, $comment_value) === 0)
			{
				$skipline = true;
				break;
			}
		}

		if ($skipline)
		{
			$this->linenumber++;
			throw new Exception('Continue', 201);
		}

		$query = trim($query, " \n");
		$query = rtrim($query, ';');

		return $query;
	}

	/**
	 * Processes the query line in the best way each restoration engine sees
	 * fit. This method is supposed to take care of backing up and dropping
	 * tables, changing table collation if requested and converting INSERT to
	 * REPLACE if requested. It is also supposed to execute $query against the
	 * database, replacing the metaprefix #__ with the real prefix.
	 *
	 * @param   string  $query  The query to process
	 *
	 * @return  bool  True on success
	 * @throws  Exception
	 * @since   10.0
	 */
	abstract protected function processQueryLine(string $query): bool;

	/**
	 * Drops tables etc before restoration.
	 *
	 * Obviously only has effect when the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropAll()
	{
		$existing = $this->dbjsonValues['existing'];

		if (!in_array($existing, ['dropall', 'dropprefix']))
		{
			return;
		}

		try
		{
			$this->conditionallyDropTables();
		}
		catch (Exception $e)
		{
			// It doesn't matter if it fails
		}

		try
		{
			$this->conditionallyDropViews();
		}
		catch (Exception $e)
		{
			// It doesn't matter if it fails
		}

		try
		{
			$this->conditionallyDropTriggers();
		}
		catch (Exception $e)
		{
			// It doesn't matter if it fails
		}

		try
		{
			$this->conditionallyDropFunctions();
		}
		catch (Exception $e)
		{
			// It doesn't matter if it fails
		}

		try
		{
			$this->conditionallyDropProcedures();
		}
		catch (Exception $e)
		{
			// It doesn't matter if it fails
		}
	}

	/**
	 * Drops tables before restoration if the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropTables(): void
	{
		// Implement this in child classes
	}

	/**
	 * Drops views before restoration if the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropViews(): void
	{
		// Implement this in child classes
	}

	/**
	 * Drops triggers before restoration if the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropTriggers(): void
	{
		// Implement this in child classes
	}

	/**
	 * Drops functions before restoration if the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropFunctions(): void
	{
		// Implement this in child classes
	}

	/**
	 * Drops procedures before restoration if the 'existing' option is set to 'dropprefix' or 'dropall'.
	 *
	 * @return  void
	 * @since   10.0
	 */
	protected function conditionallyDropProcedures(): void
	{
		// Implement this in child classes
	}

	/**
	 * Format the ETA as a human-readable string.
	 *
	 * @param   int     $raw        The raw time
	 * @param   string  $measureBy  Unit of measurement (s, m, h, d, y). Empty for auto-detection.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function etaformat(int $raw, string $measureBy = ''): string
	{
		$clear = abs($raw);

		$calcNum = [
			['s', 60],
			['m', 60 * 60],
			['h', 60 * 60 * 60],
			['d', 60 * 60 * 60 * 24],
			['y', 60 * 60 * 60 * 24 * 365],
		];

		$calc = [
			's' => [1, 'second'],
			'm' => [60, 'minute'],
			'h' => [60 * 60, 'hour'],
			'd' => [60 * 60 * 24, 'day'],
			'y' => [60 * 60 * 24 * 365, 'year'],
		];

		if ($measureBy == '')
		{
			$usemeasure = 's';

			for ($i = 0; $i < count($calcNum); $i++)
			{
				if ($clear <= $calcNum[$i][1])
				{
					$usemeasure = $calcNum[$i][0];
					$i          = count($calcNum);
				}
			}
		}
		else
		{
			$usemeasure = $measureBy;
		}

		$datedifference = floor($clear / $calc[$usemeasure][0]);

		if ($datedifference == 1)
		{
			return $datedifference . ' ' . $calc[$usemeasure][1];
		}
		else
		{
			return $datedifference . ' ' . $calc[$usemeasure][1] . 's';
		}
	}

	/**
	 * Format a size as a human-radable string.
	 *
	 * @param   int  $size  The size in bytes
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function sizeformat(int $size): string
	{
		if ($size < 0)
		{
			return 0;
		}

		$unit = ['b', 'KB', 'MB', 'GB', 'TB', 'PB'];
		$i    = floor(log($size, 1024));

		if (($i < 0) || ($i > 5))
		{
			$i = 0;
		}

		return @round($size / pow(1024, ($i)), 2) . ' ' . $unit[$i];
	}

	/**
	 * Replace non-alphanumeric characters in a string with underscores.
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 * @since   10.0
	 */
	private function sanitizeDBKey(string $string): string
	{
		return trim(preg_replace('/(\s|[^A-Za-z0-9_])+/', '_', $string), '_');
	}

	/**
	 * Add a failed query to the failed query log file.
	 *
	 * @param   string  $sql  The failed database query to log
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function logQuery(string $sql): void
	{
		if (empty($this->logFile))
		{
			return;
		}

		$fp = @fopen($this->logFile, 'a');

		if ($fp === false)
		{
			return;
		}

		$sql = rtrim($sql, "\n") . ";\n\n";

		@fwrite($fp, $sql);
		@fclose($fp);
	}

	/**
	 * Handle a query which failed to execute
	 *
	 * @param   string     $sql  The failed query
	 * @param   Throwable  $exc  The exception generated by the database driver
	 *
	 * @return  void
	 *
	 * @throws  Throwable
	 */
	private function handleFailedQuery(string $sql, Throwable $exc): void
	{
		// If the database error code is within the list of ignored codes we do nothing
		if (in_array($exc->getCode(), $this->allowedErrorCodes))
		{
			return;
		}

		// Increase the SQL error counter
		$this->errorcount++;

		// Is this a CREATE query?
		$isCreateQuery = (substr($sql, 0, 7) == 'CREATE ');

		// Should I throw an exception (halt the restoration) for this failed query?
		$throwException = $this->breakOnFailedInsert;

		if ($isCreateQuery)
		{
			$throwException = $this->breakOnFailedCreate;
		}

		// Log the failed query. If writing to the log fails nothing bad happens.
		$this->logQuery($sql);

		// If I am not supposed to halt the restoration stop here.
		if (!$throwException)
		{
			return;
		}

		// Format the error message in a human readable way and throw it again
		/** @var Language $lang */
		$lang = $this->getContainer()->get('language');
		$message = '<h2>' . $lang->sprintf('FRAMEWORK_DBRESTORE_ERR_ERRORATLINE', $this->linenumber) . '</h2>' . "\n";
		$message .= '<p>' . $lang->text('FRAMEWORK_DBRESTORE_ERR_MYSQLERROR') . '</p>' . "\n";
		$message .= '<code>ErrNo #' . htmlspecialchars($exc->getCode()) . '</code>' . "\n";
		$message .= '<pre>' . htmlspecialchars($exc->getMessage()) . '</pre>' . "\n";
		$message .= '<p>' . $lang->text('FRAMEWORK_DBRESTORE_ERR_RAWQUERY') . '</p>' . "\n";
		$message .= '<pre>' . htmlspecialchars($sql) . '</pre>' . "\n";

		throw new Exception($message);
	}

	/**
	 * Recursively converts a given source into an array.
	 *
	 * @param   mixed  $source  The input to be converted, which can be a scalar, null, array, or object.
	 *
	 * @return  mixed  Returns the converted array or retains the original scalar/null value.
	 * @since   10.0
	 */
	private function recursiveToArray($source)
	{
		if (is_scalar($source) || is_null($source))
		{
			return $source;
		}

		if (is_object($source))
		{
			return $this->recursiveToArray((array) $source);
		}

		if (!is_array($source))
		{
			return null;
		}

		return array_map([$this, 'recursiveToArray'], $source);
	}
}

if (!defined('DATA_CHUNK_LENGTH'))
{
	define('DATA_CHUNK_LENGTH', 65536);            // How many bytes to read per step
	define('MAX_QUERY_LINES', 300);            // How many lines may be considered to be one query (except text lines)
}
