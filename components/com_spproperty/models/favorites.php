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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;

class SppropertyModelFavorites extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('site');
        $params = $app->getParams();
        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
        $this->setState('filter.language', Multilanguage::isEnabled());
        $limit = $params->get('properties_limit');
        $this->setState('list.limit', $limit);
    }

    // build query
    protected function getListQuery()
    {
        $user           = Factory::getUser();
        $input          = Factory::getApplication()->input;
        $sort_catid     = $input->get('catid', null, 'INT');
        $pstatus        = $input->get('pstatus', null, 'STRING');

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));

        $query->select($db->quoteName('b.title', 'category_name'));
        $query->select('f.user_id AS fav_user_id');
        $query->from($db->quoteName('#__spproperty_properties', 'a'));

        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');
        $query->join('LEFT', $db->quoteName('#__spproperty_favourites', 'f') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('f.property_id') . ')');
        // Get menu Params
        $app            = Factory::getApplication();
        $menu           = $app->getMenu()->getActive(); // get the active item
        $params         = $menu->getParams(); // get the active item
        $order_by       = $params->get('order_by', '');
        $catid          = $params->get('catid', '');
        $agentid        = $params->get('agentid', '');
        $pstatus        = ($pstatus) ? $pstatus : $params->get('property_status', '');

        $query->where($db->quoteName('f.user_id') . ' = ' . $db->quote($user->id));

        if ($pstatus) {
            $query->where($db->quoteName('a.property_status') . '=' . $db->quote($pstatus));
        }

        //Enabled
        $query->where($db->qn('a.published') . " = " . $db->quote('1'));
        $query->order($db->quoteName('a.ordering') . ' ASC');

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }
        return $query;
    }

    //Get all properties with the following property features
    private function getFeaturesInfo($features = array())
    {
        if (!is_array($features)) {
            $features = array();
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id');
        $query->from($db->qn('#__spproperty_properties', 'a'));

        $properties = [];
        $conditions = [];
        foreach ($features as $feature) {
            $conditions[] = $db->qn('a.features') . ' LIKE ' . $db->q('%"' . $feature . '"%');
        }
        $query->where(implode(' OR ', $conditions));
        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }

    //if item not found
    public function &getItem($id = null)
    {
        $item = parent::getItem($id);
        $isSite = Factory::getApplication()->isClient('site');
        if ($isSite) {
            if ($item->spproperty_property_id) {
                return $item;
            } else {
                throw new Exception(Text::_('COM_SPPROPERTY_NO_ITEMS_FOUND'), 404);
            }
        } else {
            return $item;
        }
    }


    public static function getAllProperties($params = '', $limit = '')
    {

        $order_by = $params->get('order_by', 'DESC');

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('a.id', 'a.title', 'a.alias', 'a.image', 'a.description', 'a.category_id', 'a.price', 'a.psize', 'a.beds', 'a.baths', 'a.garages', 'a.country' ,'a.city', 'a.property_status', 'a.price_request', 'a.rent_period')));
        $query->select($db->quoteName('b.title', 'category_name'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));
        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');

        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

        if ($catid = $params->get('catid')) {
            $query->where($db->quoteName('a.category_id') . '=' . $catid);
        }

        if ($agentid = $params->get('agentid')) {
            $query->where($db->quoteName('a.agent_id') . '=' . $agentid);
        }

        if ($limit = $params->get('limit', 6)) {
            $query->setLimit($limit);
        }

        $query->where($db->quoteName('a.published') . " = 1");

        if ($order_by == 'asc') {
            $query->order($db->quoteName('a.ordering') . ' ASC');
        } elseif ($order_by == 'featured') {
            $query->where($db->quoteName('a.featured') . ' = 1');
            $query->order($db->quoteName('a.ordering') . ' DESC');
        } else {
            $query->order($db->quoteName('a.ordering') . ' DESC');
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as &$result) {
            $result->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $result->id . ':' . $result->alias . SppropertyHelper::getItemid('properties'));
        }

        return $results;
    }


    public function getSelectedProperties($properties)
    {


        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*, b.title as category_name')
        ->from($db->qn('#__spproperty_properties', 'a'));
        if (count($properties)) {
            $query->where($db->qn('a.id') . ' IN (' . implode(',', $properties) . ') ');
        } else {
            $query->where($db->quoteName('a.id') . ' <= 0');
        }
        $query->where($db->qn('a.published') . ' = 1');

        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');
        if (count($properties)) {
            $query->order('FIELD (a.id, ' . implode(',', $properties) . ')');
        }
        $db->setQuery($query);

        $results = $db->loadObjectList();


        foreach ($results as &$item) {
            $item->property_status_txt = '';
            if ($item->property_status == 'rent') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
            } elseif ($item->property_status == 'sell') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
            } elseif ($item->property_status == 'in_hold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
            } elseif ($item->property_status == 'pending') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
            } elseif ($item->property_status == 'sold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
            } elseif ($item->property_status == 'under_offer') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
            }

            $item->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $item->id . ':' . $item->alias . SppropertyHelper::getItemid('properties'));
            $item->price = SppropertyHelper::generateCurrency($item->price);
            $item->thumb = SppropertyHelper::getThumbs($item->image, 'property_thumbnail_large', '640x715');
        }

        return $results;
    }

    public static function getCities()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.city')
        ->from($db->qn('#__spproperty_properties', 'a'));
        $query->order($db->qn('a.city') . ' ASC');

        $db->setQuery($query);
        $result = $db->loadObjectList();
        $arr = array();
        foreach ($result as $key => $r) {
            $arr[$r->city] = $r->city;
        }
        return $arr;
    }


    // Get property types
    public static function getCategories($limit = '', $ordering = 'DESC', $pstatus = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_categories', 'a'));

        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.published') . " = 1");

        if ($ordering != 'featured') {
            $query->order('a.ordering ' . $ordering);
        } else {
            $query->order('a.ordering DESC');
        }

        if ($limit) {
            $query->setLimit($limit);
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as &$result) {
            $result->this_count     = self::countProperties($result->id, $ordering, $pstatus);
            $result->url = Route::_('index.php?option=com_spproperty&view=properties&catid=' . $result->id . ':' . $result->alias . SppropertyHelper::getItemid('properties'));
        }

        return $results;
    }

    // Count properties by id
    public static function countProperties($type_id = '', $ordering = '', $pstatus = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('COUNT(a.id)'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));

        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

        if ($type_id) {
            $query->where($db->quoteName('a.category_id') . '=' . $type_id);
        }

        if ($pstatus) {
            $query->where($db->quoteName('a.property_status') . '=' . $db->quote($pstatus));
        }

        if ($ordering == 'featured') {
            $query->where($db->quoteName('a.featured') . ' = 1');
        }

        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        $count = $db->loadResult();

        return $count;
    }

    public static function getMaxValues()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('MAX(a.price) as maxPrice, MAX(a.psize) as maxSize')
            ->from($db->quoteName('#__spproperty_properties', 'a'))
            ->where($db->quoteName('a.published') . ' = 1');

        $db->setQuery($query);
        $result = $db->loadObject();

        return array("maxPrice" => $result->maxPrice, "maxSize" => $result->maxSize);
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
        $query->order($db->quoteName('a.title') . ' ASC');
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
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_agents', 'a'));
        $query->where($db->quoteName('a.id') . '=' . $agntid);
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    // ajax booking
    public function insertBooking($pid = '', $name = '', $phone = '', $email = '', $message = '', $user_id = 0, $visitor_ip = '', $request_type = 'visit')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $columns = array('property_id','type', 'customer_name', 'customer_email', 'customer_phone', 'customer_comments', 'visitor_ip', 'created_by', 'created', 'published');
        $values = array($db->quote($pid),$db->quote($request_type), $db->quote($name), $db->quote($email), $db->quote($phone), $db->quote($message), $db->quote($visitor_ip), $db->quote($user_id), $db->quote(Factory::getDate()), 1);
        $query
            ->insert($db->quoteName('#__spproperty_visitrequests'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();

        return $db->insertid();
    }
    private function updateOrCreate($tbl_name, array $check, array $values)
    {
        $db         = Factory::getDbo();
        $query      = $db->getQuery(true);
        $conditions = array();
        $fields     = array();
        $columns    = array();
        $data       = array();
        $return     = null;

        $query->select('*')
            ->from($db->quoteName($tbl_name));

        foreach ($values as $key => $v) {
            $fields[]   = $db->quoteName($key) . ' = ' . $db->quote($v);
            $columns[]  = $key;
            $data[]     = $v;
        }

        foreach ($check as $key => $v) {
            $conditions[] = $db->quoteName($key) . ' = ' . $db->quote($v);
        }

        $query->where($conditions);

        $db->setQuery($query);
        $result = $db->loadObject();

        unset($db, $query);

        if (!empty($result)) {
            //Update
            $db         = Factory::getDbo();
            $query      = $db->getQuery(true);
            $query->update($db->quoteName($tbl_name));

            $query->set($fields)
                ->where($conditions);

            $db->setQuery($query);
            $db->execute();
            $return = 2;
        } else {
            $db         = Factory::getDbo();
            $query      = $db->getQuery(true);
            $query->insert($db->quoteName($tbl_name))
                ->columns($columns)
                ->values(implode(',', $data));
            $db->setQuery($query);
            $db->execute();
            $return = 1;
        }
        return $return;
    }

    public function handleFavourite($data, $flag = 1)
    {

        if ($flag == 1) {
            $this->updateOrCreate('#__spproperty_favourites', array(
                'user_id' => $data->user_id,
                'property_id' => $data->property_id
            ), array(
                'user_id' => $data->user_id,
                'property_id' => $data->property_id
            ));
        } else {
            $db     = Factory::getDbo();
            $query  = $db->getQuery(true);

            $conditions = array(
                $db->quoteName('user_id') . ' = ' . $data->user_id,
                $db->quoteName('property_id') . ' = ' . $data->property_id
            );

            $query->delete($db->quoteName('#__spproperty_favourites'))
                ->where($conditions);

            $db->setQuery($query);
            $db->execute();
        }
        return true;
    }
}
