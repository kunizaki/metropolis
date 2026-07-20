<?php

/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Layout\LayoutHelper;

?>

<div id="spproperty" class="spproperty spproperty-view-agents spproperty-agents">

    <?php echo LayoutHelper::render('agents.agents', array('agents' => $this->items, 'columns' => $this->columns)); ?>

    <?php if ($this->pagination->pagesTotal > 1) { ?>
      <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
      </div>
    <?php } ?>

</div> <!-- /.spproperty-view-agents -->












