<?php

/**
* @package mod_spproperty_search
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Component\ComponentHelper;

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
// get component params
$cParams    = ComponentHelper::getParams('com_spproperty');

$doc = Factory::getDocument();
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/style.css');
$doc->addStylesheet(Uri::root(true) . '/modules/' . $module->module . '/assets/css/nouislider.min.css');
$doc->addStylesheet(Uri::root(true) . '/modules/' . $module->module . '/assets/css/style.css');
$doc->addScript(Uri::root(true) . '/modules/' . $module->module . '/assets/js/nouislider.min.js');
$doc->addScript(Uri::base(true) . '/modules/' . $module->module . '/assets/js/spproperty-search.js');


$maxData              = SppropertyModelProperties::getMaxValues();
$maxPrice             = (isset($maxData['maxPrice']) && $maxData['maxPrice']) ? $maxData['maxPrice'] : 100;
$maxSize              = (isset($maxData['maxSize']) && $maxData['maxSize']) ? $maxData['maxSize'] : 100;
$currency             = $cParams->get('currency', 'USD:$');
$measurement          = $cParams->get('measurement', Text::_('COM_SPPROPERTY_PROPERTIES_SQFT'));
$flatAndFloorNoList   = null;
$maxMinDataRangeDropdown = null;
$priceRange           = $params->get('price_range');
$sizeRange            = $params->get('size_range');

if ($params->get('show_lvlftno')) {
    $flatAndFloorNoList = $params->get('show_lvlftno_option') === 'dropdown' ? SppropertyModelProperties::getFlatAndFloorNumbers() : null;
}

if ($params->get('ranger') === 'range-dropdown') {
    $maxMinDataRangeDropdown = SppropertyModelProperties::getMaxMinDataRangeDropdown() ?: null;
}

if ($params->get('show_sizerange')  && !is_null($maxMinDataRangeDropdown)) {
    $propertySizeRangeDropdownData = SppropertyModelProperties::getRangeDropdownData($maxMinDataRangeDropdown['size'],$sizeRange,'size',$measurement);
}

if ($params->get('show_pricerange') && !is_null($maxMinDataRangeDropdown)) {
    $propertyPriceRangeDropdownData = SppropertyModelProperties::getRangeDropdownData($maxMinDataRangeDropdown['price'], $priceRange,'price');
}

$doc->addScriptDeclaration("
    var maxPrice = " . $maxPrice . ";
    var maxSize = " . $maxSize . ";
    var currency = '" . (explode(':', $currency)[1]) . "';
    var measurement = '" . $measurement . "';
    var ranger = '" . $params->get('ranger', 'inputbox') . "';
    var view = '" . $params->get('view', 'properties') . "';
");


$getItems   = SppropertyModelProperties::getAllProperties($params);
$items = array();
foreach ($getItems as $element) {
    $hash = $element->city;
    $items[$hash] = $element;
}

$items      = SppropertyModelProperties::getCities();
$cats       = SppropertyModelProperties::getCategories(0);
$features   = SppropertyModelProperties::getPfeatures();

$gparams = Factory::getApplication()->getParams();
$mParams = Factory::getApplication()->getMenu()->getActive()->getParams();

$input      = Factory::getApplication()->input;
$catid      = $input->get('catid', null, 'INT');

$map_layout = $gparams->get('layout_type', '_:default');
$search_view = $params->get('view', 'properties');
if ($search_view == 'properties') {
    $menuItemId = SppropertyHelper::getItemid('properties', array(
        array('params', 'like', '%"property_carousel":"' . $mParams->get('property_carousel', 'default') . '"%'),
        array('params', 'like', '%"catid":"' . $mParams->get('catid', '') . '"%'),
        array('params', 'like', '%"agentid":"' . $mParams->get('agentid', '') . '"%'),
        array('params', 'like', '%"property_status":"' . $mParams->get('property_status', '') . '"%')
    ));
} elseif ($search_view == 'maps') {
    $menuItemId = SppropertyHelper::getItemid('maps', array(
            array('params', 'like', '%"layout_type":"' . $map_layout . '"%'),
            array('params', 'like', '%"catid":"' . $catid . '"%'),
        ));
} else {
    $menuItemId = SppropertyHelper::getItemid($search_view);
}

require ModuleHelper::getLayoutPath('mod_spproperty_search', $params->get('layout'));
