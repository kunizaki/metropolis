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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SppropertyViewAgent extends HtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null)
    {
        $model      = $this->getModel();
        $doc        = Factory::getDocument();
        $config     = Factory::getApplication()->getConfig();
        $adminMail  = $config->get('mailfrom', '');
        $this->item = $this->get("Item");

        // get component params 
        $this->cParams  = ComponentHelper::getParams('com_spproperty');
        $this->contact_tac      = $this->cParams->get('contact_tac', 1);
        $this->contact_tac_text = $this->cParams->get('contact_tac_text');
        // get columns
        $this->properties_columns  = $this->cParams->get('properties_columns', 2);
        //Show Captcha
        $this->captcha       = $this->cParams->get('contact_captcha', false);
        $joomlaVersion       = explode('.', JVERSION);
        $this->captchaPlugin = $joomlaVersion[0] <= 4 ? 'recaptcha' : 'recaptcha_invisible';
        $this->item->thumb   = SppropertyHelper::getThumbs($this->item->image, 'agent_thumbnail', '90x90');

        // get map
        $this->map_type = $this->cParams->get('map_selection', 'google');

        if ($this->map_type == 'leaflet') {
            $doc->addStyleSheet(Uri::base(true) . '/administrator/components/com_spproperty/assets/css/leaflet.css');
            $doc->addScript(Uri::base(true) . '/administrator/components/com_spproperty/assets/js/leaflet.js');
            $doc->addScript(Uri::base(true) . '/administrator/components/com_spproperty/assets/js/spleaflet.js');

            $this->mapbox_token = $this->cParams->get('mapbox_token');
            $this->map_view     = $this->cParams->get('map_view', 'streets');
        } else {
            $gmap_api = $this->cParams->get('gmap_api','');
            
            $doc->addScript('//maps.google.com/maps/api/js?libraries=places,marker&loading=async&key=' . $gmap_api . '');
            $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/gmap_mutiple.js');
        }

        $doc->addScript(Uri::root(true) . '/components/com_spproperty/assets/js/spproperty.js');
        $doc->addScript(Uri::root(true) . '/components/com_spproperty/assets/js/gmap_style.js');

        $this->item->url        = Route::_('index.php?option=com_spproperty&view=agent&id=' . $this->item->id . ':' . $this->item->alias . SppropertyHelper::getItemid('agents'));
        $this->agent_properties = $model->getAgntProperties($this->item->id);
        $this->item->email      = empty($this->item->email) ? $adminMail : $this->item->email;


        foreach ($this->agent_properties as $this->agent_property) {
            $this->agent_property->property_status_txt = '';
            if ($this->agent_property->property_status == 'rent') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
            } elseif ($this->agent_property->property_status == 'sell') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
            } elseif ($this->agent_property->property_status == 'in_hold') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
            } elseif ($this->agent_property->property_status == 'pending') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
            } elseif ($this->agent_property->property_status == 'sold') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
            } elseif ($this->agent_property->property_status == 'under_offer') {
                $this->agent_property->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
            }
            $this->agent_property->solid_price = $this->agent_property->price;
            $this->agent_property->price = SppropertyHelper::generateCurrency($this->agent_property->price);
            $this->agent_property->thumb = SppropertyHelper::getThumbs($this->agent_property->image, 'property_thumbnail', '360x207');
        }

        //Get Currency
        $this->currency = explode(':', $this->cParams->get('currency', 'USD:$'));
        $this->currency = $this->currency[1];

        $this->plocations = array();
        $maps_model       = BaseDatabaseModel::getInstance('maps', 'SppropertyModel');
        foreach ($this->agent_properties as $key => &$this->agent_property) {
            $this->plocations[$key] ['title']       = (htmlspecialchars_decode($this->createInfoWindow($this->agent_property), ENT_QUOTES) );
            $this->plocations[$key] ['location']    = $this->agent_property->map;
        }

        return parent::display($tpl = null);
    }

    public function createInfoWindow($marker)
    {
        $params = ComponentHelper::getParams('com_spproperty');
        $ps_msr = $params->get('measurement', 'sqft');
        $msr    = $params->get('show_unit') ? '/' . $params->get('measurement', 'sqft') : '';
        $html   = array();
        $html[] = '<div class="infowindow">';
        $html[] = '<div class="image-wrapper">';
        $html[] = '<img src="' . SppropertyHelper::getThumbs($marker->image, '', '360x207') . '" >';
        $html[] = '</div>';
        $html[] = '<div class="info-wrapper">';
        $html[] = '<div class="title-wrapper">';
        $html[] = '<p>' . $marker->title . '</p>';
        $html[] = '<p>';
        $html[] = '<span class="price">' . $marker->price . ' ' . $msr . '</span>';
        $html[] = '<span class="property-size"> | ' . $marker->psize . ' ' . $ps_msr . '</span>';
        $html[] = '</p>';
        $html[] = '</div>';
        $html[] = '<div class="location-wrapper">';
        $html[] = '<span class="fa fa-map-marker"></span> <span>' . $marker->address . '</span>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '<a href="' . $marker->url . '" class="map-to-property"></a>';
        $html[] = '</div>';

        return implode('', $html);
    }
}
