<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

//helper & model
$com_helper = JPATH_BASE . '/components/com_spproperty/helpers/helper.php';

if (file_exists($com_helper)) {
    require_once($com_helper);
} else {
    echo '<p class="alert alert-warning">' . Text::_('COM_SPPROPERTY_COMPONENT_NOT_INSTALLED_OR_MISSING_FILE') . '</p>';
    return;
}


HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();

// Include CSS files
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/spproperty-structure.css');
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/style.css');
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/font-awesome.min.css');
$doc->addStyleSheet(Uri::root(true) . '/administrator/components/com_spproperty/assets/css/icomoon.css');
$doc->addScript(Uri::root(true) . '/administrator/components/com_spproperty/assets/js/icomoon.json',[],['type' => 'application/json']);
$doc->addStyleSheet(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css');
$doc->addStyleSheet(Uri::root() . 'components/com_spproperty/assets/css/owl.theme.default.min.css');
$doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js');
$doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/simplebar.js');

$controller = BaseController::getInstance("Spproperty");
$input      = Factory::getApplication()->input;
$view       = $input->getCmd('view', 'default');
$input->set('view', $view);
$controller->execute($input->getCmd('task'));
$controller->redirect();
