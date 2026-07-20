<?php
/**
 * @package com_spauthorarchive
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2019 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

$item 				= $displayData['item'];
$params 			= isset($displayData['params']) && $displayData['params'] ? $displayData['params'] : array();
$columns 			= $displayData['columns'];
$show_thumbnail 	= $displayData['show_thumbnail'];
$show_intro 		= $displayData['show_intro'];
$intro_limit 		= $displayData['intro_limit'];
$readmore_text 		= $displayData['readmore_text'];

//
$show_author = 1;
$show_category = 1;
$show_date = 1;

?>

<div class="spauthorarchive-col-sm-<?php echo round(12/$columns) ?>">
	<div class="blog">
		<div class="items-row">
			<div class="item">
				<div class="article-body">
				<?php if($show_author || $show_category || $show_date) { ?>
					<div class="spauthorarchive-article-meta">
						<?php if($show_category) { ?>
						<?php $item->catUrl = Route::_(ContentHelperRoute::getCategoryRoute($item->catslug)); ?>
						<span class="spauthorarchive-meta-category"><a href="<?php echo $item->catUrl ?>"
							itemprop="genre"><?php echo $item->category; ?></a></span>
						<?php } ?>
					</div>
					<?php } ?>

					<div class="article-header">
						<h2><a href="<?php echo $item->link; ?>" itemprop="url"> <?php echo $item->title ; ?></a></h2>
					</div>
					<div class="newsberg-article-content">
						<div class="newsberg-article-introtext">
							<?php if($show_intro) { 
							$item->introtext = (isset($item->introtext) && $item->introtext) ? $item->introtext : $item->fulltext ; ?>
							<?php if (strlen($item->introtext) > $intro_limit) { ?>
							<?php echo substr($item->introtext, 0, $intro_limit) .'...'; ?>
							<?php } else { ?>
							<?php echo $item->introtext; ?>
							<?php } ?>
							<?php } ?>
							<div class="newsberg-article-introtext-bottom">
								<div class="article-info">
									<?php if($show_date) { ?>
									<span class="published"
										itemprop="datePublished"><?php echo HTMLHelper::_('date', $item->publish_up, 'DATE_FORMAT_LC4'); ?></span>
									<?php } ?>
								</div>
								<!-- if spauthorarchive component is enabled -->
								<?php if (ComponentHelper::getComponent('com_spauthorarchive', true)->enabled) { ?>
								<?php echo LayoutHelper::render('joomla.content.spbookmark', array('item' => $item, 'params' => $params)); ?>
								<?php } ?>

							</div>
						</div>
						<div class="newsberg-article-image">
							<?php if($show_thumbnail) {
							$image = '';
							$image = $item->image_thumbnail;
							?>
							<?php if( $item->post_format == 'video' && isset($item->video_src) && $item->video_src ) { ?>
							<div class="entry-video embed-responsive embed-responsive-16by9">
								<object class="embed-responsive-item" style="width:100%;height:100%;"
									data="<?php echo $item->video_src; ?>">
									<param name="movie" value="<?php echo $item->video_src ?>">
									<param name="wmode" value="transparent" />
									<param name="allowFullScreen" value="true">
									<param name="allowScriptAccess" value="always">
									</param>
									<embed src="<?php echo $item->video_src ?>" type="application/x-shockwave-flash"
										allowscriptaccess="always"></embed>
								</object>
							</div>
							<?php } elseif( $item->post_format == 'audio' && isset($item->audio_embed) && $item->audio_embed) { ?>
							<div class="entry-audio embed-responsive embed-responsive-16by9">
								<?php echo $item->audio_embed; ?>
							</div>
							<?php } elseif( $item->post_format == 'link' && isset($item->link_url) && $item->link_url) { ?>
							<div class="entry-link">
								<a target="_blank" href="<?php echo $item->link_url; ?>">
									<h4><?php echo $item->link_title; ?></h4>
								</a>
							</div>
							<?php } else { ?>
							<?php if(isset($image) && $image) { ?>
							<a href="<?php echo $item->link; ?>" itemprop="url"><img
									class="spauthorarchive-img-responsive" src="<?php echo $image; ?>"
									alt="<?php echo $item->title; ?>" itemprop="thumbnailUrl"></a>
							<?php } ?>
							<?php } ?>
							<?php } ?>
						</div>
					</div>
					<div class="read-more">
						<a class="spauthorarchive-readmore" href="<?php echo $item->link; ?>"
							itemprop="url"><?php echo $readmore_text; ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>