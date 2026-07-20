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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;

class JFormFieldSpagents extends ListField
{
    protected $type   = 'spagents';
    protected $layout = 'joomla.form.field.list-fancy-select';

    private function getAgents($userid = null)
    {
        $db         = Factory::getDbo();
        $query      = $db->getQuery(true);

        $query->select('a.*')
        ->from($db->qn('#__spproperty_agents', 'a'))
        ->where($db->qn('a.published') . ' = 1');
        if (!is_null($userid)) {
            $query->where($db->qn('a.created_by') . ' = ' . $db->q($userid));
        }

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    protected function getOptions()
    {
        $db         = Factory::getDbo();
        $query      = $db->getQuery(true);
        $user       = Factory::getUser();
        $userid     = $user->id;
        $cParams    = ComponentHelper::getParams('com_spproperty');
        $groupTitle = $cParams->get('agent_group_name', 'Agent');
        $superUsers = SppropertyHelper::isDesiredGroup('Super Users') || $user->authorise('core.admin');
        $agentGroup = SppropertyHelper::isDesiredGroup($groupTitle);
        $agents     = array();

        if ($superUsers) {
            $agents = $this->getAgents();
        } else {
            if ($agentGroup) {
                $agents = $this->getAgents($userid);
            }
        }

        $options = [];
        if (!empty($agents)) {
            foreach ($agents as $v) {
                $options[] = HTMLHelper::_('select.option', $v->id, $v->title);
            }
        }
        return array_merge(parent::getOptions(), $options);
    }
}
