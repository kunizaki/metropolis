<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2018 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\MVC\Controller\BaseController;

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

class SpauthorarchiveControllerArticles extends BaseController
{

	public function getModel($name = 'form', $prefix = '', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config); 
		return $model; 
	}

	public function display($cachable = false, $urlparams = false, $tpl = null)
	{
		$cachable = true;

		if (!is_array($urlparams))
		{
			$urlparams = [];
		}
		
		$additionalParams = array(
			'catid' => 'INT',
			'id' => 'INT',
			'cid' => 'ARRAY',
			'year' => 'INT',
			'month' => 'INT',
			'limit' => 'UINT',
			'limitstart' => 'UINT',
			'showall' => 'INT',
			'return' => 'BASE64',
			'filter' => 'STRING',
			'filter_order' => 'CMD',
			'filter_order_Dir' => 'CMD',
			'filter-search' => 'STRING',
			'print' => 'BOOLEAN',
			'lang' => 'CMD',
			'Itemid' => 'INT');

		$urlparams = array_merge($additionalParams, $urlparams);
		
		parent::display($cachable, $urlparams, $tpl);
	}
	
}
