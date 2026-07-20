<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\View\HtmlView;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class SppropertyViewGalleries extends HtmlView
{
    protected $items;
    protected $pagination;

    public function display($tpl = null)
    {

        $model              = $this->getModel();
        $this->items        = $this->get("Items");
        $this->pagination   = $this->get('Pagination');
        $doc                = Factory::getDocument();

        $app                = Factory::getApplication();
        $menu               = $app->getMenu()->getActive();
        $mParams            = $menu->getParams();
        $this->columns      = $mParams->get('columns', 4);
        $menuItemId         = SppropertyHelper::getItemid('galleries');

        $gallery_style      = $mParams->get('gallery_style', 'masonry');
        $this->albums       = array();

        if ($gallery_style == 'masonry') {
            $this->albums = $this->masonary($this->items, $this->columns, array('menuItemId' => $menuItemId));
        } elseif ($gallery_style == 'rectangle') {
            $this->albums = $this->rectangle($this->items, $this->columns, array('menuItemId' => $menuItemId));
        }

        return parent::display($tpl = null);
    }

    private function masonary($items, $columns, $contents = array())
    {
        $col                = round(count($items) / $columns);
        $perCol             = floor(count($items) / $columns);
        $extra              = count($this->items) % $columns;

        //Generate thumb and tower image within ration 2:1
        $cThumb             = ceil(($col * 2) / 3);
        $cTower             = floor(($col * 1) / 3);
        $thumbChoice        = array($cThumb, $cTower);


        $thumbs             = array(
            array('property_thumbnail','360x207'),
            array('property_thumbnail_tower','640x715')
        );

        $ind = 0;
        $length = count($items);
        $arr = array();
        for ($i = 0; $i < $columns && $ind < $length; $i++) {
            $temp = array();
            for ($j = 0; $j < $perCol; $j++) {
                $obj = new stdClass();
                $obj->image = $items[$ind]->image;
                $obj->title = $items[$ind]->title;
                $obj->id = $items[$ind]->id;
                $obj->alias = $items[$ind]->alias;
                $g      = json_decode($items[$ind]->gallery, true);
                $chk    = SppropertyHelper::old2new($g, ['photo', 'alt_text'], 'gallery');
                if (!empty($chk)) {
                    $obj->count = count($chk);
                } else {
                    $obj->count = count((array)$g);
                }
                $temp[] = $obj;
                $ind++;
            }

            if ($extra > 0) {
                $obj = new stdClass();
                $obj->image = $items[$ind]->image;
                $obj->title = $items[$ind]->title;
                $obj->id    = $items[$ind]->id;
                $obj->alias = $items[$ind]->alias;
                $g      = json_decode($items[$ind]->gallery, true);
                $chk    = SppropertyHelper::old2new($g, ['photo', 'alt_text'], 'gallery');
                if (!empty($chk)) {
                    $obj->count = count($chk);
                } else {
                    $obj->count = count((array)$g);
                }
                $temp[] = $obj;
                $ind++;
                $extra--;
            }
            $arr[] = $temp;
        }

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
                    $item->album = SppropertyHelper::getThumbs($item->image, $tempTh[0], $tempTh[1]);
                    $item->url  = Route::_('index.php?option=com_spproperty&view=gallery&id=' . $item->id . ':' . $item->alias . $contents['menuItemId']);
                } else {
                    $index      = ($randThumb + 1) % 2;
                    if ($tempTC[$index] > 0) {
                        $tempTC[$index]--;
                    }
                    $tempTh     = $thumbs[$index];
                    $item->album = SppropertyHelper::getThumbs($item->image, $tempTh[0], $tempTh[1]);
                    $item->url  = Route::_('index.php?option=com_spproperty&view=gallery&id=' . $item->id . ':' . $item->alias . $contents['menuItemId']);
                }
            }
        }

        return $arr;
    }

    // private function square($items, $columns, $contents = array()) {

    // }

    private function rectangle($items, $columns, $contents = array())
    {
        $thumbs = array('property_thumbnail','360x207');
        $col                = round(count($items) / $columns);
        $perCol             = floor(count($items) / $columns);
        $extra              = count($items) % $columns;

        $ind = 0;
        $length = count($items);
        $arr = array();
        for ($i = 0; $i < $columns && $ind < $length; $i++) {
            $temp = array();
            for ($j = 0; $j < $perCol; $j++) {
                $obj = new stdClass();
                $obj->image = $items[$ind]->image;
                $obj->title = $items[$ind]->title;
                $obj->id = $items[$ind]->id;
                $obj->alias = $items[$ind]->alias;
                $g      = json_decode($items[$ind]->gallery, true);
                $chk    = SppropertyHelper::old2new($g, ['photo', 'alt_text'], 'gallery');
                if (!empty($chk)) {
                    $obj->count = count(array($chk));
                } else {
                    $obj->count = count(array($g));
                }
                $temp[] = $obj;
                $ind++;
            }

            if ($extra > 0) {
                $obj = new stdClass();
                $obj->image = $items[$ind]->image;
                $obj->title = $items[$ind]->title;
                $obj->id    = $items[$ind]->id;
                $obj->alias = $items[$ind]->alias;
                $g      = json_decode($items[$ind]->gallery, true);
                $chk    = SppropertyHelper::old2new($g, ['photo', 'alt_text'], 'gallery');
                if (!empty($chk)) {
                    $obj->count = count(array($chk));
                } else {
                    $obj->count = count(array($g));
                }
                $temp[] = $obj;
                $ind++;
                $extra--;
            }
            $arr[] = $temp;
        }

        foreach ($arr as $value) {
            foreach ($value as $item) {
                $item->album = SppropertyHelper::getThumbs($item->image, $thumbs[0], $thumbs[1]);
                $item->url  = Route::_('index.php?option=com_spproperty&view=gallery&id=' . $item->id . ':' . $item->alias . $contents['menuItemId']);
            }
        }
        return $arr;
    }
}
