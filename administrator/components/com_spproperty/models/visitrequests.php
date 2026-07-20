
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

class SppropertyModelVisitrequests extends ListModel
{
    public function __construct(array $config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id','a.id',
                'customer_name', 'a.customer_name',
                'customer_email', 'a.customer_email',
                'customer_phone', 'a.customer_phone',
                'property_id', 'a.property_id',
                'created_by', 'a.created_by',
                'created', 'a.created',
                'visitor_ip', 'a.visitor_ip',
                'type', 'a.type'

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
        $state = $this->get('State');
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('a.*, p.title as property_title, u.username as name');
        $query->from($db->quoteName('#__spproperty_visitrequests', 'a'));

        $query->join('LEFT', $db->quoteName('#__spproperty_properties', 'p') . " ON (" . $db->quoteName('a.property_id') . " = " . $db->quoteName('p.id') . " )");
        $query->join('LEFT', $db->quoteName('#__users', 'u') . " ON (" . $db->quoteName('a.created_by') . " = " . $db->quoteName('u.id') . " )");

        if ($status = $this->getState('filter.published')) {
            if ($status != '*') {
                $query->where($db->quoteName('a.published') . " = " . $status);
            }
        } else {
            $query->where($db->quoteName('a.published') . " IN (0,1)");
        }

        if ($property = $this->getState('filter.property')) {
            $query->where($db->quoteName('a.property_id') . ' = ' . $db->quote($property));
        }

        if ($request = $this->getState('filter.type')) {
            $query->where($db->quoteName('a.type') . ' = ' . $db->quote($request));
        }


        $orderCol = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'desc');

        $order = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        $query->order($order);
        return $query;
    }
}
