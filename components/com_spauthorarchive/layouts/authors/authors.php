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

$items 	= $displayData['authors'];
$columns 		= $displayData['columns'];
$show_desc 		= $displayData['show_desc'];

if (count((array)$items)) { ?>

    <div class="spauthorarchive-row">
		<?php foreach ($items as $author) { ?>
		<div class="spauthorarchive-departments spauthorarchive-col-md-12 spauthorarchive-col-lg-<?php echo round(12/$columns); ?>">
            <div class="spauthorarchive-department-wrap">

				<div class="spauthorarchive-media-wrap">
					<?php if(isset($author->image) && $author->image) { ?>
						<div class="spauthorarchive-department-img-wrap">
							<img src="<?php echo $author->image ?>" />
						</div>
					<?php } ?>
					<div class="spauthorarchive-department-content">
						<?php if ( !empty($author->socials) && count($author->socials) ) { ?>
							<ul class="spauthorarchive-author-socials">
								<?php foreach ($author->socials as  $social) { ?>
								<li class="<?php echo $social['social_name']; ?>">
									<a href="<?php echo $social['social_url']; ?>" target="_blank">
										<i class="fa fa-<?php echo $social['social_name']; ?>"></i>
									</a>
								</li>
								<?php } ?>
							</ul>
						<?php } ?>
						
						<h3 class="spauthorarchive-department-title">
							<a href="<?php echo $author->url; ?>"><?php echo $author->name; ?></a>
						</h3>

						<?php if( isset($author->profile_data['designation']) && $designation = $author->profile_data['designation'] ) { ?>
							<p><?php echo $designation; ?></p>
						<?php } ?>

						<?php if( $show_desc && isset($author->profile_data['description']) && $description = $author->profile_data['description'] ) { ?>
							<?php if (strlen($description) > 218) { ?>
								<p><?php echo substr($description, 0, 218) . '...'; ?></p>
							<?php } else { ?>
								<p><?php echo $description; ?></p>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
			</div>
		</div> <!-- /.spauthorarchive-departments -->
		<?php } ?>
	</div> <!-- /.spauthorarchive-row -->

<?php } else { ?>
    <div class="row">
        <div class="spauthorarchive-col-sm-12 sp-no-item-found">
            <p><?php echo Text::_('COM_SPAUTHORARCHIVE_NO_ITEMS_FOUND'); ?></p>
        </div>
    </div>
<?php } ?>

