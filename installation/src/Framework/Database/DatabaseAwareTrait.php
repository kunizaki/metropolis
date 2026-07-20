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
 * Trait to implement the DatabaseAwareInterface
 *
 * @since  10.0
 */
trait DatabaseAwareTrait
{
	/**
	 * The database connection known to this object
	 *
	 * @var   AbstractDriver
	 * @since 10.0
	 */
	protected $db;

	/**
	 * Set the database driver object
	 *
	 * @param   AbstractDriver   $db
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setDriver(AbstractDriver $db): void
	{
		$this->db = $db;
	}

	/**
	 * Return the database driver object
	 *
	 * @return  AbstractDriver
	 * @since   10.0
	 */
	public function getDbo(): AbstractDriver
	{
		return $this->db;
	}
}