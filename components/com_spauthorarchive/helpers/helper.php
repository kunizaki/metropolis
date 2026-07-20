<?php
/**
 * @package com_spauthorarchive
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2021 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
class SpauthorarchiveHelper
{

    // Common
	public static function getItemid($view = 'authors')
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true); 
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE '. $db->quote('%option=com_spauthorarchive&view='. $view .'%'));
		$query->where($db->quoteName('published') . ' = '. $db->quote('1'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if (count((array) $result))
		{
			return '&Itemid=' . $result;
		}

		return false;
	}
	
	
}
