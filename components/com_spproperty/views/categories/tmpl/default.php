<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

?>

<div id="spproperty" class="spproperty spproperty-view-categories spproperty-categories">
    <div class="spproperty-row">
        <?php foreach ($this->items as $key => $item) { ?>
            <div class="spproperty-category spproperty-col-sm-4">
                <?php if ($item->icon_image == 1) { ?>
                    <div class="spproperty-card category-image" style="background-image: url('<?php echo $item->image; ?>')">
                <?php } else { ?>
                    <div class="spproperty-card">
                    <span class="<?php echo $item->icon; ?>"></span>
                <?php } ?>
                <a href="<?php echo $item->url; ?>" class="category-link"></a>
                <p class="title"><?php echo $item->title; ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="spproperty-row">
        <div class="spproperty-col-sm-12" style="text-align: center;">
            <?php if ($this->pagination->pagesTotal > 1) { ?>
                <div class="pagination">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>