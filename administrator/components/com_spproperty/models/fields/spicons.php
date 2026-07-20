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
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Form\FormField;

class JFormFieldSpicons extends FormField
{
    protected $type = 'Spicons';

    protected function getInput()
    {
        $doc = Factory::getDocument();
        $doc->addScript(Uri::root(true) . '/administrator/components/com_spproperty/assets/js/spicons.js');
        $doc->addStyleSheet(Uri::root(true) . '/administrator/components/com_spproperty/assets/css/spicons.min.css');
        $doc->addStyleSheet(Uri::root(true) . '/administrator/components/com_spproperty/assets/css/icomoon.css');

        $doc->addStyleDeclaration("
            .spicons .spicons-body .font-wrapper .font-container .font-cell {
                padding-top: 5px!important;
            }
        ");

        $html = "<div class='icon_sp' id='" . $this->id . "'></div>";
        $value = !empty($this->value) ? $this->value : '';

        if(file_exists(JPATH_ADMINISTRATOR . '/components/com_spproperty/assets/js/icomoon.json')){
            $icomoon = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_spproperty/assets/js/icomoon.json');
        }

        $doc->addScriptDeclaration("
            jQuery(function($){
                const icomoon = $icomoon;
                $('.icon_sp').spIcons({
                    icon_name: 'icomoon',
                    icon_view_style: 'grid',
                    select_placeholder: 'Select icon...',
                    search_placeholder: 'Search',
                    input_name: '" . $this->name . "',
                    allow_icon_prefix: false,
                    container_width: '400px',
                    selected_icon: '" . $value . "',
                    styles: {
                        icon_color:'#000',
                        icon_font_size: '16px'
                    },
                    icons: JSON.parse(icomoon)
                });
            });
        ");

        return $html;
    }
}
