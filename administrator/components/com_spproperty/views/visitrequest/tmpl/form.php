<?php

/**
 * @package     SP Property
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('formbehavior.chosen', 'select');

echo $this->getRenderedForm();
