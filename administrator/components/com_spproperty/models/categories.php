
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

class SppropertyModelCategories extends ListModel
{
    public function __construct(array $config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id','a.id',
                'title', 'a.title',
                'created', 'a.created',
                'language', 'a.language'
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

        $query->select('a.*, l.title_native as lang');
        $query->from($db->quoteName('#__spproperty_categories', 'a'));

        $query->join('LEFT', $db->quoteName('#__languages', 'l') . " ON (" . $db->quoteName('a.language') . " = " . $db->quoteName('l.lang_code') . " )");

        // search
        if ($search = $this->getState('filter.search')) {
            $keywords = explode(" ", trim($search));
            $query->where($db->quoteName('a.title') . " REGEXP " . $db->quote(implode("|", $keywords)));
        }

        $status = $this->getState('filter.published');
        if (is_numeric($status)) {
            $query->where($db->quoteName('a.published') . " = " . $status);
        } else {
            $query->where($db->quoteName('a.published') . " IN (0,1)");
        }

        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . " = " . $db->quote($language));
        }

        $orderCol = $this->getState('list.ordering', 'a.ordering');
        $orderDirn = $this->getState('list.direction', 'desc');

        $order = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        $query->order($order);

        return $query;
    }
}
