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
use Joomla\CMS\MVC\Model\ListModel;

class SpauthorarchiveModelAuthors extends ListModel
{

	protected function getListQuery()
	{
		// Initialize variables.
		$app = Factory::getApplication();
		$user = Factory::getUser();
		// Get Params
		$params 	= $app->getParams();
		$menu 		= Factory::getApplication()->getMenu()->getActive();
		if($menu)
		{
			$params->merge($menu->getParams());
		}
		$ordering		= $params->get('ordering', ' reg-desc');
		$usergroup_ids 	= $params->get('user_groups', array());
		$show_authors 	= $params->get('show_authors', 'have_article');

		//if user group's isn't assign then get groups
		if (empty($usergroup_ids) && !count($usergroup_ids))
		{
			$groups_info = $this->getUserGroupIds( array('Author', 'Editor', 'Publisher', 'Manager') );
			$usergroup_ids = array_column($groups_info, 'id');
		}	

		$group_ids = $groups = sprintf("'%s'", implode("','", $usergroup_ids ) );
		
		// start DB query
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('u.id', 'u.name', 'u.username', 'u.email')));
		$query->from('#__user_usergroup_map as a');
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id') . ')');
		
		if($show_authors =='have_article')
		{
			$query->join('RIGHT', $db->quoteName('#__content', 'c') . ' ON (' . $db->quoteName('u.id') . ' = ' . $db->quoteName('c.created_by') . ')');
		}
		
		$query->where('a.group_id IN (' . $group_ids . ')');
		$query->where($db->qn('u.block')." = ".$db->quote('0'));
		$query->group($db->quoteName('u.id'));

		if ($ordering == 'name-asc')
		{
			$query->order('u.name ASC');
		}
		elseif ($ordering == 'name-desc')
		{
			$query->order('u.name DESC');
		}
		elseif ($ordering == 'reg-asc')
		{
			$query->order('u.registerDate ASC');
		}
		else
		{
			$query->order('u.registerDate DESC');
		}

		return $query;
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('limit', 6);
		
		$this->setState('list.limit', $limit);
	}

	// get user groups id
	protected function getUserGroupIds($groups_name = array())
	{
		$db    = Factory::getDbo();
		$groups = sprintf("'%s'", implode("','", $groups_name ) );
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__usergroups as a');
		$query->where('a.title IN (' . $groups . ')');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	// get user profile data
	public function getUserProfileData($user_id = 0)
	{
		$userId = isset($user_id) ? $user_id : 0;

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('a.profile_key', 'a.profile_value')));
		$query->from('#__user_profiles as a');
		$query->where($db->quoteName('a.user_id')." = ".$db->quote((int) $userId));
		$query->where($db->quoteName('a.profile_key') . " LIKE '" . 'profilespaarchive' . "%'");
		$query->order('ordering DESC');
		$db->setQuery($query);

		try {
			$results = $db->loadRowList();
		} catch (\Exception $e) {
			return false;
		}

		// Merge the profile data.
		$profilespaarchive = array();

		if (isset($results) && !empty($results))
		{
			foreach ($results as $v) {
				$k = str_replace('profilespaarchive.', '', $v[0]);
				$profilespaarchive[$k] = json_decode($v[1], true);
			}
		}

		return $profilespaarchive;

	} //getUserProfileData
	

}
