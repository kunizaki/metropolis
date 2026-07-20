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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;

class SppropertyViewMyproperties extends HtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null)
    {

        $app                = Factory::getApplication();
        $user               = Factory::getUser();
        if ($user->guest) {
            $login_url = Route::_('index.php?option=com_users&view=login&return=' . base64_encode(Route::_('index.php?option=com_spproperty&view=myproperties')));
            $app->redirect($login_url);
            return false;
        }

        $model              = $this->getModel();
        $this->items        = $this->get("Items");
        $this->pagination   = $this->get('Pagination');
        $doc                = Factory::getDocument();


        $this->input = $app->input;
        // get the active item
        $menu           = $app->getMenu()->getActive();
        $params         = $menu->getParams();

        $menuItemId = SppropertyHelper::getItemid('properties', array(
            array('params', 'like', '%"property_carousel":"default"%')
        ));

        //Add assets
        $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/spproperty.js');
        $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/spproperty-sorting.js');

        // get current menu id
        $Itemid     = $this->input->get('Itemid', 0, 'INT');
        $sort_catid = $this->input->get('catid', 0, 'INT');

        if ($sort_catid) {
            $cat_info   = $model->getCatInfo($sort_catid);
            if ($menu) {
                if ($params->get('page_title_alt', '')) {
                    $page_title = $params->get('page_title_alt', '') . ' - ' . $cat_info->title;
                } elseif ($params->get('page_title')) {
                    $page_title = $params->get('page_title', '') . ' - ' . $cat_info->title;
                } elseif ($menu->title) {
                    $page_title = $menu->title . ' - ' . $cat_info->title;
                } else {
                    $page_title = $menu->title . ' - ' . $cat_info->title;
                }
            }
            SppropertyHelper::itemMeta(array( 'title' => $page_title));
        }


        // get component params
        jimport('joomla.application.component.helper');
        $this->cParams  = ComponentHelper::getParams('com_spproperty');
        // get columns
        $this->columns  = $this->cParams->get('properties_columns', 2);
        // get property types


        foreach ($this->items as &$item) {
            $item->property_status_txt = '';
            if ($item->property_status == 'rent') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
            } elseif ($item->property_status == 'sell') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
            } elseif ($item->property_status == 'in_hold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
            } elseif ($item->property_status == 'pending') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
            } elseif ($item->property_status == 'sold') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
            } elseif ($item->property_status == 'under_offer') {
                $item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
            }
            $item->url = Route::_('index.php?option=com_spproperty&view=property&id=' . $item->id . ':' . $item->alias . $menuItemId);
            $item->solid_price = $item->price;
            $item->price = SppropertyHelper::generateCurrency($item->price);
            $item->thumb = SppropertyHelper::getThumbs($item->image, 'property_thumbnail', '360x207');
        }
        return parent::display($tpl = null);
    }
}
