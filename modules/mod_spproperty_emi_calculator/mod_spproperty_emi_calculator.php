<?php

/**
* @package mod_spproperty_emi_calculator
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

HTMLHelper::_('jquery.framework');

//helper & model
$com_helper         = JPATH_BASE . '/components/com_spproperty/helpers/helper.php';
$com_property_model = JPATH_BASE . '/components/com_spproperty/models/properties.php';

if (file_exists($com_helper) && file_exists($com_property_model)) {
    require_once($com_helper);
    require_once($com_property_model);
} else {
    echo '<p class="alert alert-warning">' . Text::_('MOD_SPPROPERTY_COMPONENT_NOT_INSTALLED_OR_MISSING_FILE') . '</p>';
    return;
}

$doc = Factory::getDocument();
$doc->addScript(Uri::root(true) . '/modules/' . $module->module . '/assets/js/spemicalc.js');
$doc->addScript(Uri::root(true) . '/modules/' . $module->module . '/assets/js/chart.min.js');
$doc->addStyleSheet(Uri::root(true) . '/modules/' . $module->module . '/assets/css/style.css');


// get component params
$cParams    = ComponentHelper::getParams('com_spproperty');



require ModuleHelper::getLayoutPath('mod_spproperty_emi_calculator', $params->get('layout'));
