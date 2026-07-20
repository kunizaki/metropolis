<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Steps\StepItem;
use Psr\Container\ContainerInterface;

/**
 * Abstract implementation of a CLI restoration step.
 *
 * @since  10.0
 */
abstract class AbstractInstallationStep implements InstallationStepInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The configuration for this step.
	 *
	 * @var   null|array
	 * @since 10.0
	 */
	protected $configuration = null;

	/**
	 * The identifier for this step. Typically, the corresponding view name of the web restoration script.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $identifier;

	/**
	 * The human-readable title of this step.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $title;

	/**
	 * The sub-step for this step.
	 *
	 * The meaning depends on the step. Most steps do not have substeps. The database step has one substep per database,
	 * the off-site directories step has one step per directory and so on.
	 *
	 * @var   null|string
	 * @since 10.0
	 */
	private $substep = null;

	/** @inheritDoc */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Create a CLI restoration object based on a restoration step from the queue.
	 *
	 * @param   StepItem  $step
	 *
	 * @return  InstallationStepInterface
	 * @since   10.0
	 * @see     \Akeeba\BRS\Framework\Steps\StepQueue
	 */
	public static function fromStep(StepItem $step): InstallationStepInterface
	{
		// Note: Platform-specific classes override common core classes
		$classes = [
			'\\Akeeba\\BRS\\Platform\\Cli\\Step\\' . ucfirst($step->getView()),
			'\\Akeeba\\BRS\\Cli\\Step\\' . ucfirst($step->getView()),
		];

		foreach ($classes as $class)
		{
			if (!class_exists($class))
			{
				continue;
			}

			/** @var InstallationStepInterface $o */
			$o = new $class($step->getContainer());

			if ($step->getSubStep())
			{
				$o->setSubstep($step->getSubStep());
			}

			return $o;
		}

		throw new \RuntimeException(sprintf('Cannot find CLI installer step for "%s"', $step->getView()));
	}

	/** @inheritDoc */
	public function getIdentifier(): string
	{
		if (!isset($this->identifier))
		{
			$parts            = explode('\\', get_class($this));
			return $this->identifier = strtolower(end($parts));
		}

		return $this->identifier;
	}

	/** @inheritDoc */
	public function getTitle(): string
	{
		return $this->title;
	}

	/** @inheritDoc */
	public function getSubstep(): ?string
	{
		return $this->substep;
	}

	/** @inheritDoc */
	public function setSubstep(?string $substep): void
	{
		$this->substep = $substep;
	}

	/**
	 * @inheritDoc
	 */
	public function getConfiguration(): array
	{
		if ($this->configuration === null)
		{
			$this->configuration = $this->getDefaultConfiguration();
		}

		return $this->configuration;
	}

	/**
	 * @inheritDoc
	 */
	public function setConfiguration(array $configuration)
	{
		if ($this->configuration === null)
		{
			$this->configuration = $this->getDefaultConfiguration();
		}

		foreach ($configuration as $k => $v)
		{
			if (!array_key_exists($k, $this->configuration))
			{
				continue;
			}

			$this->configuration[$k] = $v;
		}
	}

	/**
	 * Returns the default configuration for this step.
	 *
	 * Child classes will most likely need to override this.
	 *
	 * @return  array
	 * @since   10.0
	 */
	protected function getDefaultConfiguration(): array
	{
		return [];
	}
}