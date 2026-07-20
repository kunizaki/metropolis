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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldSpcats extends FormField
{
    protected $type = 'spcats';

    protected function getInput()
    {

        // Get Tournaments
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all records from the user profile table where key begins with "custom.".
        $query->select($db->quoteName(array('id', 'title', 'alias' )));
        $query->from($db->quoteName('#__spproperty_categories'));
        $query->where($db->quoteName('published') . " = 1");
        $query->order('ordering DESC');

        $db->setQuery($query);
        $results = $db->loadObjectList();
        $cats = $results;

        $options = array('' => Text::_('COM_SPPROPERTY_ALL'));

        foreach ($cats as $cat) {
            $options[] = HTMLHelper::_('select.option', $cat->id, $cat->title);
        }

        return HTMLHelper::_('select.genericlist', $options, $this->name, 'class="form-select"', 'value', 'text', $this->value);
    }
}
