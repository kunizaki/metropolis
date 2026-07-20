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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Multilanguage;

class SppropertyModelProperty extends ItemModel
{
    protected $_context = 'com_spproperty.property';

    protected function populateState()
    {
        $app = Factory::getApplication('site');
        $itemId = $app->input->getInt('id');
        $this->setState('property.id', $itemId);
        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    /**
     * Handle old data and convert to new
     * @param data the old data
     * @param keys key exists on the data
     * @param prefix index name prefix
     */
    public function old2new($data, $keys = array(), $prefix = '')
    {
        if (empty($data)) {
            $data = [];
        }
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        $index = 0;
        $arr = array();
        foreach ($data[$keys[0]] as $i => $d) {
            $temp = array();
            foreach ($keys as $k) {
                $temp[$k] = $data[$k][$i];
            }

            $arr[$prefix . $index] = $temp;
            $index++;
        }
        return $arr;
    }

    public function getItem($itemId = null)
    {
        $user = Factory::getUser();

        $itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('property.id');

        if ($this->_item == null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$itemId])) {
            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('a.*');
                $query->from($db->quoteName('#__spproperty_properties', 'a'));
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

                if (empty($data)) {
                    throw new Exception(Text::_('COM_SPPROPERTY_ERROR_ITEM_NOT_FOUND'), 404);
                }

                $user = Factory::getUser();
                $groups = $user->getAuthorisedViewLevels();
                if (!in_array($data->access, $groups)) {
                    throw new Exception(Text::_('COM_SPPROPERTY_ERROR_NOT_AUTHORISED'), 404);
                }

                $this->_item[$itemId] = $data;
            } catch (Exception $e) {
                throw new Exception($e->getMessage(), $e->getCode());
                if ($e->getCode() != 404) {
                    $this->_item[$itemId] = false;
                }
            }
        }

        $this->_item[$itemId]->features = !empty($this->_item[$itemId]->features) ? json_decode($this->_item[$itemId]->features, true) : '';
        $this->_item[$itemId]->gallery  = !empty($this->_item[$itemId]->gallery) ? json_decode($this->_item[$itemId]->gallery, true) : '';

        if (!is_bool($gallery = $this->old2new($this->_item[$itemId]->gallery, ['photo', 'alt_text'], 'gallery'))) {
            $this->_item[$itemId]->gallery = $gallery;
        }
        $this->_item[$itemId]->floor_plans = json_decode($this->_item[$itemId]->floor_plans, true);
        if (!is_bool($floor_plans = $this->old2new($this->_item[$itemId]->floor_plans, ['img', 'layout_name', 'text'], 'floor_plans'))) {
            $this->_item[$itemId]->floor_plans = $floor_plans;
        }

        return $this->_item[$itemId];
    }

    // Get Category info by ID
    public static function getCatInfo($catid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_categories', 'a'));
        $query->where($db->quoteName('a.id') . '=' . $db->quote($catid));

        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }


    // Get features
    public static function getPfeatures($fetid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_propertyfeatures', 'a'));
        if ($fetid) {
            $query->where($db->quoteName('a.id') . '=' . $fetid);
        }
        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);

        if ($fetid) {
            $results = $db->loadObject();
        } else {
            $results = $db->loadObjectList();
        }

        return $results;
    }


    // Get Agent info by id
    public static function getAgntInfo($agntid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*');
        $query->from($db->quoteName('#__spproperty_agents', 'a'));
        $query->where($db->quoteName('a.id') . ' = ' . $agntid);
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    public function getAgentUserProfile($agentid)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('up.*, u.name, u.email')
        ->from($db->qn('#__user_profiles', 'up'))
        ->join('LEFT', $db->qn('#__users', 'u') . ' ON ( ' . $db->qn('up.user_id') . ' = ' . $db->qn('u.id') . ' )')
        ->where($db->qn('up.user_id') . ' = ' . $db->q($agentid));

        $db->setQuery($query);
        $results = $db->loadObjectList();

        $object = new stdClass();
        foreach ($results as $agent) {
            if (isset($agent->profile_key) && $agent->profile_key) {
                $key = explode('.', $agent->profile_key);
                if (count($key) > 1) {
                    $key        = $key[1];
                    $object->$key  = json_decode($agent->profile_value, true);
                    $object->title = $agent->name;
                    $object->email = $agent->email;
                    $object->id    = $agent->user_id;
                }
            }
        }
        return $object;
    }
}
