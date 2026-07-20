<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Container;

use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Interface for objects using a PSR-11 container.
 *
 * @since  10.0
 */
interface ContainerAwareInterface
{
	/**
	 * Set the PSR-11 container used by this object.
	 *
	 * @param   ContainerInterface  $container  The container object
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setContainer(ContainerInterface $container): void;

	/**
	 * Get the PSR-11 container used by this object.
	 *
	 * @return  ContainerInterface
	 * @since   10.0
	 */
	public function getContainer(): ContainerInterface;
}