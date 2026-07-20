<?php

/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

?>

<div id="sp-property-gallery" class="spproperty-view-gallery spproperty">
    <?php if (!empty($this->item)) { ?>
    <div class="spproperty-gallery-title-wrap" style="background-image: url('<?php echo $this->item->image; ?>');">
        <p class="badge badge-primary"><?php echo $this->item->category_name; ?></p>
        <h4><?php echo $this->item->title; ?></h4>
    </div>
    <div class="container">
        <?php if (!empty($this->gallery)) { ?>
            <div class="spproperty-row clearfix">
                <?php foreach ($this->gallery as $items) {  ?>
                    <?php foreach ($items as $item) { ?>
                        <div class="spproperty-col-sm-<?php echo round(12 / $this->columns); ?>">
                            <a href="<?php echo Uri::root() . $item->image; ?>" class="spproperty-gallery-item item-cursor" data-title="<?php echo $this->item->title; ?>" data-desc="">
                                <div>
                                    <img src="<?php echo $item->thumb; ?>" alt="<?php echo $item->thumb; ?>">
                                </div>
                            </a>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        <?php } ?>
        <a class="sppb-btn sppb-btn-primary sppb-btn-sm back-btn" href="<?php echo $this->galleries_url; ?>"><i class="fa fa-backward"></i>
            <?php echo Text::_('COM_SPPRPPERTY_BACK_TO_GALLERY'); ?></a>
    </div>
    <?php } ?>
</div>