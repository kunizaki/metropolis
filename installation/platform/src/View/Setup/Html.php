<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024-2026 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Platform\View\Setup;

defined('_AKEEBA') or die();

use Akeeba\BRS\Framework\Helper\Select;
use Akeeba\BRS\Framework\Mvc\View;
use Akeeba\BRS\Framework\Server\Technology;
use Akeeba\BRS\Model\Main;
use Akeeba\BRS\Platform\Model\Setup;
use Akeeba\BRS\View\ShowOnTrait;
use Akeeba\BRS\View\StepsTrait;

/**
 * The View Controller for the Site Setup page.
 *
 * @since  10.0
 */
class Html extends View
{
	use StepsTrait;
	use ShowOnTrait;

	/**
	 * The current state variables, as reported by the model.
	 *
	 * @var   object|null
	 * @since 10.0
	 */
	public $stateVars = null;

	/**
	 * Does this server have FTP support?
	 *
	 * @var   bool
	 * @deprecated 11.0 Will be removed along with Joomla! 3 support in 11.0
	 * @since      10.0
	 */
	public $hasFTP = true;

	/**
	 * Are we running under Apache webserver?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $htaccessSupported = false;

	/**
	 * Are we running under NGINX webserver?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $nginxSupported = false;

	/**
	 * Are we running under IIS webserver?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $webConfSupported = false;

	/**
	 * View options for the php.ini handling.
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $removePhpiniOptions = [];

	/**
	 * View options for the web.config handling.
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $replaceWeconfigOptions = [];

	/**
	 * View options for the .htaccess handling
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $removeHtpasswdOptions = [];

	/**
	 * Do we have a .htaccess file?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $hasHtaccess = false;

	/**
	 * Current state of robots handling.
	 *
	 * @var   int
	 * @since 10.0
	 */
	public $robotHandling = 0;

	/**
	 * Currently selected option for the .htaccess handling.
	 *
	 * @var   string
	 * @since 10.0
	 */
	public $htaccessOptionSelected = 'none';

	/**
	 * Drop-down options for the .htaccess handling.
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $htaccessOptions = [];

	/**
	 * Are we restoring under HTTP while having the option Force SSL enabled?
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $protocolMismatch = false;

	/**
	 * Should I regenerate the secret key?
	 *
	 * @var   array
	 * @since 10.0
	 */
	public $newSecretOptions;

	/**
	 * Flag to signal the absence of any Super User account.
	 *
	 * @var   bool
	 * @since 10.0
	 */
	public $noSuper = false;

	/**
	 * Initialise information for displaying the page.
	 *
	 * @return  bool
	 * @since   10.0
	 */
	public function onBeforeMain()
	{
		$doc  = $this->getContainer()->get('application')->getDocument();
		$lang = $this->getContainer()->get('language');

		/** @var Setup $model */
		$model           = $this->getModel();
		$this->stateVars = $model->getStateVariables();
		$this->noSuper   = count($this->stateVars->superusers ?? []) === 0;
		$jVersion        = $this->getContainer()->get('session')->get('jversion', '2.5.0');
		$this->hasFTP    = function_exists('ftp_connect')
		                   && version_compare($jVersion, '3.999.999', 'le');

		// Server technology
		$technology              = new Technology($this->getContainer());
		$this->htaccessSupported = $technology->isHtaccessSupported();
		$this->nginxSupported    = $technology->isNginxSupported();
		$this->webConfSupported  = $technology->isWebConfigSupported();

		// Prime the options array with some default info
		$this->removePhpiniOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEPHPINI_HELP',
		];

