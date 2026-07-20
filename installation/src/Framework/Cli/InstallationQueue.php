<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli;

use Akeeba\BRS\Framework\Cli\Exception\ValidationException;
use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

final class InstallationQueue implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The stack of CLI installation steps.
	 *
	 * @var   array<InstallationStepInterface>
	 * @since 10.0
	 */
	private $queue;

	/**
	 * Constructor.
	 *
	 * Initialises the stack.
	 *
	 * @param   ContainerInterface  $container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$this->queue = [];

		foreach ($container->get('steps') as $k => $v)
		{
			$this->queue[] = AbstractInstallationStep::fromStep($v);
		}
	}

	/**
	 * Generator method returning only applicable steps.
	 *
	 * This method should not be used to carry out any work on the steps. It's meant to provide a way to quickly
	 * iterate over all applicable steps for troubleshooting, and to produce a list of the steps which will take place
	 * during restoration.
	 *
	 * @return  \Generator
	 * @since   10.0
	 */
	public function generator(): \Generator
	{
		foreach ($this->queue as $item)
		{
			if ($item->isApplicable())
			{
				yield $item;
			}
		}
	}

	/**
	 * Returns the current configuration of all restoration steps, in the format the user needs to provide it.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getConfiguration(): array
	{
		$ret = [];

		foreach ($this->generator() as $item)
		{
			$identifier = $item->getIdentifier();
			$substep    = $item->getSubstep();

			$ret[$identifier] = $ret[$identifier] ?? [];

			if ($substep)
			{
				$ret[$identifier][$substep] = $item->getConfiguration();
			}
			else
			{
				$ret[$identifier] = $item->getConfiguration();
			}
		}

		return $ret;
	}

	/**
	 * Sets the configuration across all restoration steps.
	 *
	 * @param   array  $configuration
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setConfiguration(array $configuration): void
	{
		foreach ($this->generator() as $item)
		{
			$identifier = $item->getIdentifier();
			$substep    = $item->getSubstep();
			$itemConfig = $configuration[$identifier] ?? [];

			if ($substep)
			{
				$itemConfig = $itemConfig[$substep] ?? [];
			}

			if (empty($itemConfig))
			{
				continue;
			}

			$item->setConfiguration($itemConfig);
		}
	}

	/**
	 * Validates all restoration steps.
	 *
	 * This should be called _after_ setConfiguration().
	 *
	 * @param   callable  $validationErrorHandler  A callable which is given the step and validation error exception.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function validate(callable $validationErrorHandler): void
	{
		foreach ($this->generator() as $item)
		{
			try
			{
				$item->validate();
			}
			catch (ValidationException $e)
			{
				$validationErrorHandler($item, $e);

				break;
			}
		}
	}

	/**
	 * Executes the restoration.
	 *
	 * @param   callable|null  $announciator  A callable used to announce the restoration scripts.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function execute(?callable $announciator = null): void
	{
		foreach ($this->generator() as $item)
		{
			if ($announciator)
			{
				$announciator($item);
			}

			$item->execute();
		}
	}
}