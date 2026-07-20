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
use Joomla\CMS\Component\ComponentHelper;

class SppropertyModelAgents extends ListModel
{
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication('site');
        $params = $app->getParams();
        $this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
        $this->setState('filter.language', Multilanguage::isEnabled());
        $limit = $params->get('agents_limit', 6);
        $this->setState('list.limit', $limit);
    }

    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_agents', 'a'));

        $isSite = Factory::getApplication()->isClient('site');


        //Frontend
        if ($isSite) {
            // Get menu Params
            $app = Factory::getApplication();
            $active = $app->getMenu()->getActive();// get the active item
            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $cParams = ComponentHelper::getParams('com_spproperty');

            $selection_type = $params->get('agent_selection_type', '');
            if ($selection_type) {
                if ($selection_type == 1) {
                    $order = self::getAgentsWithHigherPropertyOrder();
                    $order_arr = array();

                    foreach ($order as $v) {
                        $order_arr[] = $v->agent_id;
                    }
                    if (!empty($order_arr)) {
                        $query->where($db->quoteName('a.id') . ' IN (' . implode(',', $order_arr) . ')');
                        array_unshift($order_arr, 'a.id');
                        $query->order('field(' . implode(',', $order_arr) . ')');
                    }
                } elseif ($selection_type == 2) {
                    $aid = $params->get('selected_agents', array());
                    if (!empty($aid)) {
                        $aid = implode(',', $aid);
                        $query->where($db->quoteName('a.id') . ' IN (' . $aid . ')');
                    }

                    if ($ordering = $params->get('order_by', 'asc')) {
                        $query->order($db->quoteName('a.ordering') . ' ' . $ordering);
                    }
                } elseif ($selection_type == 3) {
                    $query->where($db->quoteName('featured') . ' = 1');
                    if ($ordering = $params->get('order_by', 'asc')) {
                        $query->order($db->quoteName('a.ordering') . ' ' . $ordering);
                    }
                }
            }

            $query->where($db->qn('a.published') . " = " . $db->quote('1'));
            if ($ordering = $params->get('order_by', 'asc')) {
                $query->order($db->quoteName('a.ordering') . ' ' . $ordering);
            }

            $agents_limit = $cParams->get('agents_limit', 6);
            $this->setState('list.limit', $agents_limit);

            if ($this->getState('filter.language')) {
                $query->where($db->quoteName('a.language') . ' IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            }
        }
        return $query;
    }

    public static function getAllAgents($params = null)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.*')
            ->from($db->quoteName('#__spproperty_agents', 'a'));

        if (!empty($params)) {
            $selection_type = $params->get('agent_selection_type', 1);
            if (!empty($selection_type)) {
                if ($selection_type == 1) {
                    $order = self::getAgentsWithHigherPropertyOrder();
                    $order_arr = array();

                    foreach ($order as $v) {
                        $order_arr[] = $v->agent_id;
                    }
                    if (!empty($order_arr)) {
                        $query->where($db->quoteName('a.id') . ' IN (' . implode(',', $order_arr) . ')');
                        array_unshift($order_arr, 'a.id');
                        $query->order('field(' . implode(',', $order_arr) . ')');
                    }
                } elseif ($selection_type == 2) {
                    $aid = $params->get('selected_agents', array());
                    if (!empty($aid)) {
                        $aid = implode(',', $aid);
                        $query->where($db->quoteName('a.id') . ' IN (' . $aid . ')');
                    }

                    if ($ordering = $params->get('ordering', 'asc')) {
                        $query->order($db->quoteName('a.ordering') . ' ' . $ordering);
                    }
                } else {
                    $query->where($db->quoteName('featured') . ' = 1');
                    if ($ordering = $params->get('ordering', 'asc')) {
                        $query->order($db->quoteName('a.ordering') . ' ' . $ordering);
                    }
                }
            }

            $query->where($db->quoteName('a.published') . ' = 1');

            if ($limit = $params->get('limit', 3)) {
                $query->setLimit($limit);
            }
        }

        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);
        return $db->loadObjectList();
    }

    public static function getAgentsWithHigherPropertyOrder()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.agent_id, COUNT(a.agent_id) as total_properties')->from($db->quoteName('#__spproperty_properties', 'a'))
            ->where($db->quoteName('a.published') . ' = 1')
            ->where($db->quoteName('a.agent_id') . ' IS NOT NULL ');
        $query->group($db->quoteName('a.agent_id'));
        $query->order($db->quoteName('total_properties') . ' DESC');

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    //if item not found
    public function &getItem($id = null)
    {
        $item = parent::getItem($id);
        $isSite = Factory::getApplication()->isSite();
        if ($isSite) {
            if ($item->id) {
                return $item;
            } else {
                throw new Exception(Text::_('COM_SPPROPERTY_NO_ITEMS_FOUND'), 404);
            }
        } else {
            return $item;
        }
    }

    // Get agent properties
    public static function getAgntProperties($agentid = '', $limit = '')
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->select($db->quoteName('b.title', 'category_name'));
        $query->from($db->quoteName('#__spproperty_properties', 'a'));
        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');
        //Language
        $query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.agent_id') . '=' . $agentid);
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

    // ajax booking
    public function insertBooking($pid = '', $name = '', $phone = '', $email = '', $message = '', $user_id = 0)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $columns = array('property_id', 'customer_name', 'customer_email', 'customer_phone', 'customer_comments', 'created_by', 'created', 'published');
        $values = array($db->quote($pid), $db->quote($name), $db->quote($email), $db->quote($phone), $db->quote($message), $db->quote($user_id), $db->quote(Factory::getDate()), 1);
        $query
            ->insert($db->quoteName('#__spproperty_bookings'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();

        return $db->insertid();
    }
}
