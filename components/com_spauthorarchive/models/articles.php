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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Access\Access;
use Joomla\CMS\MVC\Model\ListModel;


if(!class_exists('ContentHelperRoute')) require_once (JPATH_SITE . '/components/com_content/helpers/route.php');

class SpauthorarchiveModelArticles extends ListModel {
	
	protected function populateState($ordering = null, $direction = null)
	{
		$app = Factory::getApplication('site');
		$params = $app->getParams();
		$itemId = $app->input->getInt('uid');
		$this->setState('author.id', $itemId);
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		$limit = $params->get('art_limit', 9);
		$this->setState('list.limit', $limit);
	}

	protected function getListQuery()
	{
		$app = Factory::getApplication();
		$user = Factory::getUser();
		// Get Params
		$params   = $app->getMenu()->getActive()->getParams();
		$ordering = $params->get('art_ordering', ' DESC');

		$user = Factory::getUser();
		$author_id = (!empty($author_id))? $author_id : (int) $this->getState('author.id');
			
		// start
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__content', 'a'));
		$query->select($db->quoteName('b.alias', 'category_alias'));
		$query->select($db->quoteName('b.title', 'category'));
		$query->join('LEFT', $db->quoteName('#__categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('b.extension') . ' = ' . $db->quote('com_content'));

		if($author_id) {
			$query->where($db->quoteName('a.created_by') . ' = ' . $db->quote($author_id));
		}

		$query->where($db->quoteName('a.state') . ' = ' . $db->quote(1));
		$query->where('(a.publish_up = ' . $nullDate . 'OR a.publish_up IS NULL OR a.publish_up <= ' . $nowDate . ')');
		$query->where('(a.publish_down = ' . $nullDate . 'OR a.publish_down IS NULL OR a.publish_down >= ' . $nowDate . ')');

		// has order by
		if ($ordering == 'hits') {
			$query->order($db->quoteName('a.hits') . ' DESC');
		} elseif($ordering == 'featured') {
			$query->where($db->quoteName('a.featured') . ' = ' . $db->quote(1));
			$query->order($db->quoteName('a.publish_up') . ' DESC');
		} elseif($ordering == 'oldest') {
			$query->order($db->quoteName('a.publish_up') . ' ASC');
		} elseif($ordering == 'alphabet_asc') {
			$query->order($db->quoteName('a.title') . ' ASC');
		} elseif($ordering == 'alphabet_desc') {
			$query->order($db->quoteName('a.title') . ' DESC');
		} elseif($ordering == 'random') {
			$query->order($query->Rand());
		} else {
			$query->order($db->quoteName('a.publish_up') . ' DESC');
		}

		// Language filter
		if ($app->getLanguageFilter()) {
			$query->where('a.language IN (' . $db->Quote(Factory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")");

		return $query;
	}


	/**
	 * getAuthorPosts
	 *
	 * @param  mixed $author_id
	 *
	 * @return void
	 */
	public static function getAuthorPosts($author_id, $limit = 5) {

		$user = Factory::getUser();
		// start
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$app = Factory::getApplication();
		$db = Factory::getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		$query = $db->getQuery(true);

		$query->select('a.*');
		$query->from($db->quoteName('#__content', 'a'));
		$query->select($db->quoteName('b.alias', 'category_alias'));
		$query->select($db->quoteName('b.title', 'category'));
		$query->join('LEFT', $db->quoteName('#__categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('b.extension') . ' = ' . $db->quote('com_content'));

		if($author_id) {
			$query->where($db->quoteName('a.created_by') . ' = ' . $db->quote($author_id));
		}

		$query->where($db->quoteName('a.state') . ' = ' . $db->quote(1));
		$query->where('(a.publish_up = ' . $nullDate . 'OR a.publish_up IS NULL OR a.publish_up <= ' . $nowDate . ')');
		$query->where('(a.publish_down = ' . $nullDate . 'OR a.publish_down IS NULL OR a.publish_down >= ' . $nowDate . ')');
		
		$query->setLimit($limit);	
		$query->order($db->quoteName('a.publish_up') . ' DESC');

		// Language filter
		if ($app->getLanguageFilter()) {
			$query->where('a.language IN (' . $db->Quote(Factory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		$query->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")");
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->slug    	= $result->id . ':' . $result->alias;
			$result->catslug 	= $result->catid . ':' . $result->category_alias;
			$result->username   = Factory::getUser($result->created_by)->name;
			$result->link 	    = Route::_(ContentHelperRoute::getArticleRoute($result->slug, $result->catid, $result->language));
		}

		return $results;

	}


}
