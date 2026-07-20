<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\CMSVersion;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Abstract base class for CMS version detection implementations.
 *
 * @since  10.0
 */
abstract class AbstractDetection implements DetectionInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The container instance to be set.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}
}