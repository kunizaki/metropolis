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
use Joomla\CMS\Language\Text;

$doc = Factory::getDocument();
$doc->addStyleDeclaration("
    #spproperty-map {
        height: 400px;
    }
");
$input      = Factory::getApplication()->input;
$sorting    = $input->get('sorting', 'ordering-desc', 'STRING');
$searchitem = $input->get('searchitem', null, 'INT');
?>

<div class="spproperty" id="spproperty-map-container">
    <div class="row">
        <div class="col-lg-12">
            <div id="spproperty-map"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="row">
                <!-- header -->
                <div class="col-sm-12 col-lg-6">
                    <!-- filter -->
                    <?php if (!is_null($searchitem)) { ?>
                    <div class="sorting">
                        <div class="form-group">
                            <select name="sorting" id="sorting" class="form-control">
                                <option value="ordering-asc" <?php echo $sorting == 'ordering-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ORDERING_ASC'); ?></option>
                                <option value="ordering-desc" <?php echo $sorting == 'ordering-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ORDERING_DESC'); ?></option>
                                <option value="title-asc" <?php echo $sorting == 'title-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ALPHA_ASC'); ?></option>
                                <option value="title-desc" <?php echo $sorting == 'title-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_ALPHA_DESC'); ?></option>
                                <option value="created-desc" <?php echo $sorting == 'created-desc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_RECENT_ASC'); ?></option>
                                <option value="created-asc" <?php echo $sorting == 'created-asc' ? 'selected' : ''; ?> ><?php echo Text::_('COM_SPPROPERTY_SORTING_RECENT_DESC'); ?></option>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                </div>
                <div class="col-sm-12 col-lg-6">
                    <!-- view style -->
                    <div class="pull-right">
                        <a href="javascript:" class="btn spproperty-list-view">
                            <span class="fa fa-list"></span>
                        </a>
                        <a href="javascript:" class="btn btn-info spproperty-grid-view">
                            <span class="fa fa-th"></span>
                        </a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div id="sp-property-properties-map" class="spproperty-view-properties-map spproperty-map"></div>
            </div>
        </div>
    </div>
</div>