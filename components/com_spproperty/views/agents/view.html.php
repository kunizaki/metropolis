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
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyViewAgents extends HtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null)
    {

        // Get model
        $model  = $this->getModel();
        $app    = Factory::getApplication();
        // get the active item
        $activeMenu     = $app->getMenu()->getActive();

        if ($activeMenu) {
            $this->items        = $this->get('Items');
            $this->pagination   = $this->get('Pagination');
            $this->input        = Factory::getApplication()->input;

            $params         = $activeMenu->getParams();
            $order_by       = $params->get('order_by', '');

            // get current menu id
            $Itemid         = $this->input->get('Itemid', 0, 'INT');

            // get component params
            $this->cParams  = ComponentHelper::getParams('com_spproperty');
            // get columns
            $this->columns  = $this->cParams->get('agents_columns', 3);
            // get property types

            foreach ($this->items as $this->item) {
                $this->item->url = Route::_('index.php?option=com_spproperty&view=agent&id=' . $this->item->id . ':' . $this->item->alias . SppropertyHelper::getItemid('agents'));
                $this->item->thumb = SppropertyHelper::getThumbs($this->item->image, 'agent_thumbnail', '90x90');
            }

            //Generate Item Meta
            if (count($this->items)) {
                $itemMeta = array();
                //$itemMeta['image'] = JURI::base() . $this->items[0]->image;
                SppropertyHelper::itemMeta($itemMeta);
            }
        }

        return parent::display($tpl = null);
    }
}
