<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Steps;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Steps\Type\StepTypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Steps queue.
 *
 * This lists the steps we need to follow to complete a site restoration.
 *
 * @since  10.0
 */
final class StepQueue implements ContainerAwareInterface, \Iterator, \JsonSerializable
{
	use ContainerAwareTrait;

	private $steps = [];

	private $currentIndex = null;

	/**
	 * Constructor method for initializing the steps iterator.
	 *
	 * It sets up the container, initializes iterator mode, and determines the steps either from the provided array,
	 * session, or configuration. Additionally, it sets the current step based on the session's stored hash value, if
	 * available.
	 *
	 * @param   ContainerInterface  $container  The dependency injection container instance.
	 * @param   array               $steps      An optional array of steps to initialize.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container, array $steps = [])
	{
		$this->setContainer($container);

		foreach ($steps ?: $this->fromSession() ?: $this->fromConfiguration() as $step)
		{
			$step = $step instanceof StepItem ? $step : new StepItem($this->getContainer(), $step['view'], $step['substep'] ?? null);

			$this->steps[] = $step;
		}

		$this->currentIndex = 0;

		// Set the current item from the session information (or rewind if no step, or an invalid step is indicated).
		$index              = $this->getContainer()->get('session')->get('steps.current', null) ?: 0;
		$this->currentIndex = preg_match('#^\d+$#', $index) ? intval($index) : 0;
		$this->currentIndex = max(0, min($this->currentIndex, count($this->steps) - 1));
	}

	/**
	 * Set the current step from the `view` and `substep` URL query parameters.
	 *
	 * If no such data exists, or does not point to an existing StepItem in the queue, we will continue using the
	 * current step, as it was before calling this method.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setCurrentStepFromRequest(): void
	{
		$input   = $this->getContainer()->get('input');
		$view    = $input->getCmd('view', null);
		$substep = $input->getRaw('substep', null);
		$index   = 0;

		foreach ($this as $step)
		{
			if ($step->getView() === $view && $step->getSubstep() === $substep)
			{
				$this->currentIndex = $index;

				return;
			}

			$index++;
		}
	}

	/**
	 * Destructor method that ensures the object's state is saved to the session.
	 *
	 * This method is automatically called when the object is no longer referenced.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function __destruct()
	{
		$this->toSession();
	}

	/**
	 * Returns the current step.
	 *
	 * @return  StepItem|null
	 * @since   10.0
	 */
	public function current(): ?StepItem
	{
		if ($this->currentIndex < 0 || $this->currentIndex >= count($this->steps))
		{
			return null;
		}

		return $this->steps[$this->currentIndex] ?? null;
	}

	/**
	 * Returns the next step.
	 *
	 * @return  StepItem|null
	 * @since   10.0
	 */
	public function nextStep(): ?StepItem
	{
		if ($this->currentIndex >= count($this->steps) - 1)
		{
			return null;
		}

		return $this->steps[$this->currentIndex + 1];
	}

	/**
	 * Returns the previous step.
	 *
	 * @return  StepItem|null
	 * @since   10.0
	 */
	public function previousStep(): ?StepItem
	{
		if ($this->currentIndex <= 0)
		{
			return null;
		}

		return $this->steps[$this->currentIndex - 1];
	}

	/**
	 * Returns the first step.
	 *
	 * @return  StepItem|null
	 * @since   10.0
	 */
	public function top(): ?StepItem
	{
		return $this->steps[0] ?? null;
	}

	/**
	 * Returns the last step.
	 *
	 * @return  StepItem|null
	 * @since   10.0
	 */
	public function bottom(): ?StepItem
	{
		return $this->steps[count($this->steps) - 1] ?? null;
	}

	/** @inheritdoc */
	public function next(): void
	{
		$this->currentIndex++;

		if ($this->currentIndex >= count($this->steps))
		{
			$this->currentIndex = count($this->steps);
		}
	}

	/** @inheritdoc */
	public function key(): int
	{
		return $this->currentIndex;
	}

	/** @inheritdoc */
	public function valid(): bool
	{
		return $this->currentIndex < count($this->steps);
	}

	/** @inheritdoc */
	public function rewind(): void
	{
		$this->currentIndex = 0;
	}

	/** @inheritdoc */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return array_map(
			function (StepItem $step): array {
				return [
					'view'    => $step->getView(),
					'substep' => $step->getSubstep(),
				];
			},
			$this->steps
		);
	}

	/**
	 * Retrieves and decodes serialized steps from the session.
	 *
	 * @return  array  The deserialized data as an array, or an empty array if the session does not contain valid data.
	 * @since   10.0
	 */
	private function fromSession(): array
	{
		$serialisedSteps = $this->getContainer()->get('session')->get('steps.serialised', '');

		if (empty($serialisedSteps))
		{
			return [];
		}

		try
		{
			$data = @json_decode($serialisedSteps, true);
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!is_array($data))
		{
			return [];
		}

		return $data;
	}

	/**
	 * Serializes the steps and saves them along with the current step hash to the session.
	 *
	 * If the current step hash cannot be retrieved, a null value is stored for the current step.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function toSession(): void
	{
		$session = $this->getContainer()->get('session');

		$session->set('steps.serialised', json_encode($this));
		$session->set('steps.current', $this->currentIndex);
	}

	/**
	 * Builds an array of step data based on the configuration provided.
	 *
	 * Iterates through the configuration's steps, retrieves the corresponding step data,
	 * and appends it to the final array. If a step type handler class does not exist, a generic
	 * StepItem instance is created for the step.
	 *
	 * @return  array  An array containing the step data derived from the configuration.
	 * @since   10.0
	 */
	private function fromConfiguration(): array
	{
		$data = [];

		foreach ($this->getContainer()->get('configuration')->steps as $stepName)
		{
			$typeClass = __NAMESPACE__ . '\\Type\\' . ucfirst($stepName);

			if (class_exists($typeClass) && in_array(StepTypeInterface::class, class_implements($typeClass)))
			{
				$typeHandler = new $typeClass($this->getContainer());
				$data        = array_merge($data, $typeHandler->getStepData());

				continue;
			}

			$data[] = new StepItem($this->getContainer(), $stepName);
		}

		return array_values($data);
	}
}