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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SppropertyViewAgent extends HtmlView
{
    protected $item;

    protected $form;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        if (count($errors = $this->get('Errors'))) {
            throw new \RuntimeException(implode('<br>', $errors), 500);
        }

        $this->addToolbar();

        return parent::display($tpl);
    }


    protected function addToolbar()
    {
        $input = Factory::getApplication()->input;
        $input->set('hidemainmenu', true);

        $user = Factory::getUser();
        $userId = $user->get('id');
        $isNew = $this->item->id == 0;
        $canDo = SppropertyHelper::getActions('com_spproperty', 'component');

        ToolbarHelper::title(Text::_("COM_SPPROPERTY_TITLE_AGENTS_EDIT"), '');

        if ($canDo->get('core.edit') || $canDo->get('core.create')) {
            ToolbarHelper::apply('agent.apply', 'JTOOLBAR_APPLY');
            ToolbarHelper::save('agent.save', 'JTOOLBAR_SAVE');
            ToolbarHelper::save2new('agent.save2new');
            ToolbarHelper::save2copy('agent.save2copy');
        }

        ToolbarHelper::cancel('agent.cancel', 'JTOOLBAR_CLOSE');
    }
}
