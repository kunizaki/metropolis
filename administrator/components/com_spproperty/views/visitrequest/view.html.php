
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

class SppropertyViewVisitrequest extends HtmlView
{
    protected $item;
    protected $form;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        if (count($errors = $this->get('Errors'))) {
            throw new RuntimeException(implode("\n", $errors), 500);
            return false;
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

        ToolbarHelper::title(Text::_('COM_SPPROPERTY') . ": " . Text::_('COM_SPPROPERTY_TITLE_VISITREQUESTS_EDIT'), '');

        if ($canDo->get('core.edit') || $canDo->get('core.edit.own')) {
            ToolbarHelper::apply('visitrequest.apply', 'JTOOLBAR_APPLY');
            ToolbarHelper::save('visitrequest.save', 'JTOOLBAR_SAVE');
            ToolbarHelper::save2new('visitrequest.save2new');
        }

        ToolbarHelper::cancel('visitrequest.cancel', 'JTOOLBAR_CLOSE');
    }
}
