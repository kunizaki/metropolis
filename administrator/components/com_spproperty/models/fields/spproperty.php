<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class JFormFieldSpproperty extends FormField
{
    protected $type = 'Spproperty';
    protected $static;
    protected $repeatable;

    public function __get($name)
    {
        switch ($name) {
            case 'static':
                if (empty($this->static)) {
                    $this->static = $this->getStatic();
                }

                return $this->static;
                break;

            case 'repeatable':
                if (empty($this->repeatable)) {
                    $this->repeatable = $this->getRepeatable();
                }

                return $this->repeatable;
                break;

            default:
                return parent::__get($name);
        }
    }


    public function getStatic()
    {
        return $this->getInput();
    }


    public function getRepeatable()
    {
        return $this->getInput();
    }

    public function getInput()
    {

        $property_id = $this->value;

        if ($property_id) {
            //$property_id = implode(',', json_decode($property_ids));
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select(array('a.*'));
            $query->from($db->quoteName('#__spproperty_properties', 'a'));
            //$query->where($db->quoteName('a.spproperty_property_id')." IN (" . $property_ids . ")");
            $query->where($db->quoteName('a.spproperty_property_id') . " = " . $property_id);
            $db->setQuery($query);
            $property = $db->loadObject();

            $output = '';

            if ($property) {
                $output .= '<a href="index.php?option=com_spproperty&view=property&id=' . $property->spproperty_property_id . '">' . $property->title . '</a>';
            }

            return $output;
        }

        return '....';
    }
}
