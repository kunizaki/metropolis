<?php

/**
 * @package     SP Property
 *
 * @copyright   Copyright (C) 2010 - 2015 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

class SppropertyViewAgents extends FOFViewForm{

	public function display($tpl = null){

		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();

		$app = JFactory::getApplication();

		// get the active item
		$params  = $app->getMenu()->getActive()->params;
		$order_by = $params->get('order_by', '');

		// get current menu id
		$Itemid = $this->input->get('Itemid', 0, 'INT');

		// get component params
		jimport('joomla.application.component.helper');
		$this->cParams 	= JComponentHelper::getParams('com_spproperty');
		// get columns
		$this->columns 	= $this->cParams->get('agents_columns', 3);
		// get property types

		foreach ($this->items as $this->item) {
			$this->item->url = JRoute::_('index.php?option=com_spproperty&view=agent&id='. $this->item->spproperty_agent_id .':'. $this->item->slug . SppropertyHelper::getItemid('agents'));
		}

		//Generate Item Meta
		if(count($this->items)) {
			$itemMeta = array();
			//$itemMeta['image'] = JURI::base() . $this->items[0]->image;
			SppropertyHelper::itemMeta($itemMeta);
		}

		return parent::display($tpl = null);
	}
}