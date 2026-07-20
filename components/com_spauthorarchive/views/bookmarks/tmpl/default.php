<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Layout\LayoutHelper;

?>
<div id="spauthorarchive" class="spauthorarchive sp-autho-archive-view-bookmarks layout-<?php echo $this->layout_type; ?>">
    <div class="spauthorarchive-content">
        <div class="spauthorarchive-row">
            <?php foreach ($this->items as $key => $item) {
            ?>
                <?php echo LayoutHelper::render('articles.articles', array('item' => $item, 'params' => $this->params, 'columns' => $this->columns, 'show_thumbnail' => $this->show_thumbnail, 'show_intro' => $this->show_intro, 'intro_limit' => $this->intro_limit, 'readmore_text' => $this->readmore_text)); ?>
            <?php } // END:: foreach ?>

            <?php //if ($this->pagination->get('pages.total') >1) { ?>
                <div class="pagination">
                    <?php //echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php //} ?>

        </div> <!-- /.spauthorarchive-row -->
    </div> <!-- /.spauthorarchive-content -->
</div> <!-- /#spauthorarchive -->

<?php if ($this->pagination->pagesTotal > 1) { ?>
    <div class="pagination pagination-wrapper">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php } ?>


