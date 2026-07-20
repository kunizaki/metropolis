<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Application;

use Akeeba\BRS\Framework\Cli\Command;
use Akeeba\BRS\Framework\Cli\CommandInterface;

defined('_AKEEBA') or die();

/**
 * A trait to locate all known commands.
 *
 * @since  10.0
 */
trait CommandsAwareTrait
{
	/**
	 * All available commands.
	 *
	 * @var   array<Command>
	 * @since 10.0
	 */
	protected $commands;

	/**
	 * Populates the known commands.
	 *
	 * @return  void
	 * @since   10.0
	 */
	private function populateCommands(): void
	{
		$paths      = $this->getContainer()->get('paths');
		$sourceDirs = [
			'\\Akeeba\\BRS\\Cli\\Command\\'           => $paths->get('installation') . '/src/Cli/Command',
			'\\Akeeba\\BRS\\Platform\\Cli\\Command\\' => $paths->get('platform.src') . '/Cli/Command',
		];

		foreach ($sourceDirs as $prefix => $dir)
		{
			if (!@is_dir($dir))
			{
				continue;
			}

			$di = new \DirectoryIterator($dir);

			/** @var \DirectoryIterator $item */
			foreach ($di as $item)
			{
				if (!$item->isFile() || $item->getExtension() !== 'php')
				{
					continue;
				}

				$className = $prefix . $item->getBasename('.php');

				if (!class_exists($className, true))
				{
					continue;
				}

				$implements = class_implements($className);

				if (!in_array(CommandInterface::class, $implements))
				{
					continue;
				}

				try
				{
					/** @var CommandInterface $command */
					$command = new $className($this->getContainer());
					$this->commands[$command->getName()] = $command;
				}
				catch (\Throwable $e)
				{
					continue;
				}
			}

			ksort($this->commands);
		}
	}
}