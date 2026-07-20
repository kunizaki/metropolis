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
class SppropertyModelProperties extends ListModel
{
    public function __construct(array $config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id','a.id',
                'title','a.title',
                'category_id', 'a.category_id',
                'property_status', 'a.property_status',
                'city', 'a.city',
                'address', 'a.address',
                'psize', 'a.psize',
                'price', 'a.price',
                'published','a.published',
                'created','a.created',
                'ordering','a.ordering',
                'language','a.language',

            ];
        }
        parent::__construct($config);
    }

    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        $app = Factory::getApplication();
        $context = $this->context;

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    protected function getListQuery()
    {
        $app = Factory::getApplication();
        $user = Factory::getUser();
        $state = $this->get('State');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*, c.title as category, l.title_native as lang');

        $query->from($db->quoteName('#__spproperty_properties', 'a'));

        $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'c') . " ON (" . $db->quoteName('a.category_id') . " = " . $db->quoteName('c.id') . " )");
        $query->join('LEFT', $db->quoteName('#__languages', 'l') . " ON (" . $db->quoteName('a.language') . " = " . $db->quoteName('l.lang_code') . " )");

        if (!$user->authorise('core.admin')) {
            $userAgentId = SppropertyHelper::userAgentId($user->get('id'));
            if (!empty($userAgentId)) {
                $query->where($db->quoteName('a.agent_id') . ' = ' . $db->quote($userAgentId));
            }
        }

        if ($search = $this->getState('filter.search')) {
            if (!empty($search)) {
                if (stripos($search, 'id:') === 0) {
                    $query->where('a.id = ' . (int) substr($search, 3));
                } elseif (stripos($search, 'propertyid:') === 0) {
                    $search = $db->quote($db->escape(substr($search, 12), true));
                    $query->where('(a.property_id = ' . $search . ')');
                } else {
                    $keywords = explode(" ", trim($search));
                    $query->where('(' . $db->quoteName('a.title') . " REGEXP " . $db->quote(implode("|", $keywords)) . ' OR ' . $db->quoteName('a.description') . ' REGEXP ' . $db->quote(implode('|', $keywords)) . ' OR ' . $db->quoteName('a.property_id') . ' = ' . $db->quote(trim($search)) . ')');
                }
            }
        }

        $status = $this->getState('filter.published');

        if (is_numeric($status)) {
            $query->where($db->quoteName('a.published') . " = " . $status);
        } else {
            $query->where($db->quoteName('a.published') . " IN (0,1)");
        }

        if ($category = $this->getState('filter.category')) {
            $query->where($db->quoteName('a.category_id') . " = " . $db->quote($category));
        }

        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . " = " . $db->quote($language));
        }

        if ($property_status = $this->getState('filter.property_status')) {
            $query->where($db->quoteName('property_status') . " = " . $db->quote($property_status));
        }


        $orderCol = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'desc');

        $order = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        $query->order($order);

        return $query;
    }
}
