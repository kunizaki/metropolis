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

use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

/**
 * Abstract implementation of a step type handler class.
 *
 * @since  10.0
 */
abstract class AbstractStepType implements StepTypeInterface
{
	use ContainerAwareTrait;

	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}
}