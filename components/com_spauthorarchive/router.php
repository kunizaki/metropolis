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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;

class SpauthorarchiveRouter extends RouterView
{

	protected $noIDs = false;

	public function __construct($app = null, $menu = null)
	{

		$params = ComponentHelper::getParams('com_spauthorarchive');
		$this->noIDs = (bool) $params->get('sef_ids', 1);

		//articles
		$authors = new RouterViewConfiguration('authors');
		$this->registerView($authors);

		$articles = new RouterViewConfiguration('articles');
		$articles->setKey('uid')->setParent($authors);
		$this->registerView($articles);

		$bookmarks = new RouterViewConfiguration('bookmarks');
		$this->registerView($bookmarks);

		// generate rules
		parent::__construct($app, $menu);	

		$this->attachRule(new NomenuRules($this));
		// $this->attachRule(new StandardRules($this));
		$this->attachRule(new MenuRules($this));
		
		// legacy
		JLoader::register('SpauthorarchiveRouterRulesLegacy', __DIR__ . '/helpers/legacyrouter.php');
		$this->attachRule(new SpauthorarchiveRouterRulesLegacy($this));
	}

	public function getArticlesSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db = Factory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('username'))
				->from($dbquery->qn('#__users'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);

			$id .= ':' . $db->loadResult();
		}
		
		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return [$void => $segment];
		}

		return [ (int)$id => $id];
	}

	public function getArticlesId($segment, $query)
	{
		
		if ($this->noIDs)
		{
			$db = Factory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('id'))
				->from($dbquery->qn('#__users'))
				->where('username = ' . $dbquery->q($segment));
			$db->setQuery($dbquery);

			$segment = $db->loadResult();
		}

		return (int) $segment;
	}


	
	// specialist
	public function getAuthorSegment($id, $query)
	{
		if (!strpos($id, ':'))
		{
			$db = Factory::getDbo();
			$dbquery = $db->getQuery(true);
			$dbquery->select($dbquery->qn('username'))
				->from($dbquery->qn('#__users'))
				->where('id = ' . $dbquery->q($id));
			$db->setQuery($dbquery);

			$id .= ':' . $db->loadResult();
		}

		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return array($void => $segment);
		}

		return array((int) $id => $id);
	}
	
}

/**
 * Users router functions
 *
 * These functions are proxys for the new router interface
 * for old SEF extensions.
 *
 * @param   array  &$query  REQUEST query
 *
 * @return  array  Segments of the SEF url
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function spauthorarchiveBuildRoute(&$query){
	$app = Factory::getApplication();
	$router = new SpauthorarchiveRouter($app, $app->getMenu());

	return $router->build($query);
}

/**
 * Convert SEF URL segments into query variables
 *
 * @param   array  $segments  Segments in the current URL
 *
 * @return  array  Query variables
 *
 * @deprecated  4.0  Use Class based routers instead
 */
function spauthorarchiveParseRoute($segments){
	$app = Factory::getApplication();
	$router = new SpauthorarchiveRouter($app, $app->getMenu());

	return $router->parse($segments);
}
