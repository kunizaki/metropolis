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
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Multilanguage;

class SppropertyModelAgent extends ItemModel
{
    protected $_context = 'com_spproperty.agent';

    protected function populateState()
    {
        $app = Factory::getApplication('site');
        $itemId = $app->input->getInt('id');
        $this->setState('agent.id', $itemId);
        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    public function getItem($itemId = null)
    {
        $user = Factory::getUser();
        $itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('agent.id');

        if ($this->_item == null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$itemId])) {
            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('a.*');
                $query->from($db->quoteName('#__spproperty_agents', 'a'));
                $query->where('a.id = ' . (int) $itemId);

                // Filter by published state.
                $query->where('a.published = 1');

                if ($this->getState('filter.language')) {
                    $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
                }

                //Authorised
                $groups = implode(',', $user->getAuthorisedViewLevels());
                $query->where('a.access IN (' . $groups . ')');

                $db->setQuery($query);
                $data = $db->loadObject();

                $this->_item[$itemId] = $data;
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
                if ($e->getCode() != 404) {
                    $this->_item[$itemId] = false;
                }
            }
        }
        $this->_item[$itemId]->isAgent = 0;
        return $this->_item[$itemId];
    }

    // Get agent properties
    public static function getAgntProperties($agentid = '', $limit = '')
    {
        $agentid = (int)$agentid;

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->select($db->quoteName('b.title', 'category_name'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));
        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');
        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.agent_id') . ' = ' . $agentid);
        $query->where($db->quoteName('a.published') . " = 1");
        if ($limit) {
            $query->setLimit($limit);
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();


        foreach ($results as &$result) {
            $result->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $result->id . ':' . $result->alias . SppropertyHelper::getItemid('properties'));
        }
        return $results;
    }
}
