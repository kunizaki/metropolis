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
 * A trait to implement ContainerAwareInterface
 *
 * @since  10.0
 */
trait ContainerAwareTrait
{
	/**
	 * The PSR-11 container
	 *
	 * @var   ContainerInterface
	 * @since 10.0
	 */
	protected $container;

	/**
	 * Set the PSR-11 container used by this object.
	 *
	 * @param   ContainerInterface  $container  The container object
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setContainer(ContainerInterface $container): void
	{
		$this->container = $container;
	}

	/**
	 * Get the PSR-11 container used by this object.
	 *
	 * @return  ContainerInterface
	 * @since   10.0
	 */
	public function getContainer(): ContainerInterface
	{
		return $this->container;
	}
}