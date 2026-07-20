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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyViewProperty extends HtmlView
{
    protected $item;

    public function display($tpl = null)
    {

        $model      = $this->getModel();
        $this->item = $this->get("Item");
        $doc        = Factory::getDocument();

        // get component params
        $this->cParams          = ComponentHelper::getParams('com_spproperty');
        $this->recipient        = $this->cParams->get('recipient');
        $this->contact_tac      = $this->cParams->get('contact_tac', 1);
        $this->contact_tac_text = $this->cParams->get('contact_tac_text');

        $app            = Factory::getApplication();
        $menu           = $app->getMenu()->getActive();
        $mParams        = $menu->getParams();
        $this->carousel = $mParams->get('property_carousel', 'default');
        $this->catid    = $mParams->get('catid', '');
        $agentid        = $mParams->get('agentid', '');
        $pstatus        = $mParams->get('property_status', '');

        $menuItemId = SppropertyHelper::getItemid('properties', array(
            array('params', 'like', '%"property_carousel":"' . $this->carousel . '"%'),
            array('params', 'like', '%"catid":"' . $this->catid . '"%'),
            array('params', 'like', '%"agentid":"' . $agentid . '"%'),
            array('params', 'like', '%"property_status":"' . $pstatus . '"%')
        ));

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
   
            $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/gmap.js');
            $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/gmap_style.js');
        }

        //Show Captcha or not
        $this->captcha       = $this->cParams->get('contact_captcha', false);
        $joomlaVersion       = explode('.', JVERSION);
        $this->captchaPlugin = $joomlaVersion[0] <= 4 ? 'recaptcha' : 'recaptcha_invisible';

        $doc->addScript(Uri::root(true) . '/components/com_spproperty/assets/js/spproperty.js');

        if (!file_exists(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css')) {
            $doc->addStyleSheet(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css');
        }
        if (!file_exists(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js')) {
            $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js');
        }
        if (!file_exists(Uri::root() . 'components/com_spproperty/assets/css/owl.theme.default.min.css')) {
            $doc->addStylesheet(Uri::root() . 'components/com_spproperty/assets/css/owl.theme.default.min.css');
        }

        //this item url
        $this->item->url      = Route::_('index.php?option=com_spproperty&view=property&id=' . $this->item->id . ':' . $this->item->alias . $menuItemId);

        // get category info
        $this->item->cat_info = $model->getCatInfo($this->item->category_id);

        //get visitor IP
        $this->visitorip      = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];

        // Map
        $this->map = explode(',', $this->item->map);

        //features
        $this->featureinfos = array();
        if (!empty($this->item->features)) {
            foreach ($this->item->features as $key => $feature) {
                $this->featureinfos [$key] = $model->getPfeatures($feature);
            }
        }

        //agent info
        if ($this->item->agent_id) {
            $this->item->agent = $model->getAgntInfo($this->item->agent_id);
            if ($this->item->agent) {
                $this->item->agent->url = Route::_('index.php?option=com_spproperty&view=agent&id=' . $this->item->agent->id . ':' . $this->item->agent->alias . SppropertyHelper::getItemid('agents'));
                $this->item->agent->thumb = SppropertyHelper::getThumbs($this->item->agent->image, 'agent_thumbnail', '90x90');
            }
        }

        //Get Currency
        $this->item->solid_price = $this->item->price;
        $this->item->price = SppropertyHelper::generateCurrency($this->item->price, $this->item->currency, $this->item->currency_position, $this->item->currency_format);

        if (filter_var($this->item->video, FILTER_VALIDATE_URL)) {
            //video parse
            $video = parse_url($this->item->video);

            switch ($video['host']) {
                case 'youtu.be':
                    $id = trim($video['path'], '/');
                    $this->videosrc = '//www.youtube.com/embed/' . $id;
                    break;

                case 'www.youtube.com':
                case 'youtube.com':
                    parse_str($video['query'], $query);
                    $id = $query['v'];
                    $this->videosrc = '//www.youtube.com/embed/' . $id;
                    break;

                case 'vimeo.com':
                case 'www.vimeo.com':
                    $id = trim($video['path'], '/');
                    $this->videosrc = "//player.vimeo.com/video/{$id}";
            }
        }

        //Generate Item Meta
        $itemMeta               = array();
        $itemMeta['title']      = $this->item->title;
        $cleanText              = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        $itemMeta['image']      = Uri::base() . $this->item->image;
        SppropertyHelper::itemMeta($itemMeta);

        //Property status text
        $this->item->property_status_txt = '';
        if ($this->item->property_status == 'rent') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
        } elseif ($this->item->property_status == 'sell') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
        } elseif ($this->item->property_status == 'in_hold') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
        } elseif ($this->item->property_status == 'pending') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
        } elseif ($this->item->property_status == 'sold') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
        } elseif ($this->item->property_status == 'under_offer') {
            $this->item->property_status_txt = Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
        }

        return parent::display($tpl = null);
    }
}
