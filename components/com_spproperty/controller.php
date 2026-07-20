<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\MVC\Controller\BaseController;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

class SppropertyController extends BaseController
{
    public function display($cachable = false, $urlparams = false)
    {
        parent::display($cachable, $urlparams);
        return $this;
    }
}
