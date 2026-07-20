<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Ftp;

use Akeeba\BRS\Framework\Buffer\Buffer;
use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Exception;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class Ftp implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Initialised instances.
	 *
	 * @var   array<self>
	 * @since 10.0
	 */
	protected static $instances = [];

	/**
	 * Socket resource
	 *
	 * @var   resource
	 * @since 10.0
	 */
	private $connection = null;

	/**
	 * Timeout limit, in seconds.
	 *
	 * @var    int
	 * @since  10.0
	 */
	private $timeout = 15;

	/**
	 * Transfer Type
	 *
	 * @var   int
	 * @since 10.0
	 */
	private $type = null;

	/**
	 * Native OS Type
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $os = null;

	/**
	 * File extensions to always transfer as ASCII.
	 *
	 * @var   array
	 * @since 10.0
	 */
	private $autoAscii = [
		"asp",
		"bat",
		"c",
		"cpp",
		"csv",
		"h",
		"htm",
		"html",
		"shtml",
		"ini",
		"inc",
		"log",
		"php",
		"php3",
		"pl",
		"perl",
		"sh",
		"sql",
		"txt",
		"xhtml",
		"xml",
	];

	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container  The application container
	 * @param   array               $options    The associative array of options to set.
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $options = [])
	{
		self::initFtp();

		$this->setContainer($container);

		// If default transfer type is not set, set it to autoascii detect
		if (!isset($options['type']))
		{
			$options['type'] = FTP_BINARY;
		}
		$this->setOptions($options);

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			$this->os = 'WIN';
		}
		elseif (strtoupper(substr(PHP_OS, 0, 3)) === 'MAC')
		{
			$this->os = 'MAC';
		}
		else
		{
			$this->os = 'UNIX';
		}
	}

	/**
	 * Make sure FTP support is initialised.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public static function initFtp(): void
	{
		static $initialised = false;

		if ($initialised)
		{
			return;
		}

		$initialised = true;

		// Make sure the buffer:// stream handler is registered.
		class_exists(Buffer::class, true);

		// Error codes:
		if (!defined('FTP_ERROR_CANNOTCONNECT'))
		{
			define('FTP_ERROR_CANNOTCONNECT', 30); // Unable to connect to host
			define('FTP_ERROR_NOTCONENCTED', 31); // Not connected
			define('FTP_ERROR_CANTSENDCOMMAND', 32); // Unable to send command to server
			define('FTP_ERROR_BADUSERNAME', 33); // Bad username
			define('FTP_ERROR_BADPASSWORD', 34); // Bad password
			define('FTP_ERROR_BADRESPONSE', 35); // Bad password
			define('FTP_ERROR_PASVFAILED', 36); // Passive mode failed
			define('FTP_ERROR_DATATRANSFER', 37); // Data transfer error
			define('FTP_ERROR_LOCALFS', 38); // Local filesystem error
			define('FTP_ERROR_SOFT', 39); // Miscellaneous FTP issue (soft error)
		}

		if (!defined('CRLF'))
		{
			define('CRLF', "\r\n");
		}
		if (!defined("FTP_AUTOASCII"))
		{
			define("FTP_AUTOASCII", -1);
		}
		if (!defined("FTP_BINARY"))
		{
			define("FTP_BINARY", 1);
		}
		if (!defined("FTP_ASCII"))
		{
			define("FTP_ASCII", 0);
		}

		// Is FTP extension loaded?  If not try to load it
		if (!extension_loaded('ftp') && function_exists('dl'))
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
			{
				@ dl('php_ftp.dll');
			}
			else
			{
				@ dl('ftp.so');
			}
		}
	}

	/**
	 * Singleton implementation for the FTP connector.
	 *
	 * You may optionally specify a username and password in the parameters. If you do so, you may not login() again
	 * with different credentials using the same object. If you do not use this option, you must quit() the current
	 * connection when you are done, to free it for use by others.
	 *
	 * @param   ContainerInterface  $container  The application container.
	 * @param   string              $host       Host to connect to.
	 * @param   int                 $port       Port to connect to.
	 * @param   array|null          $options    Array with type and timeout options.
	 * @param   string|null         $user       Username to use for the connection.
	 * @param   string|null         $pass       Password to use for the connection.
	 *
	 * @return  self    The FTP Client object.
	 * @throws Exception
	 * @since   10.0
	 */
	public static function getInstance(
		ContainerInterface $container, string $host = '127.0.0.1', int $port = 21, ?array $options = null,
		?string $user = null, ?string $pass = null
	): Ftp
	{
		$signature = $user . ':' . $pass . '@' . $host . ":" . $port;

		// Create a new instance, or set the options of an existing one
		if (!isset(self::$instances[$signature]) || !is_object(self::$instances[$signature]))
		{
			self::$instances[$signature] = new self($container, $options);
		}
		else
		{
			self::$instances[$signature]->setOptions($options);
		}

		// Connect to the server, and login, if requested
		if (!self::$instances[$signature]->isConnected())
		{
			$return = self::$instances[$signature]->connect($host, $port);

			if ($return && $user !== null && $pass !== null)
			{
				self::$instances[$signature]->login($user, $pass);
			}
		}

		return self::$instances[$signature];
	}

	/**
	 * Destructor
	 *
	 * Closes an existing connection, if we have one.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __destruct()
	{
		if (!is_resource($this->connection))
		{
			return;
		}

		$this->quit();
	}

	/**
	 * Set client options.
	 *
	 * @param   array  $options  The associative array of options to set.
	 *
	 * @return  bool  True if successful
	 * @since   10.0
	 */
	public function setOptions(array $options): bool
	{
		if (isset($options['type']))
		{
			$this->type = $options['type'];
		}

		if (isset($options['timeout']))
		{
			$this->timeout = $options['timeout'];
		}

		return true;
	}

	/**
	 * Method to connect to a FTP server
	 *
	 * @param   string  $host  Host to connect to [Default: 127.0.0.1].
	 * @param   int     $port  Port to connect on [Default: port 21].
	 *
	 * @return  bool  True if successful
	 * @throws  Exception  When a connection error occurs.
	 * @since   10.0
	 */
	public function connect(string $host = '127.0.0.1', int $port = 21): bool
	{
		// Initialise variables.
		$errno = null;
		$err   = null;

		// If already connected, return
		if (is_resource($this->connection))
		{
			return true;
		}

		$this->connection = @ftp_connect($host, $port, $this->timeout);

		if ($this->connection === false)
		{
			throw new Exception(
				$this->getContainer()->get('language')->sprintf('ANGI_CLIENT_ERROR_AFTP_NO_CONNECT', $host, $port), 30
			);
		}

		// Set the timeout for this connection
		ftp_set_option($this->connection, FTP_TIMEOUT_SEC, $this->timeout);

		return true;
	}

	/**
	 * Method to determine if the object is connected to an FTP server.
	 *
	 * @return  bool  True if connected
	 * @since   10.0
	 */
	public function isConnected(): bool
	{
		return is_resource($this->connection);
	}

	/**
	 * Method to log into a server once connected.
	 *
	 * @param   string  $user  Username for logging into the server.
	 * @param   string  $pass  Password for logging into the server.
	 *
	 * @return  bool  True if successful
	 * @throws Exception  When a login error occurs.
	 * @since   10.0
	 */
	public function login(string $user = 'anonymous', string $pass = 'nobody@example.com'): bool
	{
		if (@ftp_login($this->connection, $user, $pass) === false)
		{
			throw new Exception($this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_NOLOGIN'), 30);
		}

		return true;
	}

	/**
	 * Method to quit and close the connection.
	 *
	 * @return  bool  Always true.
	 * @since   10.0
	 */
	public function quit(): bool
	{
		// If native FTP support is enabled lets use it...
		@ftp_close($this->connection);

		$this->connection = null;

		return true;
	}

	/**
	 * Method to change the current working directory on the FTP server
	 *
	 * @param   string  $path  Path to change into on the server
	 *
	 * @return  bool True if successful
	 * @throws Exception When an FTP error occurs.
	 * @since   10.0
	 */
	public function chdir(string $path): bool
	{
		if (@ftp_chdir($this->connection, $path) === false)
		{
			throw new Exception(
				$this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_CHDIR_BAD_RESPONSE'), 35
			);
		}

		return true;
	}

	/**
	 * Method to change the permissions of the given path on the FTP server.
	 *
	 * @param   string      $path  Path to change permissions on.
	 * @param   string|int  $mode  Octal permissions to set, e.g. '0123', 0123 or 345 (string or integer)
	 *
	 * @return  boolean  True if successful
	 * @throws Exception When an FTP error occurs.
	 * @since   10.0
	 */
	public function chmod(string $path, $mode): bool
	{
		// If no filename is given, we assume the current directory is the target
		if ($path == '')
		{
			$path = '.';
		}

		// Convert the mode to a string
		if (is_int($mode))
		{
			$mode = decoct($mode);
		}

		// If native FTP support is enabled let's use it...
		if (@ftp_site($this->connection, 'CHMOD ' . $mode . ' ' . $path) === false)
		{
			if ($this->os != 'WIN')
			{
				throw new Exception(
					$this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_CHMOD_BAD_RESPONSE'), 35
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to delete a path on the FTP server.
	 *
	 * @param   string  $path  Path to delete
	 *
	 * @return  bool  True if successful
	 * @throws Exception When an FTP error occurs.
	 * @since   10.0
	 */
	public function delete(string $path): bool
	{
		// If native FTP support is enabled let's use it...
		if (@ftp_delete($this->connection, $path) === false)
		{
			if (@ftp_rmdir($this->connection, $path) === false)
			{
				throw new Exception(
					$this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_DELETE_BAD_RESPONSE'), 35
				);
			}
		}

		return true;
	}

	/**
	 * Method to write a string to the FTP server
	 *
	 * @param   string  $remote  FTP path to file to write to
	 * @param   string  $buffer  Contents to write to the FTP server
	 *
	 * @return  bool  True if successful
	 * @throws Exception When an FTP error occurs.
	 * @since   10.0
	 */
	public function write(string $remote, string $buffer): bool
	{
		// Determine file type
		$mode = $this->findMode($remote);

		// Turn passive mode on
		if (@ftp_pasv($this->connection, true) === false)
		{
			throw new Exception(
				$this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_WRITE_PASSIVE'), 36
			);
		}

		$tmp = fopen('buffer://tmp', 'r+');
		fwrite($tmp, $buffer);
		rewind($tmp);

		if (@ftp_fput($this->connection, $remote, $tmp, $mode) === false)
		{
			fclose($tmp);

			throw new Exception(
				$this->getContainer()->get('language')->text('ANGI_CLIENT_ERROR_AFTP_WRITE_BAD_RESPONSE'), 35
			);
		}

		fclose($tmp);

		return true;
	}

	/**
	 * Method to find out the correct transfer mode for a specific file
	 *
	 * @param   string  $fileName  Name of the file
	 *
	 * @return  int Transfer-mode for this filetype [FTP_ASCII|FTP_BINARY]
	 * @since   10.0
	 */
	protected function findMode(string $fileName): int
	{
		if ($this->type == FTP_AUTOASCII)
		{
			$dot = strrpos($fileName, '.') + 1;
			$ext = substr($fileName, $dot);

			if (in_array($ext, $this->autoAscii))
			{
				$mode = FTP_ASCII;
			}
			else
			{
				$mode = FTP_BINARY;
			}
		}
		elseif ($this->type == FTP_ASCII)
		{
			$mode = FTP_ASCII;
		}
		else
		{
			$mode = FTP_BINARY;
		}

		return $mode;
	}
}