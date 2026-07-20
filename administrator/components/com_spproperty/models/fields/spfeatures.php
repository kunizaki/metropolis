<?php

/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;


class JFormFieldSpfeatures extends ListField
{
      protected $type   = 'Spfeatures';
      protected $layout = 'joomla.form.field.list-fancy-select';
      
      protected function getOptions()
      {
            $pfeatures = SppropertyHelper::getPropertyFeatures();
            $options   = [HTMLHelper::_('select.option', '-1', '--------', ['disable' => true, 'multiple' => true])];

            foreach ($pfeatures as $key => $feature) 
            {
                  $languageName = LanguageHelper::branchLanguageTitle($key);
                  $options[]    = HTMLHelper::_('select.option', '<OPTGROUP>', $languageName, ['multiple' => true]);

                  foreach ($feature as $featureInfo) {
                        $options[] = HTMLHelper::_('select.option', $featureInfo->id, $featureInfo->title);
                  }

                  $options[] = HTMLHelper::_('select.option', '</OPTGROUP>');
            }

            return $options;
      }
}