		$this->removeHandlerOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEADDHANDLER_HELP',
		];

		/** @var Main $mainModel */
		$mainModel  = $this->getContainer()->get('mvcFactory')->model('Main');
		$extraInfo  = $mainModel->getExtraInfo();
		$phpVersion = '';

		if (isset($extraInfo['php_version']))
		{
			$phpVersion = $extraInfo['php_version']['current'];
		}

		$this->updateHandlerOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => $lang->sprintf('SETUP_LBL_SERVERCONFIG_UPDATEADDHANDLER_HELP', $phpVersion),
		];

		$this->replaceHtaccessOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEHTACCESS_HELP',
		];

		$this->replaceWeconfigOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REPLACEWEBCONFIG_HELP',
		];

		$this->removeHtpasswdOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_REMOVEHTPASSWD_HELP',
		];

		$this->newSecretOptions = [
			'checked'  => '',
			'disabled' => '',
			'help'     => 'SETUP_LBL_SERVERCONFIG_NEWSECRET_HELP',
		];

		// If we are restoring to a new server everything is checked by default.
		if ($model->isNewhost())
		{
			$this->removePhpiniOptions['checked']    = 'checked="checked"';
			$this->replaceHtaccessOptions['checked'] = 'checked="checked"';
			$this->replaceWeconfigOptions['checked'] = 'checked="checked"';
			$this->removeHtpasswdOptions['checked']  = 'checked="checked"';
		}

		// Special case for AddHandler rule: we want to show that if it's a new host, OR the file path is different.
		if ($model->isNewhost() || $model->isDifferentFilesystem())
		{
			$this->removeHandlerOptions['checked'] = 'checked="checked"';
			$this->newSecretOptions['checked']     = 'checked="checked"';
		}

		// If any option is invalid, gray out the option AND remove the check to avoid user confusion.
		if (!$model->hasPhpIni())
		{
			$this->removePhpiniOptions['disabled'] = 'disabled="disabled"';
			$this->removePhpiniOptions['checked']  = '';
			$this->removePhpiniOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasWebconfig())
		{
			$this->replaceWeconfigOptions['disabled'] = 'disabled="disabled"';
			$this->replaceWeconfigOptions['checked']  = '';
			$this->replaceWeconfigOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		if (!$model->hasHtpasswd())
		{
			$this->removeHtpasswdOptions['disabled'] = 'disabled="disabled"';
			$this->removeHtpasswdOptions['checked']  = '';
			$this->removeHtpasswdOptions['help']     = 'SETUP_LBL_SERVERCONFIG_NONEED_HELP';
		}

		$this->protocolMismatch       = $model->protocolMismatch();
		$this->hasHtaccess            = $model->hasHtaccess();
		$this->htaccessOptionSelected = 'none';

		$options = ['none', 'default'];

		if ($model->hasAddHandler())
		{
			$options[] = 'removehandler';

			$this->htaccessOptionSelected = $model->isNewhost() ? 'removehandler' : 'none';
		}

		if ($model->hasAddHandler())
		{
			$options[] = 'replacehandler';

			$this->htaccessOptionSelected = 'replacehandler';
		}

		$this->htaccessOptionSelected = $model->getState('htaccessHandling', $this->htaccessOptionSelected);
		$this->robotHandling          = (int) $model->getState('robotHandling', 0);
		$selectHelper                 = new Select($this->getContainer());

		foreach ($options as $opt)
		{
			$this->htaccessOptions[] = $selectHelper->option($opt, $lang->text('SETUP_LBL_HTACCESSCHANGE_' . $opt));
		}

		// JavaScript
		$doc->addMediaScript('setup.js');

		// Help URL
		$doc->setHelpURL('https://www.akeeba.com/documentation/brs/misc-setup.html');

		// Buttons
		$doc->appendButton('GENERAL_BTN_INLINE_HELP', 'btn-outline-info', 'fa-info-circle', 'show-help');
		$this->addButtonPreviousStep();

		if (!$this->noSuper)
		{
			$this->addButtonSubmitStep();

			// Next step URL
			$doc->addScriptOptions('nextStep.url', $this->getContainer()->get('steps')->nextStep()->getUri()->toString());
		}

		// Pass Super Users information to the frontend
		$doc->addScriptOptions(
			'brs.setup', [
				'superusers'     => ($this->stateVars->superusers ?? null) ?: [],
				'defaultTmpDir'  => $this->stateVars->default_tmp,
				'defaultLogsDir' => $this->stateVars->default_log,
			]
		);

		return true;
	}
}