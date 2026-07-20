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

use Psr\Container\ContainerInterface;

/**
 * Interface to CLI commands.
 *
 * @since  10.0
 */
interface CommandInterface
{
	/**
	 * Constructor
	 *
	 * @param   ContainerInterface  $container  The application container.
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container);

	/**
	 * Makes the class callable. Main execution point of the command.
	 *
	 * @return  int
	 * @since   10.0
	 */
	public function __invoke(): int;

	/**
	 * Get the command's name.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getName(): string;

	/**
	 * Get the short description of the command.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getShortDescription(): string;

	/**
	 * Get the longer help message for the command.
	 *
	 * @return  array<string>
	 * @since   10.0
	 */
	public function getHelp(): array;
}