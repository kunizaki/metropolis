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
class SppropertyModelGallery extends ItemModel
{
    protected $_context = 'com_spproperty.gallery';

    protected function populateState()
    {
        $app = Factory::getApplication('site');
        $itemId = $app->input->getInt('id');
        $this->setState('gallery.id', $itemId);
        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    public function getItem($itemId = null)
    {
        $user = Factory::getUser();

        $itemId = (!empty($itemId)) ? $itemId : (int)$this->getState('gallery.id');

        if ($this->_item == null) {
            $this->_item = array();
        }

        if (!isset($this->_item[$itemId])) {
            try {
                $db = $this->getDbo();
                $query = $db->getQuery(true);
                $query->select('a.*');
                $query->from($db->quoteName('#__spproperty_properties', 'a'));
                $query->select($db->quoteName('b.title', 'category_name'));
                $query->join('LEFT', $db->quoteName('#__spproperty_categories', 'b') . ' ON (' . $db->quoteName('a.category_id') . ' = ' . $db->quoteName('b.id') . ')');
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
        if (!is_bool($gallery = SppropertyHelper::old2new($this->_item[$itemId]->gallery, ['photo', 'alt_text'], 'gallery'))) {
            $this->_item[$itemId]->gallery = $gallery;
        }
        $this->_item[$itemId]->floor_plans = json_decode($this->_item[$itemId]->floor_plans, true);
        if (!is_bool($floor_plans = SppropertyHelper::old2new($this->_item[$itemId]->floor_plans, ['img', 'layout_name', 'text'], 'floor_plans'))) {
            $this->_item[$itemId]->floor_plans = $floor_plans;
        }

        return $this->_item[$itemId];
    }
}
