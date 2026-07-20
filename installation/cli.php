#!/usr/bin/env php
<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2025 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

//// Uncomment the following line to enable Debug Mode and the debug log
// define('AKEEBA_DEBUG', 1);

//// Uncomment the following line to prevent setting very high memory and runtime limits
// define('AKEEBA_DISABLE_HIGH_LIMITS', 1);

########################################################################################################################
## Do not edit below this line.
########################################################################################################################

// Namespace imports
use Akeeba\BRS\Cli\Application\Application as CliApplication;
use Akeeba\BRS\Framework\Cli\InstallationQueue;
use Akeeba\BRS\Framework\Console\Output;
use Akeeba\BRS\Framework\Container\Container;
use Akeeba\BRS\Framework\Input\Cli;
use Akeeba\BRS\Helper\ApplicationHelper;
use Akeeba\BRS\Helper\MinVersionHelper;
use Psr\Container\ContainerInterface;

// Define ourselves as a parent file
define('_AKEEBA', 1);
define('_JEXEC', 1);

// Mark ourselves as being executed under CLI
define('AKEEBA_CLI', 1);

// Minimum PHP version check
require_once __DIR__ . '/src/Helper/MinVersionHelper.php';
MinVersionHelper::enforceMinPhpVersion();

// Polyfills
require_once __DIR__ . '/src/Framework/Polyfills/PHP8Strings.php';

// Load Composer dependencies
if (!file_exists(__DIR__ . '/vendor/autoload.php'))
{
	echo "You must run composer install on the repository before using this CLI application.";
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
global $autoloader;
$autoloader = require_once __DIR__ . '/vendor/autoload.php';
$autoloader->setPsr4(
	'Akeeba\\BRS\\', [
		__DIR__ . '/src',
		getcwd() . '/src',
	]
);
$autoloader->setPsr4(
	'Akeeba\\BRS\\Platform\\', [
		__DIR__ . '/platform/src',
		getcwd() . '/platform/src',
	]
);

// For customisation information please refer to index.php
if (@file_exists(__DIR__ . '/customisation/include.php'))
{
	require_once __DIR__ . '/customisation/include.php';
}
elseif (@file_exists(getcwd() . '/customisation/include.php'))
{
	require_once getcwd() . '/customisation/include.php';
}

// Register services to the container
$container   = $container ?? new Container();
$container->extend('input', function($oldService) {
	return new Cli();
});
$container->extend('application', function($oldService) use ($container) {
	return new CliApplication($container);
});
$container['output'] = function(ContainerInterface $c) {
	return new Output();
};
$container['installation_queue'] = function(ContainerInterface $c) {
	return new InstallationQueue($c);
};

// Apply command-line debug mode
if ($container->get('input')->getOption('debug', false, 'bool') && !defined('AKEEBA_DEBUG'))
{
	define('AKEEBA_DEBUG', 1);
}

// Apply application-wide modifiers
ApplicationHelper::applyDebugMode();
ApplicationHelper::applyMemoryLimit();

try
{
	$application = $container->get('application');
	$application->initialise();
	$application->dispatch();
	$application->execute();
}
catch (Throwable $e)
{
	$derp = new \Akeeba\BRS\Framework\Console\Color('', '', ['bold']);
	$errorMessages = [
		sprintf('Unhandled %s exception' , get_class($e)),
		'',
		$derp($e->getMessage()),
	];
	$debugMessages = [
		'-- ' . $e->getFile() . ':' . $e->getLine(),
		$e->getTraceAsString()
	];

	$container->get('output')->errorBlock(
		(defined('AKEEBA_DEBUG') && AKEEBA_DEBUG)
			? array_merge($errorMessages, [''], $debugMessages)
			: $errorMessages
	);
	$container->get('output')->stderrWriteln($debugMessages);

	exit(255);
}