<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
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
use Akeeba\BRS\Framework\Application\AbstractApplication;
use Akeeba\BRS\Framework\Container\Container;
use Akeeba\BRS\Helper\ApplicationHelper;
use Akeeba\BRS\Helper\MinVersionHelper;

// Define ourselves as a parent file
define('_AKEEBA', 1);
define('_JEXEC', 1);

// Minimum PHP version check
require_once __DIR__ . '/src/Helper/MinVersionHelper.php';
MinVersionHelper::enforceMinPhpVersion();

// Polyfills
require_once __DIR__ . '/src/Framework/Polyfills/PHP8Strings.php';

// Main program
try
{
	// Load Composer and register PSR-4 namespace roots
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

	// Apply application-wide modifiers
	ApplicationHelper::applyDebugMode();
	ApplicationHelper::applyMemoryLimit();
	ApplicationHelper::applyTimeLimit();

	/**
	 * Load optional code for customisation
	 *
	 * Notes for customisation:
	 * - You MUST NOT use the Akeeba branding in your customised installer. You MAY mention it is based on the
	 *   Akeeba Backup Restoration Script, as long as you explicitly state which that your installation script is
	 *   not affiliated with or endorsed by Akeeba Ltd. You SHOULD include your contact information in the footer,
	 *   especially if you are distributing a template quick start.
	 * - Your file has access to $autoloader, Composer's PSR-4 autoloader. Use it to register your custom code's
	 *   PSR-4 prefixes.
	 * - You can override the Container in two ways. One, register a new \Akeeba\BRS\Framework\Container\Container
	 *   class. Two, by setting a $container variable in the root namespace in your customisation.php file, which is
	 *   an object implementing \Psr\Container\ContainerInterface (see the aforementioned Container class for the
	 *   necessary services).
	 * - You can tell the restoration script to use a custom template (theme) using
	 *   $container->get('application')->setTemplate('your_template'). Put your custom template in the
	 *   template/your_template folder.
	 * - You can override the HTML view templates you see in ViewTemplates and platform/ViewTemplates in your
	 *   template's override folder. For example, copy ViewTemplates/main/init.php into
	 *   templates/your_template/override/main/init.php and modify the latter file. Now you see your modified file,
	 *   not the original!
	 * - You cannot override JS files. Use $application->getDocument()->removeScript('path/to/script.min.js') in
	 *   your view template override to remove our default script, then
	 *   $application->getDocument()->addScript('path/to/your_script.min.js') to add your replacement script.
	 *
	 * Our documentation has more customisation information.
	 */
	if (@file_exists(__DIR__ . '/customisation/include.php'))
	{
		require_once __DIR__ . '/customisation/include.php';
	}
	// The fallback to getcwd() allows us to test this feature using symlinks on our dev server.
	elseif (@file_exists(getcwd() . '/customisation/include.php'))
	{
		require_once getcwd() . '/customisation/include.php';
	}

	// Set up and execute the application
	$container   = $container ?? new Container();
	$application = $container->get('application');
	$application->initialise();
	$application->dispatch();
	$application->render();
	$application->close();
}
catch (Throwable $exc)
{
	$filename = null;

	if (isset($application) && ($application instanceof AbstractApplication))
	{
		$template = $application->getTemplate();

		if (file_exists(__DIR__ . '/template/' . $template . '/error.php'))
		{
			$filename = __DIR__ . '/template/' . $template . '/error.php';
		}
	}

	// An uncaught application error occurred
	$exceptionClass = get_class($exc);
	$html           = <<< HTML
<h1>Application Error</h1>

<p>Please submit the following error message <em>and the trace below it</em> in their entirety when requesting support.</p>

<p style="font-size: x-small">
	Our support services are available only to qualifying subscribers in accordance with our Terms of Service.
</p>

<div class="alert alert-danger">
	<h5 class="alert-heading">$exceptionClass &mdash; {$exc->getMessage()}</h5>
	<p>
		{$exc->getFile()}::L{$exc->getLine()}
	</p>
</div>

<pre>{$exc->getTraceAsString()}</pre>

HTML;

	while ($exc = $exc->getPrevious())
	{
		$exceptionClass = get_class($exc);
		$html           .= <<< HTML
<hr/>

<div class="alert alert-danger">
	<h5 class="alert-heading">$exceptionClass &mdash; {$exc->getMessage()}</h5>
	<p>
		{$exc->getFile()}::L{$exc->getLine()}
	</p>
</div>

<pre>{$exc->getTraceAsString()}</pre>

HTML;

	}

	if (!is_null($filename))
	{
		$error_message = $html;
		$error_code    = 500;

		include $filename;

		exit();
	}

	if (defined('AKEEBA_DEBUG') && AKEEBA_DEBUG)
	{
		$out = '<?php die(); ?>' . $html;
		file_put_contents(__DIR__ . '/error.log.php', $out);
	}

	@header('HTTP/1.0 500 Internal Server Error');
	echo "<!doctype html><html><head><title>Error</title></head><body>$html</body></html>";

	exit();
}