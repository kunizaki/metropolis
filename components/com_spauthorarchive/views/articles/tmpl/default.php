<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

?>

<div id="spauthorarchive" class="spauthorarchive spauthorarchive-view-articles layout-<?php echo $this->layout_type; ?>">

    <div class="spauthorarchive-row">
		<div class="spauthorarchive-articles spauthorarchive-col-md-12 spauthorarchive-col-lg-12>">
            <div class="spauthorarchive-author-wrap">
				<?php if(isset($this->author_infos->image) && $this->author_infos->image) { ?>
					<div class="spauthorarchive-author-img-wrap">
						<img src="<?php echo $this->author_infos->image ?>" />
					</div>
				<?php } ?>
				<div class="spauthorarchive-author-content">
                    <?php if ( !empty($this->author_infos->socials) && count($this->author_infos->socials) ) { ?>
						<ul class="spauthorarchive-author-socials">
							<?php foreach ($this->author_infos->socials as  $social) { ?>
							<li class="<?php echo $social['social_name']; ?>">
								<a href="<?php echo $social['social_url']; ?>" target="_blank">
									<i class="fa fa-<?php echo $social['social_name']; ?>"></i>
								</a>
							</li>
							<?php } ?>
						</ul>
                    <?php } ?>
					<h3 class="spauthorarchive-author-title"><?php echo $this->author_infos->name; ?></h3>
					<?php if( isset($this->author_infos->designation) && $designation = $this->author_infos->designation ) { ?>
						<p><?php echo $designation; ?></p>
					<?php } ?>

					<?php if( isset($this->author_infos->description) && $description = $this->author_infos->description ) { ?>
						<p><?php echo $description; ?></p>
					<?php } ?>
				</div> <!-- /.spauthorarchive-author-content -->
			</div> <!-- /.spauthorarchive-author-wrap -->
			
			<div class="spauthorarchive-info-wrap">
				<?php if( isset($this->author_infos->education) && $education = $this->author_infos->education) { ?>
					<p>
						<span><?php echo Text::_('COM_SPAUTHORARCHIVE_AUTHOR_EDUCATION'); ?></span>
						<?php echo $education; ?>
					</p>
				<?php } ?>

				<?php if( isset($this->author_infos->experience) && $experience = $this->author_infos->experience ) { ?>
					<p>
						<span><?php echo Text::_('COM_SPAUTHORARCHIVE_AUTHOR_EXPERIENCE'); ?></span>
						<?php echo $experience; ?>
					</p>
				<?php } ?>

				<?php if( isset($this->author_infos->citizenship) && $citizenship = $this->author_infos->citizenship ) { ?>
					<p>
						<span><?php echo Text::_('COM_SPAUTHORARCHIVE_AUTHOR_CITIZENSHIP'); ?></span>
						<?php echo $citizenship; ?>
					</p>
				<?php } ?>

				<?php if( isset($this->author_infos->age) && $age = $this->author_infos->age ) { ?>
					<p>
						<span><?php echo Text::_('COM_SPAUTHORARCHIVE_AUTHOR_AGE'); ?></span>
						<?php echo $age; ?>
					</p>
				<?php } ?>
			</div>
		</div> <!-- /.spauthorarchive-articles -->
	</div> <!-- /.spauthorarchive-row -->

    <div class="spauthorarchive-content">
        <div class="spauthorarchive-row">
            <?php foreach ($this->items as $key => $item) {
            ?>
                <?php echo LayoutHelper::render('articles.articles', array('item' => $item, 'columns' => $this->columns, 'show_thumbnail' => $this->show_thumbnail, 'show_intro' => $this->show_intro, 'intro_limit' => $this->intro_limit, 'readmore_text' => $this->readmore_text)); ?>
            <?php } // END:: foreach ?>

        </div> <!-- /.spauthorarchive-row -->
    </div> <!-- /.spauthorarchive-content -->

</div>


<?php if ($this->pagination->pagesTotal > 1) { ?>
    <div class="pagination pagination-wrapper">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
<?php } ?>


