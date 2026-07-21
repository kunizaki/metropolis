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
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyHelper
{
    public static function debug($data, $die = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($die) {
            die;
        }
    }

    public static function pluralize($amount, $singular, $plural)
    {
        $amount = (int)$amount;
        if ($amount <= 1) {
            return Text::_($singular);
        }
        return Text::_($plural);
    }

    public static function getGroupInfo($userid)
    {
        $db         = Factory::getDbo();
        $query      = $db->getQuery(true);
        $groupid    = Access::getGroupsByUser($userid, false);

        $query->select('a.*')->from($db->qn('#__usergroups', 'a'))->where($db->qn('a.id') . ' = ' . $db->q($groupid[0]));
        $db->setQuery($query);

        return $db->loadObject();
    }
    public static function getAgentsInfo($groupName = 'Agent')
    {
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('a.id')
        ->from($db->quoteName('#__usergroups', 'a'))
        ->where($db->quoteName('a.title') . ' = ' . $db->quote($groupName));

        $db->setQuery($query);
        $groupid = $db->loadResult();

        $udb = Factory::getDbo();
        $uquery = $udb->getQuery(true);
        $uquery->select('um.user_id')->from($udb->qn('#__user_usergroup_map', 'um'))->where($udb->qn('um.group_id') . '=' . $udb->q($groupid));
        $udb->setQuery($uquery);

        $results = $udb->loadObjectList();
        $agents = [];
        foreach ($results as $r) {
            $agents[] = self::getUserProfile($r->user_id);
        }
        return $agents;
    }

    public static function getUserProfile($userid)
    {
        $adb = Factory::getDbo();
        $aquery = $adb->getQuery(true);
        $aquery->select('up.*, u.name, u.email')
        ->from($adb->qn('#__user_profiles', 'up'))
        ->join('LEFT', $adb->qn('#__users', 'u') . ' ON ( ' . $adb->qn('up.user_id') . ' = ' . $adb->qn('u.id') . ' )')
        ->where($adb->qn('up.user_id') . ' = ' . $userid);

        $adb->setQuery($aquery);
        $results = $adb->loadObjectList();
        $object = new \stdClass();
        foreach ($results as $agent) {
            if (isset($agent->profile_key) && $agent->profile_key) {
                $key = explode('.', $agent->profile_key);
                if (count($key) > 1) {
                    $key            = $key[1];
                    $object->$key   = json_decode($agent->profile_value, true);
                    $object->title  = $agent->name;
                    $object->email  = $agent->email;
                    $object->id     = $agent->user_id;
                }
            }
        }
        return $object;
    }

    // Common
    public static function getItemid($view = 'properties', $modifier = array())
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('id')));
        $query->from($db->quoteName('#__menu'));
        $query->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_spproperty&view=' . $view . '%'));
        if (!empty($modifier)) {
            foreach ($modifier as $v) {
                $query->where($db->quoteName($v[0]) . ' ' . $v[1] . ' ' . $db->quote($v[2]));
            }
        }
        $query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
        $db->setQuery($query);
        //return (string)$query;
        $result = $db->loadResult();
        if (!empty($result)) {
            return '&Itemid=' . $result;
        } else {
            unset($db, $query);
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('id')));
            $query->from($db->quoteName('#__menu'));
            $query->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_spproperty&view=' . $view . '%'));
            $query->where('language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
            $query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
            $db->setQuery($query);
            //return (string)$query;
            $result = $db->loadResult();
            if (!empty($result)) {
                return '&Itemid=' . $result;
            }
        }

        return false;
    }

    //Old to new Data
    public static function old2new($data, $keys = array(), $prefix = '')
    {
        if (empty($data)) {
            $data = [];
        }

        foreach ($keys as $key) {
            if (!array_key_exists($key, (array)$data)) {
                return false;
            }
        }

        $index = 0;
        $arr = array();
        foreach ($data[$keys[0]] as $i => $d) {
            $temp = array();
            foreach ($keys as $k) {
                $temp[$k] = $data[$k][$i];
            }

            $arr[$prefix . $index] = $temp;
            $index++;
        }
        return $arr;
    }

    // Item Meta
    public static function itemMeta($meta = array())
    {
        $config     = Factory::getConfig();
        $app        = Factory::getApplication();
        $doc        = Factory::getDocument();
        $menus      = $app->getMenu();
        $menu       = $menus->getActive();
        $title      = '';

        ($menu) ? $params = $menu->getParams() : $params = new Registry();

        //Title
        if (isset($meta['title']) && $meta['title']) {
            $title = $meta['title'];
        } else {
            if ($menu) {
                if ($params->get('page_title', '')) {
                    $title = $params->get('page_title');
                } else {
                    $title = $menu->title;
                }
            }
        }

        //Include Site title
        $sitetitle = $title;
        if ($config->get('sitename_pagetitles') == 2) {
            $sitetitle = $title . ' | ' . $config->get('sitename');
        } elseif ($config->get('sitename_pagetitles') === 1) {
            $sitetitle = $config->get('sitename') . ' | ' . $title;
        }

        $doc->setTitle($sitetitle);
        $doc->addCustomTag('<meta content="' . $title . '" property="og:title" />');

        //Keywords
        if (isset($meta['keywords']) && $meta['keywords']) {
            $keywords = $meta['keywords'];
            $doc->setMetadata('keywords', $keywords);
        } else {
            if ($menu) {
                if ($params->get('menu-meta_keywords')) {
                    $keywords = $params->get('menu-meta_keywords');
                    $doc->setMetadata('keywords', $keywords);
                }
            }
        }

        //Metadescription
        if (isset($meta['metadesc']) && $meta['metadesc']) {
            $metadesc = $meta['metadesc'];
            $doc->setDescription($metadesc);
            $doc->addCustomTag('<meta content="' . $metadesc . '" property="og:description" />');
        } else {
            if ($menu) {
                if ($params->get('menu-meta_description')) {
                    $metadesc = $params->get('menu-meta_description');
                    $doc->setDescription($params->get('menu-meta_description'));
                    $doc->addCustomTag('<meta content="' . $metadesc . '" property="og:description" />');
                }
            }
        }

        //Robots
        if ($menu) {
            if ($params->get('robots')) {
                $doc->setMetadata('robots', $params->get('robots'));
            }
        }

        //Open Graph
        foreach ($doc->_links as $k => $array) {
            if ($array['relation'] == 'canonical') {
                unset($doc->_links[$k]);
            }
        } // Remove Joomla canonical

        $doc->addCustomTag('<meta content="website" property="og:type"/>');
        $doc->addCustomTag('<link href="' . Uri::current() . '" rel="canonical" />');
        $doc->addCustomTag('<meta content="' . Uri::current() . '" property="og:url" />');

        if (isset($meta['image']) && $meta['image']) {
            $doc->addCustomTag('<meta content="' . $meta['image'] . '" property="og:image" />');
        }
    }

    public static function getThumbs($image, $thumb_size, $default)
    {
        $params = ComponentHelper::getParams('com_spproperty');
        $thumb  = $params->get($thumb_size, $default);

        $filename = !empty($image) ? basename($image) : null;
        $dirname  = !empty($image) ? dirname($image) : null;

        if (!is_null($filename) && !is_null($dirname)) {
            $customizePath = Path::clean(JPATH_BASE . '/' . $dirname . '/thumbs/' . File::stripExt($filename) . '_' . $thumb . '.' . self::getExt($filename));
            $imageSrc = Path::clean(Uri::base(true) . '/' . $dirname . '/thumbs/' . File::stripExt($filename) . '_' . $thumb . '.' . self::getExt($filename));

            if (file_exists($customizePath)) {
                return $imageSrc;
            }
        }
        
        return Uri::root() . $image;
    }

    public static function getExt($file)
    {
        // String manipulation should be faster than pathinfo() on newer PHP versions.
        $dot = strrpos($file, '.');

        if ($dot === false) {
            return '';
        }

        $ext = substr($file, $dot + 1);

        // Extension cannot contain slashes.
        if (strpos($ext, '/') !== false || (DIRECTORY_SEPARATOR === '\\' && strpos($ext, '\\') !== false)) {
            return '';
        }

        return $ext;
    }

    public static function formatPrice($price)
    {
        $price = (int)$price;
        if ($price >= 1000 && $price < 1000000) {
            $price /= 1000;
            if (is_float($price)) {
                $price = number_format($price, 2, '.', '');
            }
            $price .= "K";
        } elseif ($price >= 1000000 && $price < 1000000000) {
            $price /= 1000000;
            if (is_float($price)) {
                $price = number_format($price, 2, '.', '');
            }
            $price .= "M";
        } elseif ($price >= 1000000000) {
            $price /= 1000000000;
            if (is_float($price)) {
                $price = number_format($price, 2, '.', '');
            }
            $price .= "T";
        }
        return $price;
    }


    // Generate Currency
    public static function generateCurrency($amt = 0, $curr = null, $pos = null, $format = null)
    {

        //Joomla Component Helper & Get Property Params
        $params = ComponentHelper::getParams('com_spproperty');

        if (empty($curr)) {
            $curr = $params->get('currency', 'USD:$');
        }

        if (empty($pos)) {
            $pos = $params->get('currency_position', 'left');
        }

        if (empty($format)) {
            $format = $params->get('currency_format', 'short');
        }

        //Get Currency
        $currency           = explode(':', $curr);
        $currency_format    = $format;
        $currency_position  = $pos;
        $local              = "";

        $symbol = ($currency_format == 'short') ? $currency[1] : $currency[0];      
        $amt    = $params->get('show_rounds_price') ? round($amt) : $amt;

        if ($params->get('use_number_format')) {

            $decimals = $params->get('show_rounds_price', '0') == 0 ? 2 : 0;

            switch ($currency[0]) {
                case "EUR":
                    $local = "it_IT";
                    break;
                case "BRL":
                    $local = "pt_BR";
                    break;
                default:
                    $local = "en_US";
                    break;
            }

            if ($local === 'it_IT' || $local === 'pt_BR') {
                $amt = number_format($amt, $decimals, ',', '.');
            } else {
                $amt = number_format($amt, $decimals, '.', ',');
            }
        }

        $result = ($currency_position == 'left') ? $symbol . ' ' . $amt : $amt . ' ' . $symbol;

        return $result;
    }

    /**
     * Get Calculated Property Price
     *
     * @param string $solidPrice
     * @param string $propertySize
     * @return float|int
     * @since 3.1.2
     */
    public static function getCalculatedPrice($solidPrice, $propertySize)
    {
        return empty($propertySize) ? (float) $solidPrice : (float) $solidPrice * (float) $propertySize;
    }
}
