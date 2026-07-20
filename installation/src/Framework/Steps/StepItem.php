<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Steps;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Uri\Uri;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * An immutable object representing a restoration step.
 *
 * @since  10.0
 */
final class StepItem implements \JsonSerializable, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * The view name this step corresponds to.
	 *
	 * @var    string
	 * @since  10.0
	 */
	private $view;

	/**
	 * The optional substep this stepp object corresponds to.
	 *
	 * This is used by database, offsitedirs etc steps to convey the item being restored in this step.
	 *
	 * @var    string|null
	 * @since  10.0
	 */
	private $subStep = null;

	/**
	 * Constructor.
	 *
	 * @param   string       $view     The view name this step corresponds to.
	 * @param   string|null  $subStep  The optional substep this step corresponds to.
	 */
	public function __construct(ContainerInterface $container, string $view, ?string $subStep = null)
	{
		$this->setContainer($container);

		$this->view    = $view;
		$this->subStep = $subStep;
	}

	/**
	 * Creates an instance of the class from a JSON string.
	 *
	 * @param   string  $json  The JSON string to parse into an object instance.
	 *
	 * @return  self  An instance of the class created from the provided JSON data.
	 * @since   10.0
	 */
	public static function fromJson(string $json): self
	{
		try
		{
			$data = @json_decode($json);
		}
		catch (\Exception $e)
		{
			$data = [];
		}

		$data = is_array($data) ? $data : [];

		return new self($data['view'] ?? 'main', $data['subStep'] ?? null);
	}

	/**
	 * Get the URI for this step.
	 *
	 * @return  Uri
	 * @since   10.0
	 */
	public function getUri(): Uri
	{
		$uri = clone $this->getContainer()->get('uri')->instance();

		$uri->setQuery([]);
		$uri->setVar('view', $this->getView());

		if ($this->getSubStep())
		{
			$uri->setVar('substep', $this->getSubStep());
		}

		return $uri;
	}

	/**
	 * Returns the view name this step corresponds to.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getView(): string
	{
		return $this->view;
	}

	/**
	 * Returns the translation key used for the step's title.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getName(): string
	{
		return strtoupper($this->view) . '_LBL_TITLE';
	}

	/**
	 * Returns the configured substep (e.g. for database steps).
	 *
	 * @return  string|null
	 * @since   10.0
	 */
	public function getSubStep(): ?string
	{
		return $this->subStep;
	}

	/** @inheritdoc */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'view'    => $this->view,
			'subStep' => $this->subStep,
		];
	}
}