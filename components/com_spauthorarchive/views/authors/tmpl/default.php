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
<div id="spauthorarchive" class="spauthorarchive sp-autho-archive-view-authors layout-<?php echo $this->layout_type; ?>">
    <?php echo LayoutHelper::render('authors.authors', array('authors' => $this->items, 'columns' => $this->columns, 'show_desc' => $this->show_desc, 'intro_limit' => $this->intro_txt_limit)); ?>
</div> <!-- /#spauthorarchive -->


<?php if ($this->pagination->pagesTotal > 1) { ?>
    <div class="pagination pagination-wrapper">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php } ?>

