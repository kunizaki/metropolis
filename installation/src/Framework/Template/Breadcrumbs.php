<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Template;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Steps\StepItem;
use Psr\Container\ContainerInterface;

/**
 * Application breadcrumbs
 *
 * @since  10.0
 */
final class Breadcrumbs implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The application container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Should I display breadcrumbs>
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function hasCrumbs(): bool
	{
		$view      = strtolower($this->getContainer()->get('input')->getCmd('view', 'main'));
		$allCrumbs = $this->getContainer()->get('configuration')->steps;

		return in_array($view, $allCrumbs);
	}

	/**
	 * Returns the application breadcrumbs.
	 *
	 * @return  array<Breadcrumb>
	 * @since   10.0
	 */
	public function getCrumbs(): array
	{
		$input   = $this->getContainer()->get('input');
		$view    = strtolower($input->getCmd('view', ''));
		$substep = $input->getRaw('substep', null);
		$steps   = $this->getContainer()->get('steps');

		$stepNames = [];

		foreach ($steps as $step)
		{
			$stepNames[] = $step->getView();
		}

		$stepNames = array_unique($stepNames);
		$substepCounts = array_combine($stepNames, array_fill(0, count($stepNames), 0));
		$substepIndex  = 0;

		// Count substeps in every crumb
		$substepsPerView = [];

		foreach ($steps as $step)
		{
			$substepCounts[$step->getView()]++;
			$substepsPerView[$step->getView()]   = $substepsPerView[$step->getView()] ?? [];
			$substepsPerView[$step->getView()][] = $step->getSubStep();
		}

		if ($substepCounts[$view] > 0)
		{
			$substepIndex = array_search($substep, $substepsPerView[$view]) + 1;
		}

		$crumbs = [];

		foreach ($stepNames as $stepName)
		{
			$crumbs[] = new Breadcrumb(
				$stepName,
				$stepName === $view,
				max(count($substepsPerView[$stepName] ?? []), 0),
				$stepName === $view ? $substepIndex : 0
			);
		}

		return $crumbs;
	}
}