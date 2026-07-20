<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Application;

use Akeeba\BRS\Application\DefaultLanguagePostProcess;
use Akeeba\BRS\Framework\Application\AbstractApplication;
use Akeeba\BRS\Framework\Cli\Command;
use Akeeba\BRS\Framework\Input\Cli;

defined('_AKEEBA') or die();

/**
 * The Akeeba Backup Restoration Script CLI application.
 *
 * @since  10.0
 */
class Application extends AbstractApplication
{
	use CommandsAwareTrait;

	/**
	 * The currently active command.
	 *
	 * @var   Command|null
	 * @since 10.0
	 */
	private $command = null;

	/**
	 * The default command to execute when no other command is available.
	 *
	 * @var   string
	 * @since 10.0
	 */
	private $defaultCommand = 'help';

	/** @inheritDoc */
	public function initialise()
	{
		$language = $this->getContainer()->get('language');
		$language->addIniProcessCallback(new DefaultLanguagePostProcess);
		$language->loadLanguage('en-GB');

		// Use the default template if none is already set.
		$this->template = $this->template ?? 'default';

		require_once $this->container->get('paths')->get('base') . '/version.php';

		/** @var Cli $input */
		$input   = $this->getContainer()->get('input');
		$output  = $this->getContainer()->get('output');
		$isQuiet = $input->getOption('quiet', false, 'bool');

		if (!$isQuiet)
		{
			$output->title('Akeeba Backup Restoration Script v.' . AKEEBA_VERSION);
			$output->copyright(
				[
					'Copyright (C) 2006-' . gmdate('Y') . ' Akeeba Ltd.',
					'This program is Free Software, it comes with ABSOLUTELY NO WARRANTY and you are',
					'welcome to redistribute it under certain conditions; use the `license` command',
					'for more information.',
				]
			);
			$output->writeln('');
		}

		$this->populateCommands();
	}

	/** @inheritDoc */
	public function dispatch(): void
	{
		$requested = $this->getContainer()->get('input')->getFirstArgument($this->defaultCommand);

		if (!isset($this->commands[$requested]))
		{
			$this->getContainer()->get('output')->warningBlock(
				sprintf('Unknown command %s', $requested)
			);

			$requested = 'help';
		}

		$this->command = $this->commands[$requested];
	}

	/** @inheritDoc */
	public function render(): void
	{
		throw new \RuntimeException(
			sprintf("%s is invalid under the CLI context. Use execute() instead.", __METHOD__)
		);
	}

	/**
	 * Executes the CLI application command.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function execute(): void
	{
		$this->close(call_user_func($this->command));
	}


}