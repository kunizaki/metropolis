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
use Joomla\CMS\Layout\LayoutHelper;

$input         = Factory::getApplication()->input;
$catid         = $input->get('catid', null, 'INT');
$searchitem  = $input->get('searchitem', null, 'INT');
$sorting     = $input->get('sorting', 'ordering-desc', 'STRING');

?>
<?php if (!is_null($searchitem) && $searchitem == 1) { ?>
<div class="spproperty-sorting pull-right">
    <div class="sorting">
      <div class="form-group">
        <select name="sorting" id="sorting" class="form-control">
            <option value="ordering-asc" <?php echo $sorting == 'ordering-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ORDERING_ASC'); ?></option>
            <option value="ordering-desc" <?php echo $sorting == 'ordering-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ORDERING_DESC'); ?></option>
            <option value="title-asc" <?php echo $sorting == 'title-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ALPHA_ASC'); ?></option>
            <option value="title-desc" <?php echo $sorting == 'title-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ALPHA_DESC'); ?></option>
            <option value="price-asc" <?php echo $sorting == 'price-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_PRICE_AESC'); ?></option>
            <option value="price-desc" <?php echo $sorting == 'price-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_PRICE_DESC'); ?></option>
            <option value="psize-asc" <?php echo $sorting == 'psize-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_PSIZE_ASC'); ?></option>
            <option value="psize-desc" <?php echo $sorting == 'psize-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_PSIZE_DESC'); ?></option>
            <option value="created-desc" <?php echo $sorting == 'created-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_RECENT_ASC'); ?></option>
            <option value="created-asc" <?php echo $sorting == 'created-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_RECENT_DESC'); ?></option>
        </select>
      </div>
    </div>
</div>
<?php } ?>
<div id="sp-property-properties" class="spproperty-view-properties spproperty">
  
    <?php if (!$searchitem && !$this->catid) { // if isn't search ?>
        <?php echo LayoutHelper::render('properties.properties_sort', array('property_types' => $this->property_types, 'property_total' => $this->property_total, 'catid' => $catid, 'all_properties_url' => $this->all_properties_url)); ?>
    <?php } ?>

    <?php echo LayoutHelper::render('properties.properties', array('properties' => $this->items, 'columns' => $this->columns)); ?>

    <?php if ($this->pagination->pagesTotal > 1 && ($this->hide_pagination == 0)) { ?>
      <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
      </div>
    <?php } ?>

</div>

