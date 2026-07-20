<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

JLoader::register('SpauthorarchiveHelper', JPATH_SITE . '/components/com_spauthorarchive/helpers/helper.php');

HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();

// Include CSS files
$doc->addStylesheet( Uri::root(true) . '/components/com_spauthorarchive/assets/css/spauthorarchive.css' );
$doc->addStylesheet( Uri::root(true) . '/components/com_spauthorarchive/assets/css/spauthorarchive-structure.css' );

// Include JS files
$doc->addScript( Uri::root(true) . '/components/com_spauthorarchive/assets/js/spauthorarchive.js' );

$controller = BaseController::getInstance('Spauthorarchive');
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();
