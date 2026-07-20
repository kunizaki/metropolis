<?php
/**
* @title		SP Image Gallery
* @website		http://www.joomshaper.com
* @copyright	Copyright (C) 2010 - 2014 JoomShaper.com. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
*/
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

abstract class modSPGalleryHelper {

	static function getimgList($params) {
		$filter 		= '\.png$|\.gif$|\.jpg$|\.jpeg$|\.bmp$';
		$path			= $params->get('path');
		$thumbratio		= $params->get('thumbratio', 1) ? true : false;
		$thumbwidth		= trim($params->get('thumbwidth', 100));
		$thumbheight	= trim($params->get('thumbheight', 100));		
		$files 			= JFolder::files(JPATH_BASE.$path,$filter);
		
		$i=0;
		$lists = array();
		
		
		foreach ($files as $file) {
			$image				= modSPGalleryHelper::getImages($path.'/'.$file,$thumbwidth,$thumbheight,$thumbratio);
			$lists[$i]['title']	= JFile::stripExt($file);
			$lists[$i]['image'] = $image->image;
			$lists[$i]['thumb'] = $image->thumb;
			$i++;
		}
		return $lists; 
	}
	
	static function getImages($image, $thumbwidth=100, $thumbheight=100, $thumbratio="") {	  
		$images = new stdClass();
		$images->image = false;
		$images->thumb = false;

		$paths = array();
		if (isset($image)) {
			$image_path = $image;
			
			// remove any / that begins the path
			if (substr($image_path, 0 , 1) == '/') $image_path = substr($image_path, 1);
			
			// create a thumb filename
			$file_div 	= strrpos($image_path,'.');
			$thumb_ext	= substr($image_path, $file_div);
			$thumb_div	= strrpos($image_path,'/');
			$thumb_paths = substr($image_path, 0, $thumb_div);
			$thumb_prev = substr($image_path, strlen($thumb_paths), $file_div);
			$thumb_path = $thumb_paths . '/thumbs' . $thumb_prev;
			
			if (!is_dir($thumb_paths . '/thumbs')) {
				mkdir($thumb_paths . '/thumbs');
			}
			
			// check to see if this file exists, if so we don't need to create it
			if (function_exists("gd_info")) {
				// file doens't exist, so create it and save it
				if (!class_exists("spThumbnail")) include_once('class.spThumbnail.php');
				
				//Check existing thumbnails dimensions
				if (file_exists($thumb_path)) {
					$size = GetImageSize( $thumb_path );
					$currentWidth=$size[0];
					$currentHeight=$size[1];
				}
				
				//Creating thumbnails		
                if (!file_exists($thumb_path) || $currentWidth!=$thumbwidth || $currentHeight!=$thumbheight ) {
					$thumb = new spThumbnail;
					$thumb->new_width = $thumbwidth;
					$thumb->new_height = $thumbheight;
					$thumb->image_to_resize = $image_path; // Full Path to the file
					$thumb->ratio = $thumbratio; // Keep Aspect Ratio?
					$thumb->save = $thumb_path;
					$process = $thumb->resize();
    			}
			}
			
			$images->image = $image_path;
			$images->thumb = $thumb_path;
		} 
		return $images;
	}		
}	