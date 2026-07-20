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
use Joomla\CMS\Component\ComponentHelper;

// get component params
$cParams = ComponentHelper::getParams('com_spproperty');
$property_rm_btn = $cParams->get('prpry_rm_btn_text', Text::_('COM_SPPROPERTY_PROPERTIES_BTN_TEXT'));

$properties = $displayData['properties'];
$columns = $displayData['columns'];



if (!empty($properties) && count($properties)) { ?>
    <?php foreach (array_chunk($properties, $columns) as $properties) { ?>
        <div class="row">
            <?php foreach ($properties as $property) { ?>
                <div class="spproperty-col-sm-<?php echo round(12 / $columns); ?>">
                    <?php echo LayoutHelper::render('properties.property', array('property' => $property)); ?>
                </div> <!-- /.spproperty-col-sm -->
            <?php } ?>
        </div><!--/.row-->
    <?php } ?>
<?php } else { ?>
    <div class="row">
        <div class="spproperty-col-sm-12 sp-no-item-found">
            <p><?php echo Text::_('COM_SPPROPERTY_NO_ITEMS_FOUND'); ?></p>
        </div>
    </div>
<?php } ?>

