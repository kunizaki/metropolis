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
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$doc = Factory::getDocument();
$doc->addStyleDeclaration("
    #spproperty-map {
        height: " . $this->map_height . "px;
    }
");
$input      = Factory::getApplication()->input;
$sorting    = $input->get('sorting', 'ordering-desc', 'STRING');
$searchitem = $input->get('searchitem', null, 'INT');
$sp_fullmap_right = $doc->countModules('spproperty-fullmap-right');
?>

<div class="spproperty" id="spproperty-map-container">
    <div class="spproperty-row">
        <div class="spproperty-col-sm-12">
            <div id="spproperty-map"></div>
        </div>
        <!-- if item has 3 then module position will be set -->
        <?php if ($doc->countModules('spproperty-map-footer')) { ?>
            <div class="spproperty-container">
                <div class="spproperty-col-sm-12 spproperty-map-footer">
                     <?php
                         $modules = ModuleHelper::getModules('spproperty-map-footer');
                         $attribs['style'] = 'sp_xhtml';

                        foreach ($modules as $key => $module) {
                            echo ModuleHelper::renderModule($module, $attribs);
                        }
                        ?>
                 </div> <!-- /.col-sm- -->
            </div>
        <?php } ?> <!-- // END:: key condition -->
    </div>
    <div class="spproperty-container">
        <div class="spproperty-row">
            <div class="spproperty-col-lg-12">
                <div class="spproperty-row">
                    <!-- header -->
                    <div class="spproperty-col-sm-12 spproperty-col-lg-6">
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
                    <div class="spproperty-col-sm-12 spproperty-col-lg-6">
                        <!-- view style -->
                        <div class="pull-right view-button property-view">
                            <a href="javascript:" class="btn spproperty-list-view">
                                <span class="fa fa-list"></span>
                            </a>
                            <a href="javascript:" class="btn spproperty-grid-view active-view">
                                <span class="fa fa-th"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="spproperty-row">
                    <div class="map-content-loader" style="display: none;">
                        <img src="<?php echo Uri::root() . '/components/com_spproperty/assets/css/ajax-loader.gif'; ?>" >        
                    </div>
                    <div class="spproperty-col-sm-<?php echo $sp_fullmap_right ? '8' : '12'; ?>">
                        <div id="sp-property-properties-map" class="spproperty-view-properties-map spproperty-map"></div>
                    </div>
                    <?php if ($doc->countModules('spproperty-fullmap-right')) { ?>
                    <div class="spproperty-col-sm-4 spproperty-fullmap-right">
                            <?php
                                jimport('joomla.application.module.helper');
                                $modules = ModuleHelper::getModules('spproperty-fullmap-right');
                                $attribs['style'] = 'sp_xhtml';

                            foreach ($modules as $key => $module) {
                                echo ModuleHelper::renderModule($module, $attribs);
                            }
                            ?>
                        </div> <!-- /.col-sm- -->
                    <?php } ?> <!-- // END:: key condition -->
                </div>
            </div>
        </div>
    </div>
</div>