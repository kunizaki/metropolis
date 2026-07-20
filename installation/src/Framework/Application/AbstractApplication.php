<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Application;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Document\AbstractDocument;
use Akeeba\BRS\Framework\Document\DocumentInterface;
use Psr\Container\ContainerInterface;

/**
 * The application object
 *
 * @since  10.0
 */
#[\AllowDynamicProperties]
abstract class AbstractApplication implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/** @var array The application message queue */
	public $messageQueue = [];

	/** @var string The name (alias) of the application */
	protected $name = null;

	/** @var array The configuration parameters of the application */
	protected $config = [];

	/** @var string The name of the template's directory */
	protected $template = null;

	/**
	 * The application's document
	 *
	 * @var   DocumentInterface|null
	 * @since 10.0
	 */
	private $document = null;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The application container
	 * @param   array               $config     Application configuration
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $config = [])
	{
		$this->setContainer($container);

		$this->name = $container['application_name'] ?? $this->getName();
		$this->setTemplate($config['template'] ?? 'default');
	}

	/**
	 * Initialises the application
	 *
	 * @since  10.0
	 */
	abstract public function initialise();

	/**
	 * Dispatches the application
	 *
	 * @since   10.0
	 */
	public function dispatch(): void
	{
		@ob_start();

		$this->getContainer()->get('dispatcher')->dispatch();
		$this->getDocument()->setBuffer(@ob_get_clean());
	}

	/**
	 * Renders the application's document
	 *
	 * @since  10.0
	 */
	public function render(): void
	{
		$this->getDocument()->render();
	}

	/**
	 * Close the application
	 *
	 * @param   int  $code  Exit code
	 *
	 * @return  never-return
	 * @since   10.0
	 */
	public function close(int $code = 0)
	{
		exit($code);
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type. Default is info.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function enqueueMessage(string $msg, string $type = 'info')
	{
		// For empty queue, if messages exists in the session, enqueue them first.
		if (!count($this->messageQueue))
		{
			$sessionQueue = $this->container->get('session')->get('application.queue') ?: [];

			if (is_array($sessionQueue) && count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;

				$this->container->get('session')->remove('application.queue');
			}
		}

		// Enqueue the message.
		$this->messageQueue[] = ['message' => $msg, 'type' => strtolower($type)];
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 * @since   10.0
	 */
	public function getMessageQueue(): array
	{
		// For empty queue, if messages exists in the session, enqueue them.
		if (!count($this->messageQueue))
		{
			$sessionQueue = $this->container->get('session')->get('application.queue', null);

			if (is_array($sessionQueue) && count($sessionQueue))
			{
				$this->messageQueue = $sessionQueue;

				$this->container->get('session')->remove('application.queue');
			}
		}

		return $this->messageQueue;
	}

	/**
	 * Get the message queue for a specific message type.
	 *
	 * @param   string  $type
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getMessageQueueFor(string $type = 'info'): array
	{
		$ret          = [];
		$messageQueue = $this->getMessageQueue();

		if (count($messageQueue))
		{
			foreach ($messageQueue as $message)
			{
				$message = is_object($message) ? $message : (object) $message;

				if ($message->type == $type)
				{
					$ret[] = $message->message;
				}
			}
		}

		return $ret;
	}

	/**
	 * Redirect to another URL.
	 *
	 * Optionally enqueues a message in the system message queue (which will be displayed
	 * the next time a page is loaded) using the enqueueMessage method. If the headers have
	 * not been sent the redirect will be accomplished using a "301 Moved Permanently"
	 * code in the header pointing to the new location. If the headers have already been
	 * sent this will be accomplished using a JavaScript statement.
	 *
	 * @param   string       $url      The URL to redirect to. Can only be http/https URL
	 * @param   string|null  $msg      An optional message to display on redirect.
	 * @param   string|null  $msgType  An optional message type. Defaults to message.
	 * @param   boolean      $moved    True if the page is 301 Permanently Moved, otherwise 303 See Other is assumed.
	 *
	 * @return  never-return
	 */
	public function redirect(string $url, ?string $msg = '', ?string $msgType = 'info', bool $moved = false)
	{
		// Check for relative internal links.
		if (preg_match('#^index\.php#', $url))
		{
			$url = $this->getContainer()->get('uri')->base() . $url;
		}

		// Strip out any line breaks.
		$url = preg_split("/[\r\n]/", $url);
		$url = $url[0];

		// Redirections must start with http
		if (!preg_match('#^http#i', $url))
		{
			$uri    = $this->getContainer()->get('uri')->instance();
			$prefix = $uri->toString(['scheme', 'user', 'pass', 'host', 'port']);

			if ($url[0] == '/')
			{
				$url = $prefix . $url;
			}
			else
			{
				$parts = explode('/', $uri->toString(['path']));
				array_pop($parts);
				$path = implode('/', $parts) . '/';
				$url  = $prefix . $path . $url;
			}
		}

		// If the message exists, enqueue it.
		if (trim($msg ?? ''))
		{
			$this->enqueueMessage($msg, $msgType ?: 'info');
		}

		// Persist messages if they exist.
		if (count($this->messageQueue))
		{
			$this->container->get('session')->set('application.queue', $this->messageQueue);
			$this->container->get('session')->saveData();
		}

		// If the headers have been sent, then we cannot send an additional location header
		// so we will output a javascript redirect statement.
		if (headers_sent())
		{
			echo "<script>document.location.href='" . htmlspecialchars($url) . "';</script>\n";

			$this->close();
		}

		header($moved ? 'HTTP/1.1 301 Moved Permanently' : 'HTTP/1.1 303 See other');
		header('Location: ' . $url);
		header('Content-Type: text/html; charset=utf-8');

		$this->close();
	}

	/**
	 * Creates and returns the document object
	 *
	 * @return  DocumentInterface
	 * @since   10.0
	 */
	public function getDocument(): DocumentInterface
	{
		if (!is_null($this->document))
		{
			return $this->document;
		}

		$type  = $this->getContainer()->get('input')->getCmd('format', 'html');
		$parts = explode('\\', AbstractDocument::class);

		array_pop($parts);

		$parts[]   = ucfirst(strtolower($type));
		$className = implode('\\', $parts);

		if (!class_exists($className))
		{
			throw new \RuntimeException(sprintf('No document class found for "%s" document type', $type));
		}

		return $this->document = new $className($this->getContainer());

	}

	/**
	 * Gets the name of the application.
	 *
	 * @return  string  The application name, all lowercase
	 * @since   10.0
	 */
	public function getName()
	{
		if (!empty($this->name))
		{
			return $this->name;
		}

		// The classname will be in the format Akeeba\BRS\Application\Application or Akeeba\BRS\Application.
		$parts = explode('\\', get_class($this));
		array_pop($parts);

		if (end($parts) == 'Application')
		{
			array_pop($parts);
		}

		return $this->name = end($parts);
	}

	/**
	 * Returns the application template's name.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getTemplate(): string
	{
		return $this->template;
	}

	/**
	 * Set the application template name.
	 *
	 * @param   string|null  $template
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setTemplate(?string $template = null): void
	{
		$this->template = $template ?? 'default';
	}
}