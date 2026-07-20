<?php
/**
 * @package   akeebabackup
 * @copyright Copyright 2006-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\AkeebaBackup\Site\Model\Json;

// Protect from unauthorized access
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

defined('_JEXEC') || die();

/**
 * Interface for JSON API tasks
 */
interface TaskInterface
{
	/**
	 * Public constructor
	 *
	 * @param   MVCFactoryInterface  $factory  The container of the component we belong to
	 */
	public function __construct(MVCFactoryInterface $factory);

	/**
	 * Return the JSON API task's name ("method" name). Remote clients will use it to call us.
	 *
	 * @return  string
	 */
	public function getMethodName();

	/**
	 * Execute the JSON API task
	 *
	 * @param   array  $parameters  The parameters to this task
	 *
	 * @return  mixed
	 *
	 * @throws  \RuntimeException  In case of an error
	 */
	public function execute(array $parameters = []);
}
