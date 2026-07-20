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
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldSpagent extends ListField
{
    protected $type   = 'spagent';
    protected $layout = 'joomla.form.field.list-fancy-select';

    protected function getOptions()
    {
        // Get Tournaments
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all records from the user profile table where key begins with "custom.".
        $query->select($db->quoteName(array('id', 'title', 'alias' )));
        $query->from($db->quoteName('#__spproperty_agents'));
        $query->where($db->quoteName('published') . " = 1");
        $query->order('ordering DESC');

        $db->setQuery($query);
        $results = $db->loadObjectList();
        $agents = $results;

        $options = array('' => Text::_('COM_SPPROPERTY_ALL'));

        foreach ($agents as $agent) {
            $options[] = HTMLHelper::_('select.option', $agent->id, $agent->title);
        }
        
        return array_merge(parent::getOptions(), $options);

    }
}
