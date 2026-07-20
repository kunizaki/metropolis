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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyModelMaps extends ListModel
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

    protected function getListQuery()
    {
        $isSite = Factory::getApplication()->isClient('site');

        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);

        $query->select('a.*, b.title as category_name')
            ->from($db->quoteName('#__spproperty_properties', 'a'))
            ->where($db->quoteName('a.published') . ' = 1');

        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');

        if ($this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        }

        return $query;
    }

    public function getMarkers($property_id = 0, $params = null)
    {

        // Read and get url search queries
        $input          = Factory::getApplication()->input;
        $keyword        = $input->get('keyword', null, 'STRING');
        $city           = $input->get('city', null, 'STRING');
        $minsize        = $input->get('minsize', null, 'INT');
        $maxsize        = $input->get('maxsize', null, 'INT');
        $beds           = $input->get('beds', null, 'INT');
        $baths          = $input->get('baths', null, 'INT');
        $parking        = $input->get('parking', null, 'INT');
        $zipcode        = $input->get('zipcode', null, 'STRING');
        $min_price      = $input->get('min_price', null, 'INT');
        $max_price      = $input->get('max_price', null, 'INT');
        $sorting        = $input->get('sorting', null, 'STRING');
        $pfeatures      = $input->get('p_features', null, 'STRING');
        $searchitem     = $input->get('searchitem', 0);
        $sort_catid     = null;
        $pstatus        = null;

        if ($searchitem) {
            $sort_catid = $input->get('catid', null, 'INT');
            $pstatus    = $input->get('pstatus', null, 'STRING');
        } else {
            //Menu params
            if ($catid = $params->get('catid', null)) {
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


        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('a.*')
            ->from($db->quoteName('#__spproperty_properties', 'a'));



        if (!empty($property_id)) {
            $query->where($db->quoteName('a.id') . ' = ' . $db->quote($property_id));
        } else {
            if ($agentid = $params->get('agentid', '')) {
                $query->where($db->quoteName('a.agent_id') . ' = ' . $db->quote($agentid));
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

            if ($sort_catid) {
                $query->where($db->quoteName('a.category_id') . ' = ' . $db->quote($sort_catid));
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


            if ($beds) {
                $query->where($db->quoteName('a.beds') . '=' . $beds);
            }

            if ($baths) {
                $query->where($db->quoteName('a.baths') . '=' . $baths);
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

            if (isset($sortOrder, $sortDirn) && $sortOrder && $sortDirn) {
                $query->order($db->quoteName('a.' . $sortOrder) . ' ' . $sortDirn);
            } else {
                if (!empty($params)  && $orderby = $params->get('order_by', '')) {
                    if ($orderby == 'featured') {
                        $query->where($db->quoteName('a.featured') . ' = 1');
                        $query->order($db->quoteName('a.ordering') . ' ASC');
                    } else {
                        $query->order($db->quoteName('a.ordering') . ' ' . $orderby);
                    }
                }
            }

            $query->where($db->quoteName('a.published') . ' = ' . $db->quote('1'));
        }

        $db->setQuery($query);
        $results = $db->loadObjectList();

        $params = ComponentHelper::getParams('com_spproperty');
        //SppropertyHelper::debug($results);
        $markers = array();

        $params = ComponentHelper::getParams('com_spproperty');
        $msr    = $params->get('show_unit') ? '/' . $params->get('measurement', 'sqft') : '';
        $curr   = $params->get('currency', 'USD:$');
        $currPos = $params->get('currency_position', 'left');
        $propertyMenuItemId = SppropertyHelper::getItemid('properties', array(
            array('params', 'like', '%"property_carousel":"default"%'),
            array('params', 'like', '%"catid":""%'),
            array('params', 'like', '%"agentid":""%'),
            array('params', 'like', '%"property_status":""%')
        ));

        foreach ($results as $key => $result) {
            $price  = $result->price_request == 'show' || empty($result->price_request) ? $result->price : '';
            $price  = SppropertyHelper::formatPrice($price);
            if (!empty($price)) {
                list($cName, $cSymbol) = explode(':', $curr);
                if ($currPos == 'left') {
                    $price = $cSymbol . $price;
                } else {
                    $price = $price . ' ' . $cSymbol;
                }
            }

            list($lat, $lng) = explode(',', $result->map);
            $lng = trim($lng);
            $object = new stdClass();
            $latlng = new stdClass();

            $latlng->lat = (float) $lat;
            $latlng->lng = (float) $lng;

            $object->latlng = $latlng;
            $object->title  = $result->title;
            $object->image  = SppropertyHelper::getThumbs($result->image, 'property_thumbnail', '360x207');
            $object->msr    = $params->get('measurement', 'sqft');
            $object->id     = $result->id;
            $object->price  = !empty($price) ? $price : '';
            $object->size   = $result->psize . ' ' . $object->msr;
            $result->price  = !empty($price) ? $price .  ($result->property_status == 'rent' ? (!empty($result->rent_period) ? ' / ' .$result->rent_period : ' / ' . 'Month' ) : $msr )   . ' | ' : '';
            $result->psize  = $object->size;
            $object->curr   = str_repeat(explode(':', $curr)[1], 3);
            $result->url    = Route::_('index.php?option=com_spproperty&view=property&id=' . $result->id . ':' . $result->alias . $propertyMenuItemId);
            $object->info   = $this->createInfoWindow($result);

            $markers[] = $object;
        }

        return json_encode($markers);
    }

    public function createInfoWindow($marker)
    {
        $html = array();
        $html[] = "<div class='infowindow'>";
        $html[] = "<div class='image-wrapper'>";
        $html[] = "<img src='" . SppropertyHelper::getThumbs($marker->image, 'property_thumbnail', '360x207') . "' >";
        $html[] = "</div>";
        $html[] = "<div class='info-wrapper'>";
        $html[] = "<div class='title-wrapper'>";
        $html[] = "<p>" . $marker->title . "</p>";
        $html[] = "<p>";
        $html[] = "<span class='price'>" . $marker->price . " </span>";
        $html[] = "<span class='property-size'>" . $marker->psize . "</span>";
        $html[] = "</p>";
        $html[] = "</div>";
        $html[] = "<div class='location-wrapper'>";
        $html[] = "<span class='fa fa-map-marker'></span> <span>" . $marker->address . "</span>";
        $html[] = "</div>";
        $html[] = "</div>";
        $html[] = "<a href='" . $marker->url . "' class='map-to-property'></a>";
        $html[] = "</div>";

        return implode("\n", $html);
    }
}
