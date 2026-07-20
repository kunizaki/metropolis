<?php

/**
 * @package     SP Property
 *
 * @copyright   Copyright (C) 2010 - 2015 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

class SppropertyViewProperties extends FOFViewForm{

	public function display($tpl = null){

		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();

		$app = JFactory::getApplication();
		// get the active item
		$menu 			  = $app->getMenu()->getActive();
		$params				= $menu->params;
		$order_by 		= $params->get('order_by', '');
		$pstatus   		= $params->get('property_status', '');
		$this->catid  = $params->get('catid', '');

		// get current menu id
		$Itemid = $this->input->get('Itemid', 0, 'INT');
		$sort_catid = $this->input->get('catid', 0, 'INT');

		if ($sort_catid) {
			$cat_info 			= $model->getCatInfo($sort_catid);
			if ($menu) {
				if($params->get('page_title_alt', '')){
					$page_title = $params->get('page_title_alt', '') . ' - ' . $cat_info->title;
				} elseif($params->get('page_title')) {
					$page_title = $params->get('page_title', '') . ' - ' . $cat_info->title;
				} elseif($menu->title) {
					$page_title = $menu->title . ' - ' . $cat_info->title;
				} else {
					$page_title = $menu->title . ' - ' . $cat_info->title;
				}
			}
			SppropertyHelper::itemMeta(array( 'title' => $page_title));
		}

		// get component params
		jimport('joomla.application.component.helper');
		$this->cParams 	= JComponentHelper::getParams('com_spproperty');
		// get columns
		$this->columns 	= $this->cParams->get('properties_columns', 2);
		// get property types
		$this->property_total 		= $model->countProperties('', $order_by, $pstatus);
		$this->property_types 		= $model->getCategories(0, $order_by, $pstatus);
		// al property URL
		$this->all_properties_url 	= 'index.php?option=com_spproperty&view=properties&Itemid=' . $Itemid;

		foreach ($this->items as $this->item) {
			$this->item->url = JRoute::_('index.php?option=com_spproperty&view=property&id='. $this->item->spproperty_property_id .':'. $this->item->slug . SppropertyHelper::getItemid('properties'));
			$this->item->price = SppropertyHelper::generateCurrency($this->item->price);
		}

		return parent::display($tpl = null);
	}
}
