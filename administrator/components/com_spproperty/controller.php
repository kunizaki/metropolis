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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;

class SppropertyController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        $view   = $this->input->get('view', 'properties');
        $layout = $this->input->get('layout', 'default');
        $id     = $this->input->getInt('id');
        $this->input->set('view', $view);

        $user = Factory::getUser();
        $isadmin = $user->authorise('core.admin');

        $restrictedViews = array(
        'group' => ComponentHelper::getParams('com_spproperty')->get('agent_group_name', 'Agent'),
        'views' => array('agent', 'agents', 'category', 'categories')
        );
        $access = SppropertyHelper::isDesiredGroup($restrictedViews['group']) || SppropertyHelper::isDesiredGroup('Super Users') || $isadmin;
        $isAgent = SppropertyHelper::isDesiredGroup($restrictedViews['group']);
        if (!$access) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');
            $this->setRedirect(Route::_('index.php', false));
            return false;
        } else {
            if ($isAgent) {
                if (in_array($view, $restrictedViews['views'])) {
                    $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 'error');
                    $this->setRedirect(Route::_('index.php?option=com_spproperty&view=properties', false));
                    return false;
                }
            }
        }

        if (
            ($view == 'property' || $view == 'agent' || $view == 'category' || $view == 'propertyfeature' || $view == 'visitrequest')
            && $layout == 'edit'
            && (!$this->checkEditId('com_spproperty.edit.property', $id) && !$this->checkEditId('com_spproperty.edit.agent', $id) && !$this->checkEditId('com_spproperty.edit.propertyfeature', $id) && !$this->checkEditId('com_spproperty.edit.category', $id) && !$this->checkEditId('com_spproperty.edit.visitrequest', $id))
        ) {
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
            $this->setRedirect(Route::_('index.php?option=com_spproperty&view=properties', false));

            return false;
        }
        parent::display($cachable, $urlparams);

        return $this;
    }
}
