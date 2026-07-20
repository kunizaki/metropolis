<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Filesystem\Folder;

class SppropertyControllerSpgallery extends AdminController
{
    /**
     * Upload images
     */
    public function uploadFiles()
    {
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');
        $app    = Factory::getApplication();
        $input  = $app->input;

        $file   = $input->files->get('gallery_file');
        $filename = File::makeSafe($file['name']);
        $gallery_folder = $input->get('gallery_folder');
        $folder_name = $input->get('folder_name');
        $src = $file['tmp_name'];
        $dest = ($gallery_folder) ? JPATH_ROOT . '/images/gallery/' . $folder_name : JPATH_ROOT . '/images/gallery';

        $output = array();

        if (!is_dir($dest)) {
            Folder::create($dest);
        }
        $image_dest = $dest . '/' . $filename;

        if (File::upload($src, $image_dest)) {
            $photo_path = ($gallery_folder) ? 'images/gallery/' . $folder_name . '/' : 'images/gallery/';
            $output['photo'] = $photo_path . $filename;
            $output['alt_text'] = $filename;
            print_r(json_encode($output));
            die();
        }
        die('error');
    }

    /**
     * remove gallery images
     * uploaded newly
     */
    public function removeFile()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        $file = $input->files->get('removable_file');
        $filename = File::makeSafe($file['name']);

        $gallery_path = JPATH_ROOT . '/images/gallery';
        $image = $gallery_path . '/' . $filename;

        $gallery_folder    = $input->get('gallery_folder');
        $folder_name       = $input->get('folder_name');
        $image_with_folder = ($gallery_folder) ?? $gallery_path . '/' . $folder_name . '/' . $filename;

        if (file_exists($image)) {
            File::delete($image);
            echo "1";
            die();
        } elseif ($image_with_folder && file_exists($image_with_folder)) {
            File::delete($image_with_folder);
            echo "1";
            die();
        } else {
            echo "-1";
            die();
        }
        echo "0";
        die('Something went wrong!');
    }

    /**
     * removing saved files
     */

    public function removeSavedFile()
    {
        $app = Factory::getApplication();
        $input = $app->input;

        $src = $input->get('image_src');
        if (!empty($src)) {
            $src = base64_decode($src);
            $image_path = JPATH_ROOT . '/' . $src;
            if (file_exists($image_path)) {
                File::delete($image_path);
                echo "1";
                die();
            } else {
                echo "-1";
                die();
            }
        }
        echo "0";
        die("Something went wrong!");
    }
}
