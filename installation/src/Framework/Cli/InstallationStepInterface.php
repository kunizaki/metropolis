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

use Akeeba\BRS\Framework\Cli\Exception\ValidationException;
use Psr\Container\ContainerInterface;

/**
 * Interface to a CLI restoration step.
 *
 * @since  10.0
 */
interface InstallationStepInterface
{
	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container  The application container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container);

	/**
	 * Returns the step identifier ("view name").
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getIdentifier(): string;

	/**
	 * Returns the human-readable step title.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function getTitle(): string;

	/**
	 * Returns the substep, if any is specified.
	 *
	 * Only applicable to certain steps, e.g. Database.
	 *
	 * @return  string|null  NULL when no substep is set.
	 * @since   10.0
	 */
	public function getSubstep(): ?string;

	/**
	 * Sets the substep for this object.
	 *
	 * Only applicable to certain steps, e.g. Database.
	 *
	 * @param   string|null  $substep
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function setSubstep(?string $substep): void;

	/**
	 * Is this installation step applicable to this backup?
	 *
	 * Depending on the restoration script platform, and the backup configuration some restoration steps may be
	 * optional, or non-applicable. For example, the database step in the generic script may be inapplicable if no
	 * database was backed up. Another example is the data replacement in the WordPress script if restoring onto the
	 * same location. A final example is the main view which is supposed to check server configuration, but it does not
	 * make sense to do that under CLI (since the restored site may run under a different PHP version and/or PHP
	 * configuration). Returning false will simply skip over the restoration step.
	 *
	 * @return  bool  Returns false if this restoration step will not be executed.
	 * @since   10.0
	 */
	public function isApplicable(): bool;

	/**
	 * Get the current configuration of this installation step.
	 *
	 * May return the default configuration if setConfiguration is not set.
	 *
	 * @return  array
	 * @since   10.0
	 */
	public function getConfiguration(): array;

	/**
	 * Sets the step configuration from the user-provided configuration values.
	 *
	 * @param   array  $configuration
	 *
	 * @return  mixed
	 * @since   10.0
	 */
	public function setConfiguration(array $configuration);

	/**
	 * Validates the configuration for glaringly obvious omissions.
	 *
	 * @return  void
	 * @since   10.0
	 * @throws  ValidationException  When configuration errors are detected.
	 */
	public function validate(): void;

	/**
	 * Executes the restoration step.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function execute(): void;
}