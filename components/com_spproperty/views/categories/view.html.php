<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;


class SppropertyViewCategories extends HtmlView
{
    protected $items;
    protected $pagination;
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        foreach ($this->items as &$item) {
            $item->url = Route::_('index.php?option=com_spproperty&view=properties&catid=' . $item->id . ':' . $item->alias);
        }
        return parent::display($tpl);
    }
}
