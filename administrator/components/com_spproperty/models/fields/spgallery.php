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

class JFormFieldSpgallery extends FormField
{
    protected $type = 'Spgallery';

    protected function getInput()
    {
        $doc        = Factory::getDocument();
        $input      = Factory::getApplication()->input;

        $component  = $input->get('option');
        $field_name = (string)($this->element['name']);

        //check is the value is non-empty and string and json-string
        $value = !empty($this->value) && is_string($this->value) && is_array(json_decode($this->value, true)) ? $this->value : '';

        $doc->addScriptDeclaration("var host = '" . Uri::root() . "administrator/index.php?option=" . $component . "';");
        $doc->addScriptDeclaration("var dropAreaId = 'spgallery-drop-area-" . $this->id . "';");
        $doc->addScriptDeclaration("var field_name = '" . $field_name . "';");
        $doc->addScriptDeclaration("var field_id = '" . $this->id . "';");
        $doc->addScriptDeclaration("var imageLists = '" . $value . "';");

        $doc->addScript(Uri::root(true) . '/administrator/components/com_spproperty/assets/js/spgallery.js', [], ['defer' => true]);
        $doc->addStylesheet(Uri::root(true) . '/administrator/components/com_spproperty/assets/css/spgallery.css');


        $html = array();
        $html[] = "<div class='spgallery-drop-area' id='spgallery-drop-area-" . $this->id . "'>";
        $html[] = "<div class='spgallery-form'>";
        $html[] = "<img class='upload-image' src='" . Uri::root(true) . "/administrator/components/com_spproperty/assets/images/upload.png'>";
        $html[] = "<p>Drag and drop files or click the icon.</p>";
        $html[] = "<span class='underline'></span>";
        $html[] = "<input type='file' class='fileElem' id='fileElem_" . $this->id . "' multiple accept='image/*' onchange='handleFiles(this.files)'>";
        $html[] = "</div>";
        $html[] = "<div class='gallery'>";
        if (!empty($value)) {
            $html[] = $this->getValue($value);
        }
        $html[] = "</div>";
        $html[] = "<input type='hidden' name='" . $this->name . "' id='" . $this->id . "' value='" . $value . "'>";
        $html[] = "<button class='btn btn-primary btn-large upload-btn'>Upload</button>";
        $html[] = "</div>";

        return implode("\n", $html);
    }

    private function getValue($value)
    {
        $value = json_decode($value);
        $html = array();
        $index = 1;
        foreach ($value as $key => $v) {
            $html[] = '<div class="image-wrapper saved-src" data-index="' . ( (int) filter_var($key, FILTER_SANITIZE_NUMBER_INT) ) . '" data-src="' . base64_encode($v->photo) . '">';
            $html[] = '<img src="' . Uri::root() . $v->photo . '" data-photo="' . $v->photo . '" data-alt_text="' . $v->alt_text . '">';
            $html[] = '<progress class="inner-progress" min="0" max="100" value="0" style="display: none;"></progress>';
            $html[] = '<div class="remove-image-wrapper">';
            $html[] = '<div class="cross-holder">';
            $html[] = '<a class="remove-image spgallery-close" href="javascript:"></a>';
            $html[] = '</div>';
            $html[] = '</div>';
            $html[] = '</div>';
            $index++;
        }
        return implode("\n", $html);
    }
}
