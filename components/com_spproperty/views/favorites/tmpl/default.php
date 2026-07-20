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
use Joomla\CMS\Layout\LayoutHelper;

$input         = Factory::getApplication()->input;
$catid         = $input->get('catid', null, 'INT');
$searchitem  = $input->get('searchitem', null, 'INT');
$sorting     = $input->get('sorting', 'ordering-desc', 'STRING');

?>

<div id="sp-property-properties" class="spproperty-view-properties spproperty">

    <?php echo LayoutHelper::render('properties.properties', array('properties' => $this->items, 'columns' => $this->columns)); ?>

    <?php if ($this->pagination->pagesTotal > 1) { ?>
      <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
      </div>
    <?php } ?>

</div>

