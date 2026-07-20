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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyViewMaps extends HtmlView
{
    protected $cParams;
    protected $items;
    protected $model;
    protected $patination;
    protected $layout_type;

    public function display($tpl = null)
    {

        $this->items = $this->get('Items');
        $this->model = $this->getModel();
        $this->pagination = $this->get('pagination');

        $app = Factory::getApplication();
        $this->params = $app->getParams();
        $menus = Factory::getApplication()->getMenu();
        $menu = $menus->getActive();
        $mParams = $menu->getParams();
        $menuItemId = SppropertyHelper::getItemid('properties', array(
            array('params', 'like', '%"property_carousel":"default"%'),
            array('params', 'like', '%"catid":""%'),
            array('params', 'like', '%"agentid":""%'),
            array('params', 'like', '%"property_status":""%'),
        ));
        $layout = $this->params->get('layout_type', 'default');

        $this->input = $app->input;
        $Itemid = $this->input->get('Itemid', 0, 'INT');
        $markers = $this->model->getMarkers(null, $mParams);

        $this->cParams = ComponentHelper::getParams('com_spproperty');
        $doc = Factory::getDocument();


        //Add assets
        if (!file_exists(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css')) {
            $doc->addStyleSheet(Uri::root() . 'components/com_spproperty/assets/css/owl.carousel.min.css');
        }
        if (!file_exists(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js')) {
            $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/owl.carousel.min.js');
        }
        if (!file_exists(Uri::root(true) . '/components/com_spproperty/assets/css/owl.theme.default.min.css')) {
            $doc->addStylesheet(Uri::root(true) . '/components/com_spproperty/assets/css/owl.theme.default.min.css');
        }
        $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/spproperty.js');
        $doc->addScript(Uri::root() . 'components/com_spproperty/assets/js/spproperty-sorting.js');

        // get map
        $this->map_type = $this->cParams->get('map_selection', 'google');
        $this->zoomValue = $this->cParams->get('zoom_map', 5);
        if ($this->map_type == 'leaflet') {
            $doc->addStyleSheet(Uri::base(true) . '/administrator/components/com_spproperty/assets/css/leaflet.css');
            $doc->addStyleSheet(Uri::base(true) . '/components/com_spproperty/assets/css/MarkerCluster.css');
            $doc->addStyleSheet(Uri::base(true) . '/components/com_spproperty/assets/css/MarkerCluster.Default.css');
            $doc->addScript(Uri::base(true) . '/administrator/components/com_spproperty/assets/js/leaflet.js');
            $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/leaflet.markercluster.js');

            $this->mapbox_token = $this->cParams->get('mapbox_token');
            $this->map_view = $this->cParams->get('map_view', 'streets');
        } else {
            $gmap_api = $this->cParams->get('gmap_api','');

            $doc->addScript('//maps.google.com/maps/api/js?libraries=places,marker&loading=async&key=' . $gmap_api);
            $doc->addScript('https://unpkg.com/@googlemaps/markerclusterer@latest');
            $doc->addScript(Uri::root() . '/components/com_spproperty/assets/js/gmap_style.js');
        }

        // get columns
        $this->columns = $mParams->get('columns', 2);
        $this->map_height = $mParams->get('map_height', 400);

        $doc->addScript(Uri::root() . '/components/com_spproperty/assets/js/spmap.js');       
        //Declaring global variables
        $doc->addScriptDeclaration("
            var markers = " . $markers . ";
            var host    = '" . Uri::base() . 'index.php?option=com_spproperty' . "';
            var mapType = '" . $this->map_type . "';
            var zoomValue = '" . $this->zoomValue . "';
            var mapboxToken = '" . (isset($this->mapbox_token) ? $this->mapbox_token : '') . "';
            var mapView = '" . (isset($this->map_view) ? $this->map_view : '') . "';
            var baseUrl = '" . Uri::base() . "';
            var columns = '" . $this->columns . "';
            var listStyle = '" . $mParams->get('list_style', 'grid') . "';
        ");

        $this->layout_type = str_replace(':_', '', $layout);
        $this->setLayout($this->layout_type);

        return parent::display($tpl);
    }
}
