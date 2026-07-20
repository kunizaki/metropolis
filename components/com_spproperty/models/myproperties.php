<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyModelMyproperties extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('site');
        $params = $app->getParams();
        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
        $this->setState('filter.language', Multilanguage::isEnabled());
        $params = ComponentHelper::getParams('com_spproperty');
        $limit = $params->get('properties_limit', 6);
        $this->setState('list.limit', $limit);
    }

    // build query
    protected function getListQuery()
    {
        //Menu
        $app            = Factory::getApplication();
        $menu           = $app->getMenu()->getActive(); // get the active item
        $params         = $menu->getParams(); // get the active item
        $user           = Factory::getUser();

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));

        $query->select($db->quoteName('b.title', 'category_name'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));
        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');

        if (!$user->authorise('core.admin')) {
            $agent_id = $this->getUserAgentId($user->get('id'));
            $query->where($db->quoteName('a.agent_id') . ' = ' . $db->quote($agent_id));
        } else {
            $query->where($db->quoteName('a.agent_id') . ' = -1');
        }

        //Enabled
        $query->where($db->qn('a.published') . " = " . $db->quote('1'));
        $query->order($db->quoteName('a.ordering') . ' DESC');

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }
        return $query;
    }

    private function getUserAgentId($userid)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id')->from($db->quoteName('#__spproperty_agents', 'a'))
            ->where($db->quoteName('a.created_by') . ' = ' . $db->quote($userid))
            ->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        return $db->loadResult();
    }
}
