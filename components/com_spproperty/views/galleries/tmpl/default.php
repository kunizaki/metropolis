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

<div id="sp-property-galleries" class="spproperty-view-galleries spproperty">
    <?php if (!empty($this->items)) { ?>
        <div class="spproperty-row clearfix">
            <?php foreach ($this->albums as $items) {  ?>
                <div class="spproperty-col-sm-<?php echo round(12 / $this->columns); ?>">
                    <?php foreach ($items as $item) { ?>
                        <a href="<?php echo $item->url; ?>" class="spproperty-gallery-item">
                            <div>
                                <img src="<?php echo $item->album; ?>" alt="<?php echo $item->album; ?>">
                                <div class="spproperty-gallery-album-info">
                                    <span class="spproperty-gallery-album-title"><?php echo $item->title; ?></span>
                                    <div class="spproperty-gallery-album-meta clearfix">
                                        <span class="spproperty-gallery-album-meta-count"><?php echo $item->count . ' ' . SppropertyHelper::pluralize($item->count, 'Photo', 'Photos'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    <?php } ?>

    <?php if ($this->pagination->pagesTotal > 1) { ?>
      <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
      </div>
    <?php } ?>
    
</div>