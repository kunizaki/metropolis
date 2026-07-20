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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

class JFormFieldSpmap extends FormField
{
    protected $type = 'Spmap';

    protected function getInput()
    {
        jimport('joomla.application.component.helper');
        HTMLHelper::_('jquery.framework');
        $this->cParams = ComponentHelper::getParams('com_spproperty');
        $mapType = $this->cParams->get('map_selection', 'google');

        if ($mapType == 'google') {
            $return = $this->gmap();
        } else {
            $return = $this->osm();
        }

        return $return;
    }

    private function osm()
    {
        $doc = Factory::getDocument();
        $doc->addStyleSheet(Uri::base(true) . '/components/com_spproperty/assets/css/leaflet.css');
        $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/sposm.js');
        $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/leaflet.js');
        $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/leaflet-geosearch.js');

        $token = $this->cParams->get('mapbox_token');
        $view  = $this->cParams->get('map_view', 'streets');

        $doc->addScriptDeclaration('var token = "' . $token . '";');
        $doc->addScriptDeclaration('var view  = "' . $view . '";');

        $doc->addStyleDeclaration("
            #geomap{
                height: 500px;
                margin-top: 10px;
                z-index: 98;
            }

            .leaflet-top{
                bottom: 0;
                z-index: 1!important;
            }

            ul.show-location-list{
                z-index: 99;
                background: white;
                width: 400px;
                height: 150px;
                overflow-y: auto;
                position: absolute;
                top: 5%;
                left: 0;
                margin: 0;
                display: none;
                box-shadow: 1px 1px 4px #777;
                
            }

            ul.show-location-list li {
                padding: 5px;
                border-bottom: 1px solid #eee;
                cursor: pointer;
            }
           

            .sp-leaflet-wrapper{
                position:relative;
            }

        ");

        if (empty($this->value)) {
            $this->value = '40.7324319, -73.82480799999996';
        }
        $map = explode(',', $this->value);

        $html = array();
        $html[] = "<div class='sp-leaflet-wrapper'>";
        $html[] = "<input type='text' id='geo-search' class='inputbox' autocomplete='off' data-lat='" . trim($map[0]) . "' data-lon='" . trim($map[1]) . "' />";
        $html[] = "<br><a href='https://support.google.com/maps/answer/18539?co=GENIE.Platform%3DDesktop&hl=en' target='_blank'>" . Text::_('COM_SPPROPERTY_FIND_LATLNG') . "</a>";
        $html[] = "<ul class='show-location-list'></ul>";
        $html[] = "<input type='hidden' id='geo-location' class='" . $this->class . "' name='" . $this->name . "' value='" .  $this->value .  "'/>";
        $html[] = "<div id='geomap'></div>";
        $html[] = "</div>";
        return implode("\n", $html);
    }

    private function gmap()
    {
        $required  = $this->required ? ' required aria-required="true"' : '';
        $gmap_api = $this->cParams->get('gmap_api');
        $doc = Factory::getDocument();

        if (empty($this->value)) {
            $this->value = '40.7324319,-73.82480799999996';
        }

        // Load Map js
        if (!empty($gmap_api)) {

            $doc->addScript('//maps.google.com/maps/api/js?libraries=places,marker&loading=async&key=' . $gmap_api . '');
            $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/locationpicker.jquery.js');
            $map = explode(',', $this->value);
            $doc->addStyleDeclaration('
                .spproperty-gmap-canvas {
                    height: 300px;
                    margin-top: 10px;
                }
                .pac-container {
                    z-index: 99999;
                }
            ');
            $html = [];
            $html[] = "<input class='addon-input gmap-latlng' type='hidden' name='" . $this->name . "' id='" . $this->id . "' value='" . $this->value . "' />";
            $html[] = "<input class='form-control spproperty-gmap-address' type='text' autocomplete='off' " . $required . " data-latitude='" . trim($map[0]) . "' data-longitude='" . trim($map[1]) . "' />" ;
            $html[] = "<br><a href='https://support.google.com/maps/answer/18539?co=GENIE.Platform%3DDesktop&hl=en' target='_blank'>" . Text::_('COM_SPPROPERTY_FIND_LATLNG') . "</a>";
        } else {
            $doc->addScript('http://maps.google.com/maps/api/js?libraries=places,marker&loading=async&key=');

            $html = [];
            $html[] = "<input class='form-control ' type='text' name='" . $this->name . "' value='" . $this->value . "' autocomplete='off' " . $required . " />";
            $html[] = "<br><a href='https://support.google.com/maps/answer/18539?co=GENIE.Platform%3DDesktop&hl=en' target='_blank'>" . Text::_('COM_SPPROPERTY_FIND_LATLNG') . "</a>";
        }
        return implode("\n", $html);
    }
}
