<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Command;

defined('_AKEEBA') or die();

use Akeeba\BRS\Cli\Application\CommandsAwareTrait;
use Akeeba\BRS\Framework\Cli\Command;
use Akeeba\BRS\Framework\Console\Color;

class Help extends Command
{
	use CommandsAwareTrait;

	/**
	 * @inheritDoc
	 */
	public function __invoke(): int
	{
		$text = $this->getContainer()->get('language');
		$this->populateCommands();

		foreach (array_keys($this->commands) as $cmdName)
		{
			if ($cmdName === 'help')
			{
				continue;
			}

			if ($this->input->hasArgument($cmdName))
			{
				$commandStyle   = new Color('bright-green');
				$this->output->writeln($commandStyle($cmdName));
				$this->output->newLine();
				$this->output->writeln($this->commands[$cmdName]->getHelp());
				$this->output->newLine();

				return Command::OK;
			}
		}

		$this->showCommands();

		return Command::OK;
	}

	public function getHelp(): array
	{
		$cmdStyle = $this->output->getStyle('command');

		return array_merge(
			parent::getHelp(),
			[
				$cmdStyle(sprintf('%s help [command]', $this->input->getExecutable())),
			]
		);
	}

	/**
	 * Displays the available commands.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function showCommands()
	{
		$descriptions = array_map(
			function (Command $command): string {
				return $command->getShortDescription();
			},
			$this->commands
		);

		$text           = $this->getContainer()->get('language');
		$underlineStyle = new Color('', '', ['underscore']);
		$execStyle      = $this->output->getStyle('command');
		$commandStyle   = new Color('bright-green');

		$this->output->writeln($underlineStyle($text->text('CLI_HELP_LBL_COMMANDS')));

		$this->output->columnar($descriptions, $commandStyle);

		$this->output->newLine();

		$this->output->writeln(
			$text->sprintf(
				'CLI_HELP_LBL_TYPE_HELP',
				$execStyle(sprintf('%s help <command>', basename($this->input->getExecutable())))
			)
		);
	}
}