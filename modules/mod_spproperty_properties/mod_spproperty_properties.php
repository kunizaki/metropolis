<?php

/**
* @package mod_spproperty_properties
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
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

//includes js and css
$doc = Factory::getDocument();
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/spproperty-structure.css');
$doc->addStylesheet(Uri::root(true) . '/modules/' . $module->module . '/assets/css/style.css');
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/font-awesome.min.css');
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/style.css');
$doc->addScript(Uri::root(true) . '/modules/' . $module->module . '/assets/js/mod_properties.js');

//owl carousel
if (!file_exists(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css')) {
    $doc->addStyleSheet(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css');
}
if (!file_exists(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js')) {
    $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js');
}
if (!file_exists(Uri::root(true) . '/components/com_spproperty/assets/css/owl.theme.default.min.css')) {
    $doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/owl.theme.default.min.css');
}

$cParams            = ComponentHelper::getParams('com_spproperty');
// Get Columns
$columns = $params->get('columns', 2);
// Get items
$properties         = SppropertyModelProperties::getAllProperties($params);

foreach ($properties as $property) {
    $property->property_status_txt = $property->property_status;
    if ($property->property_status == 'rent') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_RENT');
    } elseif ($property->property_status == 'sell') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_SELL');
    } elseif ($property->property_status == 'in_hold') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_IN_HOLD');
    } elseif ($property->property_status == 'pending') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_IN_PENDING');
    } elseif ($property->property_status == 'sold') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_IN_SOLD');
    } elseif ($property->property_status == 'under_offer') {
        $property->property_status_txt = Text::_('MOD_SPPROPERTY_PROPERTOES_STATUS_IN_UNDER_OFFER');
    }

    $property->solid_price = $property->price;
    $property->price = SppropertyHelper::generateCurrency($property->price, $property->currency, $property->currency_position, $property->currency_format);
    $property->thumb = SppropertyHelper::getThumbs($property->image, 'property_thumbnail', '360x207');
}

$catid = $params->get('catid', null);

$menuItemId = SppropertyHelper::getItemid('properties', array(
    array('params', 'like', '%"property_carousel":"default"%'),
    array('params', 'like', '%"catid":""%'),
    array('params', 'like', '%"agentid":""%'),
    array('params', 'like', '%"property_status":""%')
));

$countResult = SppropertyModelProperties::countProperties($catid);

$seeAllUrl = '';
$seeAllBtn = $params->get('see_all_btn', 1);
if ($seeAllBtn) {
    if ($countResult > count($properties)) {
        if (!is_null($catid)) {
            $seeAllUrl  = Route::_('index.php?option=com_spproperty&view=properties&catid=' . $catid . $menuItemId);
        } else {
            $seeAllUrl  = Route::_('index.php?option=com_spproperty&view=properties' . $menuItemId);
        }
    } else {
        $seeAllUrl  = '';
    }
}


$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';

require ModuleHelper::getLayoutPath('mod_spproperty_properties', $params->get('layout'));
