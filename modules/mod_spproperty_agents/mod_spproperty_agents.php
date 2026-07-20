<?php

/**
* @package mod_spproperty_agents
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
$com_agents_model = JPATH_BASE . '/components/com_spproperty/models/agents.php';

if (file_exists($com_helper) && file_exists($com_agents_model)) {
    require_once($com_helper);
    require_once($com_agents_model);
} else {
    echo '<p class="alert alert-warning">' . Text::_('MOD_SPPROPERTY_COMPONENT_NOT_INSTALLED_OR_MISSING_FILE') . '</p>';
    return;
}

$agents     = SppropertyModelAgents::getAllAgents($params);
$columns    = $params->get('columns', 3);

//includes js and css
$doc = Factory::getDocument();
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/spproperty-structure.css');
$doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/style.css');


foreach ($agents as $agent) {
    $agent->url = Route::_('index.php?option=com_spproperty&view=agent&id=' . $agent->id . ':' . $agent->alias . SppropertyHelper::getItemid('agents'));
    $agent->thumb = SppropertyHelper::getThumbs($agent->image, 'agent_thumbnail', '90x90');
}

$moduleclass_sfx = ($params->get('moduleclass_sfx')) ? htmlspecialchars($params->get('moduleclass_sfx')) : '';
require ModuleHelper::getLayoutPath('mod_spproperty_agents', $params->get('layout'));
