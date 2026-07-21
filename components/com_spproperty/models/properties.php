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
use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;

class SppropertyModelProperties extends ListModel
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
        //Frontend
        $isSite = Factory::getApplication()->isClient('site');
        $user   = Factory::getUser();

        $app              = Factory::getApplication();
        $menu             = $app->getMenu()->getActive(); // get the active item
        ($menu) ? $params = $menu->getParams() : $params = new Registry();

        $input               = Factory::getApplication()->input;
        $sort_catid          = $input->get('catid', null, 'INT');
        $keyword             = $input->get('keyword', null, 'STRING');
        $city                = $input->get('city', null, 'STRING');
        $minsize             = $input->get('minsize', null, 'INT');
        $maxsize             = $input->get('maxsize', null, 'INT');
        $beds                = $input->get('beds', null, 'INT');
        $baths               = $input->get('baths', null, 'INT');
        $parking             = $input->get('parking', null, 'INT');
        $zipcode             = $input->get('zipcode', null, 'STRING');
        $min_price           = $input->get('min_price', null, 'INT');
        $max_price           = $input->get('max_price', null, 'INT');
        $sorting             = $input->get('sorting', null, 'STRING');
        $pfeatures           = $input->get('p_features', null, 'STRING');
        $searchitem          = $input->get('searchitem', 0);
        $agentid             = $params->get('agentid', '');
        $order_by            = $params->get('order_by', '');
        $lvlftno_dropdown    = $input->get('lvlftno_dropdown','','STRING');
        $lvlftno_input_field = $input->get('lvlftno_inputfield', '', 'STRING');
        $propertySizeDropdown = $input->get('psize_range_dropdown', '', 'STRING');
        $propertyPriceDropdown = $input->get('price_range_dropdown', '', 'STRING');
        $pstatus             = null;

        if ($searchitem) {
            $sort_catid = $input->get('catid', null, 'INT');
            $pstatus    = $input->get('pstatus', null, 'STRING');
        } else {
            //Menu params
            if (($catid = $params->get('catid', null)) && empty($sort_catid)) {
                $sort_catid = $catid;
            }
            if ($property_status = $params->get('property_status', null)) {
                $pstatus = $property_status;
            }
        }

        if (!is_null($sorting)) {
            list($sortOrder, $sortDirn) = explode('-', $sorting);
        }
        if (!is_null($pfeatures)) {
            $pfeatures = explode('-', $pfeatures);
        }


        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));

        $query->select($db->quoteName('b.title', 'category_name'));

        $query->from($db->quoteName('#__spproperty_properties', 'a'));

        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');


        if ($sort_catid) {
            $query->where($db->quoteName('a.category_id') . '=' . $sort_catid);
        }

        if (!empty($keyword) && ltrim($keyword)) {
            $keyword = preg_replace('#\xE3\x80\x80#s', " ", trim($keyword));
            $keyword_array = explode(" ", $keyword);
            $query_string = implode("|", $keyword_array);
            $query->where('(' . $db->quoteName('a.title') . ' REGEXP ' . $db->quote($query_string) . ' OR ' . $db->qn('a.description') . ' REGEXP ' . $db->q($query_string) . ' OR ' . $db->qn('a.property_id') . ' = ' . $db->q(trim($keyword)) . ')');
        }

        if ($city) {
            $city = preg_replace('#\xE3\x80\x80#s', " ", trim($city));
            $city_array = explode(" ", $city);
            $city_string = implode("|", $city_array);
            $query->where($db->quoteName('a.city') . ' REGEXP ' . $db->quote($city_string));
        }

        if ($pstatus) {
            $query->where($db->quoteName('a.property_status') . '=' . $db->quote($pstatus));
        }

        if (isset($minsize)) {
            $query->where($db->quoteName('a.psize') . '>=' . $db->quote($minsize));
        }


        if (isset($maxsize)) {
            $query->where($db->quoteName('a.psize') . '<=' . $db->quote($maxsize));
        }

        if ($propertySizeDropdown) {
            $sizeRange = explode('_',$propertySizeDropdown);
            $query->where($db->quoteName('a.psize') . ' BETWEEN ' . $sizeRange[0] . ' AND ' . $sizeRange[1]);
        }

        if ($propertyPriceDropdown) {
            $priceRange = explode('_', $propertyPriceDropdown);
            $query->where($db->quoteName('a.price') . ' BETWEEN ' . $priceRange[0] . ' AND ' . $priceRange[1]);
        }

        if ($lvlftno_dropdown) {
            $lvlftno_dropdown = str_replace('%20',' ',$lvlftno_dropdown);
            $query->where($db->quoteName('a.lvl_fltno') . '=' . $db->quote($lvlftno_dropdown));
        }

        if ($lvlftno_input_field) {
           $lvlftno_dropdown = str_replace('%20', ' ', $lvlftno_input_field);
           $query->where($db->quoteName('a.lvl_fltno') . ' REGEXP ' . $db->quote($lvlftno_input_field));
        }

        if ($beds) {
            $query->where($db->quoteName('a.beds') . '=' . $beds);
        }

        if ($baths) {
            $query->where($db->quoteName('a.baths') . '=' . $baths);
        }

        if ($agentid) {
            $query->where($db->quoteName('a.agent_id') . '=' . $agentid);
        }

        if ($parking) {
            $query->where($db->quoteName('a.garages') . '=' . $parking);
        }

        if ($zipcode) {
            $query->where($db->quoteName('a.zip') . '=' . $zipcode);
        }

        if (isset($min_price)) {
            $query->where($db->quoteName('a.price') . '>=' . $min_price);
        }

        if (isset($max_price)) {
            $query->where($db->quoteName('a.price') . '<=' . $max_price);
        }

        if ($pfeatures) {
            $features = $this->getFeaturesInfo($pfeatures);
            $properties = [];
            foreach ($features as $key => $feature) {
                $properties[] = $feature->id;
            }
            $query->where($db->quoteName('a.id') . ' IN (' . implode(',', $properties) . ')');
        }

        //Enabled
        $query->where($db->qn('a.published') . " = " . $db->quote('1'));
        // ordering
        if (isset($sortOrder) && isset($sortDirn)) {
            $query->order($db->quoteName('a.' . $sortOrder) . ' ' . $sortDirn);
        } else {
            if ($order_by == 'asc') {
                $query->order($db->quoteName('a.ordering') . ' ASC');
            } elseif ($order_by == 'featured') {
                $query->where($db->quoteName('a.featured') . ' = 1');
                $query->order($db->quoteName('a.ordering') . ' DESC');
            } elseif ($order_by == 'latest') {
                $query->order($db->quoteName('a.modified') . ' DESC');
            } elseif ($order_by == 'oldest') {
                $query->order($db->quoteName('a.modified') . ' ASC');
            } else {
                $query->order($db->quoteName('a.modified') . ' DESC');
            }
        }

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }

        return $query;
    }

    public function isFavorite($property_id, $user_id)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*');
        $query->from($db->quoteName('#__spproperty_favourites', 'a'))
            ->where($db->quoteName('a.user_id') . ' = ' . $db->quote($user_id))
            ->where($db->quoteName('a.property_id') . ' = ' . $db->quote($property_id));

        $db->setQuery($query);
        $result = $db->loadObject();
        if (!empty($result)) {
            return true;
        }
        return false;
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

        $order_by = $params->get('order_by', 'latest');

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*');
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

        if ($property_status = $params->get('property_status', '')) {
            $query->where($db->quoteName('a.property_status') . ' = ' . $db->quote($property_status));
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
            $query->order($db->quoteName('a.modified') . ' DESC');
        }
        $db->setQuery($query);
        $results = $db->loadObjectList();

        foreach ($results as &$result) {
            $result->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $result->id . ':' . $result->alias . SppropertyHelper::getItemid('properties', [['params', 'like', '%"property_status":"' . $result->property_status . '"%']]));
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

        $menuItemId = SppropertyHelper::getItemid('properties', array(
            array('params', 'like', '%"property_carousel":"default"%')
        ));


        foreach ($results as &$item) {
            $item->property_status_txt = '';
            if ($item->property_status == 'rent') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
                $item->badge_class = 'is-orange';
            } elseif ($item->property_status == 'sell') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
                $item->badge_class = 'is-primary';
            } elseif ($item->property_status == 'in_hold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
                $item->badge_class = 'is-warning';
            } elseif ($item->property_status == 'pending') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
                $item->badge_class = 'is-warning';
            } elseif ($item->property_status == 'sold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
                $item->badge_class = 'is-success';
            } elseif ($item->property_status == 'under_offer') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
                $item->badge_class = 'is-danger';
            }

            $item->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $item->id . ':' . $item->alias . $menuItemId);
            $item->solid_price = $item->price;
            $item->price = SppropertyHelper::generateCurrency($item->price);
            $item->thumb = SppropertyHelper::getThumbs($item->image, 'property_thumbnail', '360x207');
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

        // Language
        $query->where('a.language in (' . $db->quote(Factory::getApplication()->getLanguage()->getTag()) . ',' . $db->quote('*') . ')');

        $db->setQuery($query);
        
        $result = $db->loadObjectList();

        $arr = array_reduce($result, function ($carry, $item) {
                if (!empty($item->city)) {
                    $carry[$item->city] = $item->city;
                }

                return $carry;
            }, []);

        return $arr;

    }


    // Get property types
    public static function getCategories($limit = '', $ordering = 'DESC', $pstatus = '', $itemid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_categories', 'a'));

        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.published') . " = 1");

        if ($ordering != 'featured' && $ordering != 'latest' && $ordering != 'oldest') {
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
            $result->url = Route::_('index.php?option=com_spproperty&view=properties&catid=' . $result->id . ':' . $result->alias . (!empty($itemid) ? $itemid : SppropertyHelper::getItemid('properties')));
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

        // if ($cat_id) {
        //  $query->where($db->quoteName('a.spproperty_category_id').'=' . $cat_id);
        // }

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
    public static function getAgntInfo($agentid = '')
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_agents', 'a'));
        $query->where($db->quoteName('a.id') . '=' . $agentid);
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

    /**
     * Get unique flat and floor numbers from the #__spproperty_properties table.
     *
     * @return array -- An associative array with 'Select' as the default option and unique flat and floor numbers as 
     *                  values.
     * @since 4.0.6
     */

    public static function getFlatAndFloorNumbers()
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
                ->select('DISTINCT ' . $db->quoteName('lvl_fltno'))
                ->from($db->quoteName('#__spproperty_properties'))
                ->where($db->quoteName('published') . ' = 1');;

        $db->setQuery($query);

        $result = $db->loadColumn();
        
        $finalResult = array_merge(['' => 'Select Flat and Floor No '], array_combine($result, $result));
        
        return $finalResult;
    }

    /**
     * Retrieves the maximum values for price and psize from the #__spproperty_properties table.
     *
     * @return array -- An associative array containing the maximum price and psize.
     * @since  4.0.6           
     */

    public static function getMaxMinDataRangeDropdown()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('MAX(a.price) as maxPrice, MIN(a.price) as minPrice, MAX(a.psize) as maxSize, MIN(a.psize) as minSize')
            ->from($db->quoteName('#__spproperty_properties', 'a'))
            ->where($db->quoteName('a.published') . ' = 1');

        $db->setQuery($query);
        $result = $db->loadObject();

        return [
            'size' => [
                'max' => $result->maxSize,
                'min' => $result->minSize               
            ],
            'price' => [
                'max' => $result->maxPrice,
                'min' => $result->minPrice
            ]             
        ];
    }

    /**
     * Generates a dropdown data array for a specified range and unit.
     *
     * @param array|null $maxMinData  The maximum and minimum value for the range.
     * @param int        $range       The increment value for the range.
     * @param string     $type        The type of data (e.g., "Price", "Size").
     * @param string     $unit        The unit for the data (optional).
     *
     * @return array An associative array suitable for dropdown options.
     * @since  4.0.6              
     */

    public static function getRangeDropdownData($maxMinData, $range, $type, $unit = null)
    {
        $result            = ["" => "Select $type "];
        $maxMinData['min'] = $maxMinData['min'] ?: 1;

        if ($maxMinData['max'] && $maxMinData['min']) 
        {
            $ranges = range($maxMinData['min'], $maxMinData['max'], $range);
            
            array_walk($ranges, function ($value) use (&$result, $range, $unit) {
                
                $start = $value;
                $end   = $start + ($range-1);
                
                $result[$start . '_' . $end] = $start . $unit . ' - ' . $end . $unit;

            });
        }

        return $result;
    }
}
