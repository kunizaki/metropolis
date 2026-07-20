<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\RestorationCheck;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * A check to perform before restoring the site.
 *
 * @since  10.0
 */
abstract class AbstractRestorationCheck implements ContainerAwareInterface, RestorationCheckInterface
{
	use ContainerAwareTrait;

	/**
	 * Is this a required check?
	 *
	 * Failure to meet required checks will cause restoration failure.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	private $required = false;

	/**
	 * Translation string which labels this check.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $name = '';

	/**
	 * Expected value.
	 *
	 * @var   mixed
	 * @since 10.0
	 */
	private $expected = null;

	/**
	 * The current value.
	 *
	 * @var   null
	 * @since 10.0
	 */
	private $currentValue = null;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  Application container.
	 * @param   string              $name       Translation string labelling this check.
	 * @param   mixed               $expected   Expected value.
	 * @param   bool                $required   Is this check required?
	 *
	 * @since   10.0
	 */
	public function __construct(
		ContainerInterface $container, string $name, $expected, bool $required = false
	)
	{
		$this->setContainer($container);
		$this->name         = $name;
		$this->expected     = $expected;
		$this->required     = $required;
		$this->currentValue = $this->returnCurrentValue();
	}

	/** @inheritdoc */
	public function isValid(): bool
	{
		return $this->getCurrentValue() === $this->expected;
	}

	/** @inheritdoc */
	public function isRequired(): bool
	{
		return $this->required;
	}

	/** @inheritdoc */
	public function getExpected()
	{
		return $this->expected;
	}

	/** @inheritdoc */
	public function getCurrentValue()
	{
		if (!isset($this->currentValue))
		{
			$this->currentValue = $this->returnCurrentValue();
		}

		return $this->currentValue;
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return true;
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		return null;
	}

	/** @inheritdoc */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Is this a Joomla! site restoration?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	protected function isJoomla(): bool
	{
		return $this->getContainer()->get('configuration')->type == 'joomla';
	}

	/**
	 * Is this a WordPress site restoration?
	 *
	 * @return  bool
	 * @since   10.0
	 */
	protected function isWordPress(): bool
	{
		return $this->getContainer()->get('configuration')->type == 'wordpress';
	}

	/**
	 * Method to return the current value.
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	abstract protected function returnCurrentValue();
}