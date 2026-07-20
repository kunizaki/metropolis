<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Cli\Step;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Cli\AbstractInstallationStep;
use Akeeba\BRS\Framework\Cli\Exception\ValidationException;
use Akeeba\BRS\Framework\Console\Color;

/**
 * CLI installation `database` step.
 *
 * @since  10.0
 */
class Database extends AbstractInstallationStep
{
	use WriteStepTrait;

	/**
	 * @inheritDoc
	 */
	public function isApplicable(): bool
	{
		$defaultConfig = $this->getDefaultConfiguration();

		return $defaultConfig['dbtype'] !== 'none';
	}

	/**
	 * @inheritDoc
	 */
	public function validate(): void
	{
		$model = $this->getContainer()->get('mvcFactory')->tempModel('Database');
		$text  = $this->getContainer()->get('language');

		if (!$model->hasAllNecessaryConnectors())
		{
			throw new ValidationException(
				$text->text('DATABASE_NOCONNECTORS_LBL_HEAD') . ' – ' . strip_tags(
					$text->sprintf('DATABASE_NOCONNECTORS_LBL_SUMMARY', PHP_VERSION)
				)
			);
		}

		$configuration = $this->getConfiguration();

		// Explicit validation: specific_tables
		if (isset($configuration['specific_tables']) && !is_array($configuration['specific_tables']))
		{
			throw new ValidationException(
				sprintf('Invalid %s.%s.specific_tables – Not an array', $this->getIdentifier(), $this->getSubstep())
			);
		}

		/**
		 * Instead of duplicating the validation logic, we create a temporary, mutable Database configuration object. We
		 * then assign each configuration variable to it and trap any exceptions. Exceptions from that object are only
		 * thrown on validation error. Since we know which key threw it, we can reformat the message in a more
		 * user-friendly way before throwing it back as a ValidationException.
		 */
		$dummy = new \Akeeba\BRS\Framework\Configuration\Database(
			$model->getDatabaseInfo($this->getSubstep())->toArray(), false
		);

		foreach ($configuration as $k => $v)
		{
			// Explicitly validated items are skipped
			if (in_array($k, ['specific_tables']))
			{
				continue;
			}

			try
			{
				$dummy->{$k} = $v;
			}
			catch (\Throwable $e)
			{
				throw new ValidationException(
					sprintf('Invalid %s.%s.%s – %s', $this->getIdentifier(), $this->getSubstep(), $k, $e->getMessage()),
					0,
					$e
				);
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function execute(?callable $messagePrinter = null): void
	{
		$input         = $this->getContainer()->get('input');
		$output        = $this->getContainer()->get('output');
		$isQuiet       = $input->getOption('quiet', false, 'bool');
		$cLightBlue    = new Color('light-blue');
		$cSuccess      = new Color('green', '', ['bold']);
		$cDatabase     = new Color('cyan');
		$configuration = $this->getConfiguration();
		$key           = $this->getSubstep();
		$model         = $this->getContainer()->get('mvcFactory')->tempModel('Database');
		$savedData     = $model->getDatabaseInfo($key);
		$data          = array_merge($savedData->toArray(), $configuration);


		if (!$isQuiet)
		{
			$output->heading(sprintf('Restoring database ‘%s’', $cDatabase($key)));
		}

		if ($configuration['skip'])
		{
			$cRed = new Color('red');

			$this->writeStep($cRed('Skipped'));

			return;
		}

		$model->setDatabaseInfo($key, $data);

		$restoreEngine = $this->getContainer()->get('db')->restore($key, $data);

		$restoreEngine->removeInformationFromStorage();
		$restoreEngine->removeLog();
		$restoreEngine->setSpecificEntities($configuration['specific_tables']);

		while (true)
		{
			$restoreEngine->getTimer()->resetTime();

			try
			{
				$result = $restoreEngine->stepRestoration();
			}
			catch (\Exception $e)
			{
				$result = ['error' => $e->getMessage()];
			}

			$error = $result['error'];

			if ($error)
			{
				/**
				 * The error message is in HTML. I need to convert it to plain text.
				 *
				 * @see \Akeeba\BRS\Framework\Database\AbstractRestore::handleFailedQuery
				 */
				$lines = explode("\n", trim($error));
				$lines = array_map('strip_tags', $lines);
				$lines = array_map('htmlspecialchars_decode', $lines);
				$error = implode("\n", $lines);

				throw new \RuntimeException($error, 0, $e ?? null);
			}

			$this->writeStep(
				sprintf(
					'Restored %s of %s (%0.2f%%) – ETA: %s',
					$result['restored'],
					$result['total'],
					$cLightBlue($result['percent']),
					$result['eta']
				)
			);

			if ($result['done'])
			{
				$this->writeStep($cSuccess('Finished'));

				break;
			}
		}
	}

	/** @inheritDoc */
	protected function getDefaultConfiguration(): array
	{
		$skipKeys = ['dbtech', 'sqlfile', 'marker', 'parts', 'tables'];
		$dbInfo   = $this->getContainer()
			->get('mvcFactory')
			->tempModel('Database')
			->getDatabaseInfo($this->getSubstep())
			->toArray();

		foreach ($skipKeys as $key)
		{
			if (array_key_exists($key, $dbInfo))
			{
				unset($dbInfo[$key]);
			}
		}

		$dbInfo['skip']            = false;
		$dbInfo['specific_tables'] = [];

		return $dbInfo;
	}
}