<?php

/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

?>

<div id="sp-property-properties" class="spproperty-view-properties spproperty">
    <?php if (count($this->items)) { ?>
        <?php echo LayoutHelper::render('properties.properties', array('properties' => $this->items, 'columns' => $this->columns)); ?>
        <?php if ($this->pagination->get('pages.total') > 1) { ?>
            <div class="pagination">
                <?php echo $this->pagination->getPagesLinks(); ?>
            </div>
        <?php } ?>
    <?php } else { ?>
        <div class="sp-no-item-found">
            <p><?php echo Text::_('COM_SPPROPERTY_NO_ITEMS_FOUND'); ?></p>
        </div>
    <?php } ?>
</div>

