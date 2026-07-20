<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class SppropertyModelCategories extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('site');
        $params = $app->getParams();
        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
        $this->setState('filter.language', Multilanguage::isEnabled());
        $active = $app->getMenu()->getActive();
        ($active) ? $mParams = $active->getParams() : $mParams = new Registry();
        $limit = $mParams->get('category_limit', 6);
        $this->setState('list.limit', $limit);
    }

    protected function getListQuery()
    {
        $app    = Factory::getApplication();
        $input  = $app->input;
        $menu   = $app->getMenu();
        $activeMenu = $menu->getActive();
        ($activeMenu) ? $mParams = $activeMenu->getParams() : $mParams = new Registry();
        $catids = $mParams->get('category_id', array());


        $category_id = '';
        if (!empty($catids)) {
            $category_id = implode(',', $catids);
        }

        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('a.*')
            ->from($db->quoteName('#__spproperty_categories', 'a'));
        if (!empty($category_id)) {
            $query->where($db->quoteName('a.id') . ' IN (' . $category_id . ')');
        }
        $query->where($db->quoteName('a.published') . ' = 1');

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }

        return $query;
    }

    public static function getAllCategories($params = null)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*')
            ->from($db->quoteName('#__spproperty_categories', 'a'))
            ->where($db->quoteName('a.published') . ' = 1');
        if (!empty($params)) {
            if ($limit = $params->get('limit', 0)) {
                $query->setLimit($limit);
            }

            if ($order = $params->get('ordering', 'asc')) {
                $query->order($db->quoteName('a.ordering') . ' ' . $order);
            }
        }

        $db->setQuery($query);
        return $db->loadObjectList();
    }
}
