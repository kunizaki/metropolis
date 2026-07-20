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
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;

class SppropertyModelGalleries extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('site');
        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
        $this->setState('filter.language', Multilanguage::isEnabled());
        $menu = $app->getMenu()->getActive();
        ($menu) ? $params = $menu->getParams() : $params = new Registry();
        $limit = $params->get('limit', null);
        if (!empty($limit)) {
            $this->setState('list.limit', $limit);
        }
    }

    // build query
    protected function getListQuery()
    {
        //Menu
        $app        = Factory::getApplication();
        $menu       = $app->getMenu()->getActive();
        ($menu) ? $mParams = $menu->getParams() : $mParams = new Registry();
        $catid      = $mParams->get('catid', null);
        $pstatus    = $mParams->get('property_status', null);
        $agentPro   = $mParams->get('agentid', null);
        $orderBy    = $mParams->get('order_by', 'asc');

        //Database
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));
        $query->select($db->quoteName('b.title', 'category_name'));
        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');

        if (!empty($catid)) {
            $query->where($db->quoteName('a.category_id') . ' = ' . $db->quote($catid));
        }

        if (!empty($pstatus)) {
            $query->where($db->quoteName('a.property_status') . ' = ' . $db->quote($pstatus));
        }

        if (!empty($agentPro)) {
            $query->where($db->quoteName('a.agent_id') . ' = ' . $db->quote($agentPro));
        }
        $query->where($db->qn('a.published') . " = " . $db->quote('1'));

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }
        if ($orderBy == 'featured') {
            $query->where($db->quoteName('a.featured') . ' = 1');
        }
        $query->order($db->quoteName('a.ordering') . ' ' . $orderBy);
        return $query;
    }
}
