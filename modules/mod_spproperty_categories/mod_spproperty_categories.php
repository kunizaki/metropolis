<?php

/**
* @package mod_spproperty_categories
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

HTMLHelper::_('jquery.framework');

//helper & model
$com_helper         = JPATH_BASE . '/components/com_spproperty/helpers/helper.php';
$com_categories_model = JPATH_BASE . '/components/com_spproperty/models/categories.php';

if (file_exists($com_helper) && file_exists($com_categories_model)) {
    require_once($com_helper);
    require_once($com_categories_model);
} else {
    echo '<p class="alert alert-warning">' . Text::_('MOD_SPPROPERTY_COMPONENT_NOT_INSTALLED_OR_MISSING_FILE') . '</p>';
    return;
}

$doc        = Factory::getDocument();
$doc->addStyleSheet(Uri::root(true) . '/modules/' . $module->module . '/assets/css/style.css');
$categories = SppropertyModelCategories::getAllCategories($params);
$menuItemId = SppropertyHelper::getItemid('properties', array(
    array('params', 'like', '%"property_carousel":"default"%')
));
foreach ($categories as $category) {
    $category->url = Route::_('index.php?option=com_spproperty&view=properties&catid=' . $category->id . ':' . $category->alias . $menuItemId);
}


$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';
require ModuleHelper::getLayoutPath('mod_spproperty_categories', $params->get('layout'));
