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
use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Component\ComponentHelper;

class SppropertyHelper extends ContentHelper
{
    public static function getUserGroupId($groupName = 'Agent')
    {
        $db     = Factory::getDbo();
        $query  = $db->getQuery(true);
        $query->select('a.id')
        ->from($db->quoteName('#__usergroups', 'a'))
        ->where($db->quoteName('a.title') . ' = ' . $db->quote($groupName));

        $db->setQuery($query);
        return $db->loadResult();
    }

    public static function isDesiredGroup($groupName = 'Agent', $userid = null)
    {
        $user       = Factory::getUser();
        if (is_null($userid)) {
            $userid = $user->id;
        }
        $groups     = Access::getGroupsByUser($userid, false);
        $groupID    = self::getUserGroupId($groupName);
        $return     = in_array($groupID, $groups);

        return $return;
    }

    public static function userAgentId($userid)
    {
        if (!self::isDesiredGroup(ComponentHelper::getParams('com_spproperty')->get('agent_group_name', 'Agent'), $userid)) {
            return false;
        }
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('a.id')
        ->from($db->qn('#__spproperty_agents', 'a'))
        ->where($db->qn('a.created_by') . ' = ' . $db->q($userid))
        ->where($db->qn('a.published') . ' = 1');

        $db->setQuery($query);
        return $db->loadResult();
    }

    // Get features
    public static function getPfeatures($fetid = '')
    {

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select(array('a.*'));
        $query->from($db->quoteName('#__spproperty_propertyfeatures', 'a'));
        if ($fetid) {
            $query->where($db->quoteName('a.id') . '=' . $fetid);
        }
        //Language
        $query->where('a.language IN (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
        $query->where($db->quoteName('a.published') . ' = 1');
        $db->setQuery($query);

        if ($fetid) {
            $results = $db->loadObject();
        } else {
            $results = $db->loadObjectList();
        }
        return $results;
    }

    //Generate random property ID from title and creation date
    public static function generateID($title, $date)
    {
        $date   = HTMLHelper::date($date, 'ymd');
        $title  = strtoupper(substr($title, 0, 3));
        $random = rand(100, 999);
        return $title . $date . 'R' . $random;
    }

    //Debugging function. Removed in the production package
    public static function debug($data, $die = true)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
        if ($die) {
            die;
        }
    }

    /**
     * Get Joomla Version
     *
     * @param string $type
     * @return void
     */
    public static function getVersion($type = 'major')
    {
        $version = JVERSION;
        list ($major, $minor, $patch) = explode('.', $version);

        if (strpos($patch, '-') !== false) {
            $patch = explode('-', $patch)[0];
        }

        switch ($type) {
            case 'minor':
                return (int) $minor;
            case 'patch':
                return (int) $patch;
            case 'major':
            default:
                return (int) $major;
        }
    }

    /**
     * Get locales based on the currency setting.
     *
     * This function retrieves the locales based on the currency setting
     * specified in the Joomla component parameters.
     *
     * @return string The locales corresponding to the currency setting.
     * @since  4.1.2
     */
    public static function getLocalesFromCurrency()
    {
        $params   = ComponentHelper::getParams('com_spproperty');
        $currency = $params->get('currency', 'USD:$');

        //Get Currency
        $currency = explode(':', $currency);
        $locales  = ($currency[0] === "EUR") ? "it_IT" : "en_US";

        return $locales;
    }


    /**
     * Get the formatted price based on currency locales.
     *
     * This function retrieves the locales corresponding to the currency,
     * sets the locale, and then formats the price accordingly.
     *
     * @param  string $price -- The price to be formatted.
     * @return string        -- The formatted price.
     * @since  4.1.2
     */
    public static function getFormattedPrice($price)
    {
        $price   = preg_replace('/[^0-9.,]/', '', $price);
        $locales = self::getLocalesFromCurrency();
        setlocale(LC_ALL, $locales);

        $localesInfo  = localeconv();
        $decimalPoint = $localesInfo['decimal_point'];

        if ($decimalPoint === '.') {
            return str_replace(',', '', $price);
        }

        if ($decimalPoint === ',') {
            $tempPrice = str_replace('.', '', $price);
            return str_replace(',', '.', $tempPrice);
        }

        return null;
    }


    /**
     * Retrieves property features for all languages.
     *
     * @return array Property features data for all languages.
     * @since  4.1.2
     */
    public static function getPropertyFeatures()
    {
        $languages      = LanguageHelper::getLanguages();
        $dataByLanguage = [];
        $languageCodes  = [];
        $languageCodes  = array_merge(array_column($languages, 'lang_code'), ['*']);

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__spproperty_propertyfeatures')
                    ->where('published = 1')
                    ->whereIn($db->quoteName('language'),$languageCodes);

        $db->setQuery($query);

        $results = $db->loadObjectList();

        foreach ($results as $item) {
            $dataByLanguage[$item->language][] = $item;
        }

        return $dataByLanguage;
    }

    /**
     * Format the given price based on the locales from currency.
     *
     * @param  float  $price The price to format.
     * @return string        The formatted price.
     * @since  4.1.2
     */
    public static function getPriceBasedOnLocales($price)
    {
        $local = self::getLocalesFromCurrency();
        
        $formattedPrice = number_format((float) $price, 2, '.', '');

        if ($local === 'it_IT') {
            // Italian format: 1.234,56
            $formattedPrice = number_format((float) $price, 2, ',', '.');
        } else {
            // US/International format: 1,234.56
            $formattedPrice = number_format((float) $price, 2, '.', ',');
        }
        
        return $formattedPrice;
    }

    /**
     * Get the formatted property status.
     *
     * @param  string $status The property status.
     * @return string         The formatted property status.
     * @since  4.1.2
     */
    public static function getFormatedPropertyStatus($status)
    {
        switch ($status) {
            case 'rent':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_RENT');
                break;
            case 'sale':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
                break;
            case 'in_hold':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_HOLD');
                break;
            case 'pending':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_PENDING');
                break;
            case 'sold':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_SOLD');
                break;
            case 'under_offer':
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_IN_UNDER_OFFER');
                break;
            default:
                return Text::_('COM_SPPROPERTY_FIELD_PROPERTY_STATUS_SELL');
                break;
        }
    }
}
