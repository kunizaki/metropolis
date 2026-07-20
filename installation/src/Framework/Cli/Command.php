<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Cli;

use Akeeba\BRS\Framework\Cli\Akeeba\BRS\Framework\Console\Output;
use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Abstract implementation of a CLI command
 *
 * @since  10.0
 */
abstract class Command implements CommandInterface, ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * Value to return when execution completes successfully.
	 *
	 * @var   int
	 * @since 10.0
	 */
	public const OK = 0;

	/**
	 * Value to return when execution fails.
	 *
	 * @var   int
	 * @since 10.0
	 */
	public const ERROR = 255;

	/**
	 * The name of the command. Leave NULL to auto-populate from class name.
	 *
	 * @var   string|null
	 * @since 10.0
	 */
	protected $name = null;

	/**
	 * CLI input.
	 *
	 * @var   Cli
	 * @since 10.0
	 */
	protected $input;

	/**
	 * Console output.
	 *
	 * @var   \Akeeba\BRS\Framework\Console\Output
	 * @since 10.0
	 */
	protected $output;

	/** @inheritDoc */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);

		$this->name   = $this->name ?? $this->getDefaultName();
		$this->input  = $container->get('input');
		$this->output = $container->get('output');
	}

	/** @inheritDoc */
	public function getName(): string
	{
		return $this->name;
	}

	/** @inheritDoc */
	public function getShortDescription(): string
	{
		return $this->getContainer()->get('language')->text('CLI_' . str_replace(':', '_', $this->getName()) . '_DESCRIPTION_SHORT');
	}

	/** @inheritDoc */
	public function getHelp(): array
	{
		return [
			$this->getContainer()->get('language')->text('CLI_' . str_replace(':', '_', $this->getName()) . '_HELP')
		];
	}

	/**
	 * Returns the default name for a command class.
	 *
	 * @return  string
	 * @since   10.0
	 */
	private function getDefaultName(): string
	{
		$parts = explode('\\', static::class);

		return strtolower(end($parts));
	}

}