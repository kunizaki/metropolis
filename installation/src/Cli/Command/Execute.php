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

use Akeeba\BRS\Framework\Cli\Command;
use Akeeba\BRS\Framework\Yaml\Spyc;
use Psr\Container\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class Execute extends Command
{
	public function __construct(ContainerInterface $container)
	{
		$this->name = 'execute';

		parent::__construct($container);
	}

	public function __invoke(): int
	{
		$paths      = $this->getContainer()->get('paths');
		$configFile = $paths->get('installation') . '/config.yml.php';

		if (!file_exists($configFile))
		{
			$this->output->errorBlock([
				sprintf('Configuration file %s does not exist', $configFile),
				sprintf('Please run %s config:make', $this->input->getExecutable()),
			]);

			return Command::ERROR;
		}

		$configYaml = file_get_contents($configFile);

		if ($configYaml === false)
		{
			$this->output->errorBlock(sprintf('Could not read from %s', $configFile));

			return Command::ERROR;
		}

		$configuration = Yaml::parse($configYaml);

		$installationQueue = $this->getContainer()->get('installation_queue');
		$installationQueue->setConfiguration($configuration);
		$installationQueue->execute();

		$this->output->successBlock(
			[
				'Restoration complete',
				'',
				'Please remember to remove the installation directory.'
			]
		);


		return Command::OK;
	}
}