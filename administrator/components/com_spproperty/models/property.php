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
use Joomla\CMS\Table\Table;
use Joomla\String\StringHelper;
use Joomla\CMS\MVC\Model\AdminModel;

class SppropertyModelProperty extends AdminModel
{
    protected $text_prefix = 'COM_SPPROPERTY';

    public function getTable($name = 'Property', $prefix = 'SppropertyTable', $config = array())
    {
        return Table::getInstance($name, $prefix, $config);
    }

    public function getForm($data = array(), $loadData = true)
    {
        $app = Factory::getApplication();
        $form = $this->loadForm('com_spproperty.property', 'property', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        return $form;
    }


    public function loadFormData()
    {
        $data = Factory::getApplication()
            ->getUserState('com_spproperty.edit.property.data', array());

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    private function generateNewTitleLocally($alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(array('alias' => $alias))) {
            $title = StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }
        return array($title, $alias);
    }

    protected function prepareTable($table)
    {
        /* define which columns can have NULL values */
        $defnull = array('psize', 'beds', 'baths', 'garages', 'building_year');
        foreach ($defnull as $val) {
            /* define the rules when the value is set NULL */
            if (!strlen($table->$val)) {
                $table->$val = null;
            }
        }
    }

    //Override save method for 'save as copy'
    public function save($data)
    {
        $input  = Factory::getApplication()->input;
        $task   = $input->get('task');

        if ($task == 'save2copy') {
            $originalTable = clone $this->getTable();
            $originalTable->load($input->getInt('id'));

            if ($data['title'] == $originalTable->title) {
                list($title, $alias) = $this->generateNewTitleLocally($data['alias'], $data['title']);
                $data['title'] = $title;
                $data['alias'] = $alias;
            } else {
                if ($data['alias'] == $originalTable->alias) {
                    $data['alias'] = '';
                }
            }

            if ($data['property_id'] == $originalTable->property_id) {
                $data['property_id'] = StringHelper::increment($data['property_id'], 'dash');
            }
            $data['published'] = 0;
        }

        if (!isset($data['features'])) {
            $data['features'] = [];
        }

        $data['price'] = SppropertyHelper::getFormattedPrice($data['price']);

        if (parent::save($data)) {
            return true;
        }
        return false;
    }

    protected function canDelete($record)
    {
        if (!empty($record->id)) {
            if ($record->published != -2) {
                return ;
            }

            $user = Factory::getUser();

            return parent::canDelete($record);
        }
    }


    protected function canEditState($record)
    {
        return parent::canEditState($record);
    }

    /**
     * Handle old data and convert to new
     * @param data the old data
     * @param keys key exists on the data
     * @param prefix index name prefix
    */
    public function old2new($data, $keys = array(), $prefix = '')
    {
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


    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            //Handle gallery data
            $item->gallery = (!empty($item->gallery)) ? json_decode($item->gallery, true) : '';
            $gallery = $this->old2new((array)$item->gallery, ['photo', 'alt_text'], 'gallery');

            if (!is_bool($gallery = $this->old2new((array)$item->gallery, ['photo', 'alt_text'], 'gallery'))) {
                $item->gallery = $gallery;
            }

            if (is_array($item->gallery)) {
                $item->gallery = json_encode($item->gallery);
            }

            //Handle floor_plans data
            $item->floor_plans = (!empty($item->floor_plans)) ? json_decode($item->floor_plans, true) : '';
            if (!is_bool($floor_plans = $this->old2new((array)$item->floor_plans, ['img', 'layout_name', 'text'], 'floor_plans'))) {
                $item->floor_plans = $floor_plans;
            }

            $item->features = (!empty($item->features)) ? json_decode($item->features, true) : '';

            if (isset($item->map)) {
                list($latitude, $longitude) = explode(',', $item->map);
                $item->latitude     = $latitude;
                $item->longitude    = $longitude;
            }

            $item->price = SppropertyHelper::getPriceBasedOnLocales($item->price);
            return $item;
        }
        return parent::getItem($pk);
    }
}
