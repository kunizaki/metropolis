<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class SppropertyViewGallery extends HtmlView
{
    protected $item;

    public function display($tpl = null)
    {

        $model      = $this->getModel();
        $this->item = $this->get("Item");
        $doc = Factory::getDocument();

        $app                    = Factory::getApplication();
        $menu                   = $app->getMenu()->getActive();
        $mParams                = $menu->getParams();
        $this->columns          = $mParams->get('columns', 4);
        $gallery_style          = $mParams->get('gallery_style', 'masonry');
        $this->galleries_url    = Route::_('index.php?option=com_spproperty&view=galleries' . SppropertyHelper::getItemid('galleries'));

        $this->gallery = array();
        $gallery = $this->item->gallery;

        if ($gallery_style == 'masonry') {
            $this->gallery = $this->masonary($gallery, $this->columns);
        } elseif ($gallery_style == 'rectangle') {
            $this->gallery = $this->rectangle($gallery, $this->columns);
        }


        $doc->addStyleSheet(Uri::base(true) . '/components/com_spproperty/assets/css/jquery.magnific.popup.css');
        $doc->addScript(Uri::base(true) . '/components/com_spproperty/assets/js/jquery.magnific.popup.min.js');

        $doc->addScriptDeclaration("
            jQuery(function($) {
                $('.spproperty-gallery-item').magnificPopup({
                    magnificPopup: 'a',
                    type: 'image',
                    gallery: {enabled: true},
                    mainClass: 'mfp-with-zoom',
                    zoom: {
                        enabled: true,
                        duration: 300,
                        easing: 'ease-in-out',
                        opener: function(openerElement) {
                        return openerElement.is('img') ? openerElement : openerElement.find('img');
                        }
                    }


                });
            });
        ");

        return parent::display($tpl = null);
    }

    private function masonary($items, $columns, $contents = array())
    {
        $col         = round(count((array)$items) / $columns);

        //Generate thumb and tower image within ratio thumb:tower = 2:1
        $cThumb      = ceil(($col * 2) / 3);
        $cTower      = floor(($col * 1) / 3);
        $thumbChoice = array($cThumb, $cTower);

        $thumbs      = array(
            array('property_thumbnail','360x207'),
            array('property_thumbnail_tower','640x715')
        );

        $arr = array();

        if (!empty($items)) {
            foreach ($items as $data) {
                $obj           = new stdClass();
                $obj->image    = $data['photo'];
                $obj->alt_text = $data['alt_text'];
                $temp[]        = $obj;
            }

            $arr[] = $temp;
            //Predicting the the image size i.e. displaying image
            //is thumb or tower but keep the height of the album fixed.
            foreach ($arr as $value) {
                $tempTC             = $thumbChoice;
                foreach ($value as $item) {
                    $randThumb      = rand(0, 1);
                    $tempTh         = '';
                    if ($tempTC[$randThumb] > 0) {
                        $tempTh     = $thumbs[$randThumb];
                        $tempTC[$randThumb]--;
                        $item->thumb = SppropertyHelper::getThumbs($item->image, $tempTh[0], $tempTh[1]);
                    } else {
                        $index      = ($randThumb + 1) % 2;
                        if ($tempTC[$index] > 0) {
                            $tempTC[$index]--;
                        }
                        $tempTh     = $thumbs[$index];
                        $item->thumb = SppropertyHelper::getThumbs($item->image, $tempTh[0], $tempTh[1]);
                    }
                }
            }
            return $arr;
        }
    }

    private function rectangle($items, $columns, $contents = array())
    {
        $thumbs = array('property_thumbnail','360x207');
        $arr = array();

        foreach ($items as $data) {
            $obj           = new stdClass();
            $obj->image    = $data['photo'];
            $obj->alt_text = $data['alt_text'];
            $temp[]        = $obj;
        }

        $arr[] = $temp;

        foreach ($arr as $value) {
            foreach ($value as $item) {
                $item->thumb = SppropertyHelper::getThumbs($item->image, $thumbs[0], $thumbs[1]);
            }
        }
        return $arr;
    }
}
