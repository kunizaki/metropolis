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
use Akeeba\BRS\Framework\Console\Color;
use Akeeba\BRS\Framework\Yaml\Spyc;
use Psr\Container\ContainerInterface;
use Symfony\Component\Yaml\Yaml;

class MakeConfig extends Command
{
	public function __construct(ContainerInterface $container)
	{
		$this->name = 'config:make';

		parent::__construct($container);
	}

	public function __invoke(): int
	{
		$paths      = $this->getContainer()->get('paths');
		$configFile = $paths->get('installation') . '/config.yml.php';
		$written    = file_put_contents(
			$configFile,
			"#<?php die(); ?>\n" .
			Yaml::dump($this->getContainer()->get('installation_queue')->getConfiguration(), 4, 2)
		);

		if ($written === false)
		{
			$this->output->errorBlock(sprintf('Could not write into %s', $configFile));

			return Command::ERROR;
		}

		$cmdStyle = new Color('', '', ['bold']);
		$command  = sprintf('%s restore', $this->input->getExecutable());

		$this->output->successBlock(
			[
				sprintf('Configuration written into %s', basename($configFile)),
				'',
				sprintf('Edit the file, then run %s to run the restoration.', $cmdStyle($command)),
			]
		);

		return Command::OK;
	}
}