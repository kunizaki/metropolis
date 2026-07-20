<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldAuthorlist extends FormField
{

    protected $type = 'authorlist';

    protected function getInput(){
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id', 'name' )));
        $query->from($db->quoteName('#__users'));
        $query->where($db->quoteName('block')." = ".$db->quote('0'));
        $query->order('registerDate DESC');

        $db->setQuery($query);  
        $results = $db->loadObjectList();
        $author_list = $results;


        foreach($author_list as $author){
            $options[] = HTMLHelper::_( 'select.option', $author->id, $author->name );
        }
        
        return HTMLHelper::_('select.genericlist', $options, $this->name, ['class' => 'form-select'], 'value', 'text', $this->value);
    }
}
