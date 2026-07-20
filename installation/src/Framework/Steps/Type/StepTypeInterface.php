<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Steps\Type;

defined('_AKEEBA') or die();

use Psr\Container\ContainerInterface;

/**
 * Interface to a step type handler.
 *
 * @since  10.0
 */
interface StepTypeInterface
{
	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The application container.
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container);

	/**
	 * Get step data for all substeps of this type based on the information found in the Container.
	 *
	 * This uses the Configuration object provided by the Container.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getStepData(): array;
}