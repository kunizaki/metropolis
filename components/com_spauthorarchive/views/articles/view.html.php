<?php
/**
* @package com_spauthorarchive
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2021 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;

class SpauthorarchiveViewArticles extends HtmlView
{

	protected $items;
	protected $params;
	protected $layout_type;

	function display($tpl = null) {
		// Assign data to the view
		$model = $this->getModel();
		$this->items = $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		$app = Factory::getApplication();
		$this->params 		    = $app->getParams();
		$this->columns		    = $this->params->get('art_columns', 4);
		$this->show_thumbnail 	= $this->params->get('art_show_thumbnail', 0);
		$this->show_intro 		= $this->params->get('art_show_intro', 1);
		$this->intro_limit 		= $this->params->get('intro_txt_limit', 150);
		$this->readmore_text 	= $this->params->get('art_readmore_text', Text::_('COM_SPAUTHORARCHIVE_ARTICLE_READ_MORE'));
		$menus                  = Factory::getApplication()->getMenu();
		$menu                   = $menus->getActive();

		if($menu) {
			$this->params->merge($menu->getParams());
		}
		
		$this->layout_type = str_replace('_', '-', $this->params->get('layout_type', 'default'));
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			Log::add(implode('<br />', $errors), Log::WARNING, 'jerror');
			return false;
		}
		
		//Get author infos
		$app = Factory::getApplication('site');
		$author_id = $app->input->getInt('uid');
		
		// if not get author id
		if(!$author_id)
		{
			throw new \Exception(Text::_('COM_SPAUTHORARCHIVE_NO_AUTHOR_ID'), 404);	
		}

		// Load Authors
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_spauthorarchive/models');
		$authors_model = BaseDatabaseModel::getInstance( 'Authors', 'SpauthorarchiveModel' );

		//*** get author info ***//
		$user_info 						= Factory::getUser($author_id);
		$this->author_infos 			= (object) $authors_model->getUserProfileData($author_id);
		$this->author_infos->name 		= $user_info->name;
		$this->author_infos->username 	= $user_info->username;
		$this->author_infos->email 		= $user_info->email;
		$this->author_infos->image 		= (isset($this->author_infos->avatar['avatar']) && $avatar = $this->author_infos->avatar['avatar']) ? Uri::root(true) . $avatar : 'http://www.gravatar.com/avatar/' . md5(strtolower(trim($this->author_infos->email))) . '?s=180';


		foreach ($this->items as &$this->item) {
			$this->item->slug    	= $this->item->id . ':' . $this->item->alias;
			$this->item->catslug 	= $this->item->catid . ':' . $this->item->category_alias;
			$this->item->username   = Factory::getUser($this->item->created_by)->name;
			$this->item->link 	    = Route::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));
			$attribs 		        = json_decode($this->item->attribs);

			$feature_img = '';
			if (isset($attribs->helix_ultimate_image) && $attribs->helix_ultimate_image) {
				$feature_img = $attribs->helix_ultimate_image;
			} elseif (isset($attribs->spfeatured_image) && $attribs->spfeatured_image) {
				$feature_img = $attribs->spfeatured_image;
			}

			// Featured Image
			if(isset($feature_img) && $feature_img != NULL) {
				$this->item->featured_image = $featured_image = $feature_img;
				$img_baseurl = basename($featured_image);

				//Small
				$small = JPATH_ROOT . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) .  '_small.' . File::getExt($img_baseurl);
				if(file_exists($small)) {
					$this->item->image_small = Uri::root(true) . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) . '_small.' . File::getExt($img_baseurl);
				}

				//Thumb
				$thumbnail = JPATH_ROOT . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) .  '_thumbnail.' . File::getExt($img_baseurl);
				if(file_exists($thumbnail)) {
					$this->item->image_thumbnail = Uri::root(true) . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) . '_thumbnail.' . File::getExt($img_baseurl);
				} else {
					$this->item->image_thumbnail = Uri::root(true) . '/' . $this->item->featured_image;
				}

				//Medium
				$medium = JPATH_ROOT . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) .  '_medium.' . File::getExt($img_baseurl);
				if(file_exists($medium)) {
					$this->item->image_medium = Uri::root(true) . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) . '_medium.' . File::getExt($img_baseurl);
				}

				//Large
				$large = JPATH_ROOT . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) .  '_large.' . File::getExt($img_baseurl);
				if(file_exists($large)) {
					$this->item->image_large = Uri::root(true) . '/' . dirname($featured_image) . '/' . File::stripExt($img_baseurl) . '_large.' . File::getExt($img_baseurl);
				}
			} else {
				$images = json_decode($this->item->images);
				if(isset($images->image_intro) && $images->image_intro) {
					$this->item->image_thumbnail = $images->image_intro;
				} elseif (isset($images->image_fulltext) && $images->image_fulltext) {
					$this->item->image_thumbnail = $images->image_fulltext;
				} else {
					$this->item->image_thumbnail = false;
				}
			}

			// Post Format
			$this->item->post_format = 'standard';
			if(isset($attribs->helix_ultimate_article_format) && $attribs->helix_ultimate_article_format != '') {
				$this->item->post_format = $attribs->helix_ultimate_article_format;
			} elseif(isset($attribs->post_format) && $attribs->post_format != '') {
				$this->item->post_format = $attribs->post_format;
			}

			// Post Format Video
			if(isset($this->item->post_format) && $this->item->post_format == 'video') {
				
				$video_url = '';
				if (isset($attribs->helix_ultimate_video) && $attribs->helix_ultimate_video) {
					$video_url = $attribs->helix_ultimate_video;
				} elseif (isset($attribs->video) && $attribs->video) {
					$video_url = $attribs->video;
				}

				if(isset($video_url) && $video_url != NULL) {
					$video = parse_url($video_url);
					$video_src = '';
					switch($video['host']) {
						case 'youtu.be':
						$video_id 	= trim($video['path'],'/');
						$video_src 	= '//www.youtube.com/embed/' . $video_id;
						break;

						case 'www.youtube.com':
						case 'youtube.com':
						parse_str($video['query'], $query);
						$video_id 	= $query['v'];
						$video_src 	= '//www.youtube.com/embed/' . $video_id;
						break;

						case 'vimeo.com':
						case 'www.vimeo.com':
						$video_id 	= trim($video['path'],'/');
						$video_src 	= "//player.vimeo.com/video/" . $video_id;
					}

					$this->item->video_src = $video_src;
				} else {
					$this->item->video_src = '';
				}
			}

			// Post Format Audio
			if(isset($this->item->post_format) && $this->item->post_format == 'audio') {

				$audio_url = '';
				if (isset($attribs->helix_ultimate_audio) && $attribs->helix_ultimate_audio) {
					$audio_url = $attribs->helix_ultimate_audio;
				} elseif (isset($attribs->audio) && $attribs->audio) {
					$audio_url = $attribs->audio;
				}

				if(isset($audio_url) && $audio_url != NULL) {
					$this->item->audio_embed = $audio_url;
				} else {
					$this->item->audio_embed = '';
				}
			}

			// Post Format Quote
			if(isset($this->item->post_format) && $this->item->post_format == 'quote') {
				if(isset($attribs->quote_text) && $attribs->quote_text != NULL) {
					$this->item->quote_text = $attribs->quote_text;
				} else {
					$this->item->quote_text = '';
				}

				if(isset($attribs->quote_author) && $attribs->quote_author != NULL) {
					$this->item->quote_author = $attribs->quote_author;
				} else {
					$this->item->quote_author = '';
				}
			}

			// Post Format Status
			if(isset($this->item->post_format) && $this->item->post_format == 'status') {
				if(isset($attribs->post_status) && $attribs->post_status != NULL) {
					$this->item->post_status = $attribs->post_status;
				} else {
					$this->item->post_status = '';
				}
			}

			// Post Format Link
			if(isset($this->item->post_format) && $this->item->post_format == 'link') {
				if(isset($attribs->link_title) && $attribs->link_title != NULL) {
					$this->item->link_title = $attribs->link_title;
				} else {
					$this->item->link_title = '';
				}

				if(isset($attribs->link_url) && $attribs->link_url != NULL) {
					$this->item->link_url = $attribs->link_url;
				} else {
					$this->item->link_url = '';
				}
			}

			// Post Format Gallery
			if(isset($this->item->post_format) && $this->item->post_format == 'gallery') {

				$gallery_imgs = '';
				if (isset($attribs->helix_ultimate_gallery) && $attribs->helix_ultimate_gallery) {
					$gallery_imgs = $attribs->helix_ultimate_gallery;
				} elseif (isset($attribs->gallery) && $attribs->gallery) {
					$gallery_imgs = $attribs->gallery;
				}

				$this->item->imagegallery = new stdClass();
				$gallery_imgs;

				if(isset($gallery_imgs) && $gallery_imgs != NULL) {
					$gallery_img_decode = json_decode($gallery_imgs);
					$gallery_all_images = '';
					if (isset($gallery_img_decode->helix_ultimate_gallery_images) && $gallery_img_decode->helix_ultimate_gallery_images) {
						$gallery_all_images = $gallery_img_decode->helix_ultimate_gallery_images;
					} elseif (isset($gallery_img_decode->gallery_images) && $gallery_img_decode->gallery_images) {
						$gallery_all_images = $gallery_img_decode->gallery_images;
					}
					
					$gallery_images = array();
					foreach ($gallery_all_images as $key=>$value) {
						$gallery_images[$key]['full'] = $value;
						$gallery_img_baseurl = basename($value);

						//Small
						$small = JPATH_ROOT . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) .  '_small.' . File::getExt($gallery_img_baseurl);
						if(file_exists($small)) {
							$gallery_images[$key]['small'] = Uri::root(true) . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) . '_small.' . File::getExt($gallery_img_baseurl);
						}

						//Thumbnail
						$thumbnail = JPATH_ROOT . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) .  '_thumbnail.' . File::getExt($gallery_img_baseurl);
						if(file_exists($thumbnail)) {
							$gallery_images[$key]['thumbnail'] = Uri::root(true) . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) . '_thumbnail.' . File::getExt($gallery_img_baseurl);
						}

						//Medium
						$medium = JPATH_ROOT . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) .  '_medium.' . File::getExt($gallery_img_baseurl);
						if(file_exists($medium)) {
							$gallery_images[$key]['medium'] = Uri::root(true) . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) . '_medium.' . File::getExt($gallery_img_baseurl);
						}

						//Large
						$large = JPATH_ROOT . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) .  '_large.' . File::getExt($gallery_img_baseurl);
						if(file_exists($large)) {
							$gallery_images[$key]['large'] = Uri::root(true) . '/' . dirname($value) . '/' . File::stripExt($gallery_img_baseurl) . '_large.' . File::getExt($gallery_img_baseurl);
						}
					}

					$this->item->imagegallery->images = $gallery_images;
				} else {
					$this->item->imagegallery->images = array();
				}
			}
			
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	protected function _prepareDocument() {
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		$menu = $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', Text::_('COM_SPTODO_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title)) {
			$title = $app->get('sitename');
		} elseif ($app->get('sitename_pagetitles', 0) == 1) {
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		} elseif ($app->get('sitename_pagetitles', 0) == 2) {
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description')) {
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords')) {
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots')) {
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
