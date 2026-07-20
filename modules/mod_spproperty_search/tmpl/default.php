<?php

/**
* @package mod_spproperty_search
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.multiselect');
// For Feature Dropdown
HTMLHelper::_('formbehavior.chosen', '#p_features', null, array(
    'width' => '100%',
    'placeholder_text_multiple' => Text::_('MOD_SPPROPERTY_SEARCH_PROPERTY_FEATURES'),
    'display_selected_options' => true,
    'inherit_select_classes' => true
));

// For Size Range Dropdown
HTMLHelper::_('formbehavior.chosen', '#psize_range_dropdown', null, array(
    'width' => '100%',
    'placeholder_text_multiple' => Text::_('MOD_SPPROPERTY_SEARCH_SIZE_RANGE'),
    'display_selected_options' => true,
    'inherit_select_classes' => true,
));

// For Price Range Dropdown
HTMLHelper::_('formbehavior.chosen', '#price_range_dropdown', null, array(
    'width' => '100%',
    'placeholder_text_multiple' => Text::_('MOD_SPPROPERTY_SEARCH_PRICE_RANGE'),
    'display_selected_options' => true,
    'inherit_select_classes' => true,
));

// For Flat and Floor Dropdown
HTMLHelper::_('formbehavior.chosen', '#lvlftno_dropdown', null, array(
    'width' => '100%',
    'placeholder_text_multiple' => Text::_('MOD_SPPROPERTY_SEARCH_PRICE_RANGE'),
    'display_selected_options' => true,
    'inherit_select_classes' => true,
));

$input               = Factory::getApplication()->input;
$keyword             = $input->get('keyword', null, 'STRING');
$pstatus             = $input->get('pstatus', null, 'STRING');
$city_name           = $input->get('city', null, 'STRING');
$minsize             = $input->get('minsize', null, 'INT');
$maxsize             = $input->get('maxsize', null, 'INT');
$beds                = $input->get('beds', null, 'INT');
$baths               = $input->get('baths', null, 'INT');
$parking             = $input->get('parking', null, 'INT');
$zipcode             = $input->get('zipcode', null, 'INT');
$catid               = $input->get('catid', null, 'INT');
$min_price           = $input->get('min_price', null, 'INT');
$max_price           = $input->get('max_price', null, 'INT');
$p_features          = $input->get('p_features', null, 'STRING');

$levelFlatNoDropdown   = $input->get('lvlftno_dropdown', null, 'STRING');
$levelFlatNoInput      = $input->get('lvlftno_inputfield', null, 'STRING');
$propertySizeDropDown  = $input->get('psize_range_dropdown', null, 'STRING');
$propertyPriceDropdown = $input->get('price_range_dropdown', null, 'STRING');

if (!is_null($p_features)) {
    $p_features = explode('-', $p_features);
} else {
    $p_features = array();
}

Factory::getDocument()->getWebAssetManager()
    ->usePreset('choicesjs')
    ->useScript('webcomponent.field-fancy-select');


$numerics   = array(1, 2, 3, 4, 5);
$property_statuses = array(
  'rent'          => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_RENT'),
  'sell'          => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_SELL'),
  'in_hold'       => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_IN_HOLD'),
  'pending'       => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_IN_PENDING'),
  'sold'          => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_IN_SOLD'),
  'under_offer'   => Text::_('MOD_SPPROPERTY_SEARCH_STATUS_IN_UNDER_OFFER')
);
$itemid = $input->get('Itemid', null, 'STRING');
$view   = $params->get('view', 'properties');
?>

<div id="mod-sp-property-search<?php echo $module->id; ?>" class="sp-property-search <?php echo $params->get('moduleclass_sfx') ?>">
    <form class="spproperty-search">
        <div class="row">
            <div class="spproperty-search-basic">
                <?php if ($params->get('show_keyword', 1)) {
                    ?>
                    <div class="col-sm-4 col-lg-3">
                        <div class="form-group">
                            <input type="text" name="keyword" id="keyword" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_KEYWORD'); ?>" value="<?php echo $keyword; ?>">
                        </div>
                    </div>
                    <?php
                } ?><!-- end of keyword column -->

                <?php if ($params->get('show_status', 1)) {
                    ?>
                <div class="col-lg-3 col-sm-4">
                    <div class="pstatus">
                        <div class="form-group">
                            <select name="pstatus" id="pstatus" class="form-control">
                                <option value="" <?php echo ($pstatus == '') ? 'selected="selected"' : ''; ?>>
                                    <?php echo Text::_('MOD_SPPROPERTY_SEARCH_STATUS'); ?>
                                </option>
                                <?php foreach ($property_statuses as $key => $status) {
                                    ?>
                                <option value="<?php echo $key; ?>" <?php echo ($pstatus == $key) ? 'selected="selected"' : ''; ?>>
                                    <?php echo $status; ?>
                                </option>
                                    <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                    <?php
                } ?><!-- end of status column -->    
                
                <?php if ($params->get('show_location', 1)) {
                    ?>
                <div class="col-sm-4 col-lg-2">
                    <div class="location">
                        <div class="form-group">
                            <select name="city" id="city" class="form-control">
                                <option value="" <?php echo ($city_name == '') ? 'selected="selected"' : ''; ?>>
                                    <?php echo Text::_('MOD_SPPROPERTY_SEARCH_LOCATION'); ?>
                                </option>
                                <?php foreach ($items as $key => $city) {
                                    ?>
                                    <option value="<?php echo $key; ?>" <?php echo ($city_name == $city) ? 'selected="selected"' : ''; ?>> <?php echo $city; ?></option>
                                    <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                    <?php
                } ?> <!-- end of location column -->
                
                <?php if ($params->get('show_category', 1)) {
                    ?>
                <div class="col-sm-3 col-lg-2">
                    <div class="category">
                        <div class="form-group">
                            <select id="category" class="form-control" name="catid">
                                <option value="" <?php echo ($catid == '') ? 'selected="selected"' : ''; ?>>
                                    <?php echo Text::_('MOD_SPPROPERTY_SEARCH_SELECT'); ?>
                                </option>
                                <?php foreach ($cats as $cat) {
                                    ?>
                                    <option value="<?php echo $cat->id; ?>" <?php echo ($catid == $cat->id) ? 'selected="selected"' : ''; ?>><?php echo $cat->title; ?></option>
                                    <?php
                                } ?>
                            </select>
                        </div>
                    </div>
                </div><!-- end of category column -->
                    <?php
                } ?>
                
                <div class="col-sm-4 col-lg-2">
                    <div class="property-search-button">
                        <div class="form-group">
                            <button type="submit" id="mod-spproperty-search-submit" class="sppb-btn sppb-btn-primary btn-sm">
                                <?php echo Text::_('MOD_SPPROPERTY_SEARCH_BTN_TEXT'); ?>
                            </button>
                        </div>
                    </div>
                </div><!-- end of submit button column -->
                
            </div><!-- end of Basic Search -->
        </div><!-- end of basic search row -->

        <div class="row">
            <?php if ($params->get('show_advance', 1)) {
                ?>
                <div class="col-lg-2 col-sm-4">
                    <div class="form-group">
                        <a href="javascript:" class='spproperty-show-advance'><i class="fa fa-cog"></i><span class="btn-text"> <?php echo Text::_('MOD_SPPROPERTY_SEARCH_ADVANCED_SEARCH'); ?></span></a>
                    </div>
                </div>
                <?php
            } ?><!-- end of show advance option -->
        </div>
        
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="spproperty-search-advance">
                    <hr>
                    <div class="row">
                        <?php if ($params->get('show_pricerange', 1)) {
                            ?>
                            <?php if ($params->get('ranger', 'inputbox') === 'inputbox') {
                                ?>
                                <div class="col-sm-4 col-lg-3">
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <input name="min_price" id="adv-min-price" type="number" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_MIN_PRICE'); ?>" value="<?php echo $min_price; ?>">
                                            </div>
                                        </div><!-- end of min-price column -->
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <input name="max_price" id="adv-max-price" type="number" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_MAX_PRICE'); ?>" value="<?php echo $max_price; ?>">
                                            </div>
                                        </div><!-- end of max-price column -->
                                    </div>
                                </div>
                                <?php
                            } elseif ($params->get('ranger', 'inputbox') === 'range-slider') {
                                ?>
                                <div class="col-sm-12 col-lg-3">
                                    <div class="form-group">
                                        <span for=""><?php echo Text::_('MOD_SPPROPERTY_SEARCH_PRICE_RANGE'); ?></span>
                                        <small class="spproperty-price-range"></small>
                                        <div class="price-range" id="price-range"></div>
                                        <input type="hidden" name="min_price" id="adv-min-price">
                                        <input type="hidden" name="max_price" id="adv-max-price">
                                    </div>
                                </div><!-- end of price range column -->
                                <?php
                            } elseif ($params->get('ranger', 'inputbox') === 'range-dropdown') { ?>
                                <div class="col-sm-12 col-lg-3">
                                    <div class="form-group">
                                        <select name="price_range_dropdown" id="price_range_dropdown">
                                            <?php foreach ($propertyPriceRangeDropdownData as $key => $priceRange) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($key === $propertyPriceDropdown) ? "selected='selected'" : ''; ?> >
                                                <?php echo $priceRange; ?>
                                            </option>
                                            <?php } ?>
                                        </select>                                    
                                    </div>
                                </div>
                            <?php }
                        } ?>
                        
                        <?php if ($params->get('show_sizerange', 1)) {
                            ?>
                            <?php if ($params->get('ranger', 'inputbox') === 'inputbox') {
                                ?>
                                <div class="col-sm-4 col-lg-3">
                                    <div class="row">
                                        <div class="col-sm-4 col-lg-6">
                                            <div class="form-group">
                                                <input type="number" id="adv-min-size" class="form-control" name="minsize" placeholder="<?php echo empty($cParams['measurement']) ? Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE_SQFT') : Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE'); ?>" value="<?php echo $minsize; ?>">
                                            </div>
                                        </div>
                                        <div class="col-sm-4 col-lg-6">
                                            <div class="form-group">
                                                <input type="number" id="adv-max-size" class="form-control" name="maxsize" placeholder="<?php echo empty($cParams['measurement']) ? Text::_('MOD_SPPROPERTY_SEARCH_MAX_SIZE_SQFT') : Text::_('MOD_SPPROPERTY_SEARCH_MAX_SIZE'); ?>" value="<?php echo $maxsize; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } elseif ($params->get('ranger', 'inputbox') === 'range-slider') { ?>
                                    
                                <div class="col-sm-12 col-lg-3">
                                    <div class="form-group">
                                        <span for=""><?php echo Text::_('MOD_SPPROPERTY_SEARCH_SIZE_RANGE'); ?> </span>
                                        <small class="spproperty-size-range"></small>
                                        <div class="size-range" id="size-range"></div>
                                        <input type="hidden" name="minsize" id="adv-min-size" >
                                        <input type="hidden" name="maxsize" id="adv-max-size" >
                                    </div>
                                </div><!-- end of size-range column -->
                                
                                <?php } elseif ($params->get('ranger', 'inputbox') === 'range-dropdown') { ?>
                                <div class="col-sm-12 col-lg-3">
                                    <div class="form-group">
                                        <select name="psize_range_dropdown" id="psize_range_dropdown">
                                            <?php foreach ($propertySizeRangeDropdownData as $key => $sizeRange) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($key === $propertySizeDropDown) ? "selected='selected'" : ''; ?> >
                                                <?php echo $sizeRange; ?>
                                            </option>
                                            <?php } ?>
                                        </select>                                       
                                    </div>
                                </div>
                            <?php } ?>                    
                        <?php } ?>

                        <?php if ($params->get('show_beds', 1)) {
                            ?>
                        <div class="col-lg-2 col-sm-4">
                            <div class="bed">
                                <div class="form-group">
                                    <select name="beds" id="beds" class="form-control">
                                        <option value="" <?php echo ($beds == '') ? 'selected="selected"' : ''; ?>><?php echo Text::_('MOD_SPPROPERTY_SEARCH_BED'); ?></option>

                                        <?php
                                        foreach ($numerics as $key => $numeric) {
                                            $bed_text = ($key == 0) ? Text::_('MOD_SPPROPERTY_SEARCH_BED') : Text::_('MOD_SPPROPERTY_SEARCH_BEDS');
                                            ?>
                                            <option value="<?php echo $numeric; ?>" <?php echo ($beds == $numeric) ? 'selected="selected"' : ''; ?>><?php echo $numeric . ' ' . $bed_text; ?></option>
                                            <?php
                                        } ?>

                                    </select>
                                </div>
                            </div><!-- end of bed column -->
                        </div>
                            <?php
                        } ?>
                        <?php if ($params->get('show_baths', 1)) {
                            ?>
                        <div class="col-lg-2 col-sm-4">
                            <div class="bath">
                                <div class="form-group">
                                    <select name="baths" id="baths" class="form-control">
                                        <option value="" <?php echo ($baths == '') ? 'selected="selected"' : ''; ?>>
                                            <?php echo Text::_('MOD_SPPROPERTY_SEARCH_BATH'); ?>
                                        </option>

                                        <?php
                                        foreach ($numerics as $key => $numeric) {
                                            $bath_text = ($key == 0) ? Text::_('MOD_SPPROPERTY_SEARCH_BATH') : Text::_('MOD_SPPROPERTY_SEARCH_BATHS');
                                            ?>
                                            <option value="<?php echo $numeric; ?>" <?php echo ($baths == $numeric) ? 'selected="selected"' : ''; ?>>
                                                <?php echo $numeric . ' ' . $bath_text; ?>
                                            </option>
                                            <?php
                                        } ?>

                                    </select>
                                </div>
                            </div>
                        </div><!-- end of bath column -->
                            <?php
                        } ?>
                        
                        <?php if ($params->get('show_parking', 1)) {
                            ?>
                        <div class="col-lg-2 col-sm-4">
                            <div class="form-group">
                                <select id="parking" class="form-control">
                                    <option value=""  <?php echo ($parking == '') ? 'selected="selected"' : ''; ?>>
                                        <?php echo Text::_('MOD_SPPROPERTY_SEARCH_PARKING'); ?>
                                    </option>

                                    <?php
                                    foreach ($numerics as $key => $numeric) {
                                        $park_text = ($key == 0) ? Text::_('MOD_SPPROPERTY_SEARCH_PARKING') : Text::_('MOD_SPPROPERTY_SEARCH_PARKINGS');
                                        ?>

                                        <option value="<?php echo $numeric; ?>" <?php echo ($parking == $numeric) ? 'selected="selected"' : ''; ?>>
                                            <?php echo $numeric . ' ' . $park_text; ?>
                                        </option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div><!-- end of parking column -->
                            <?php
                        } ?>
                        
                        <?php if ($params->get('show_zipcode', 1)) {
                            ?>
                        <div class="col-sm-3 col-lg-4">
                            <div class="form-group">
                                <input name="zipcode" class="form-control" id="zip-codes" type="text" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_ZIP_CODE'); ?>" value="<?php echo $zipcode; ?>">
                            </div>
                        </div><!-- end of zip code column -->
                            <?php
                        } ?>
                        
                        <?php if ($params->get('show_pfeatures', 1)) {
                            ?>
                        <div class="col-lg-4 col-sm-4">
                            <div class="form-group">
                                <select name="p_features[]" id="p_features" multiple="multiple">
                                    <?php foreach ($features as $key => $feature) {
                                        ?>
                                        <option value="<?php echo $feature->id; ?>" <?php echo in_array($feature->id, $p_features) ? "selected='selected'" : ''; ?> ><?php echo $feature->title; ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div><!-- end of property features column -->  
                            <?php
                        } ?>

                        <!-- if Show Flat and Floor Number option is enabled -->
                        <?php if ($params->get('show_lvlftno',1)): ?>
                            
                            <!-- if dropdown option is enabled -->
                            <?php if ($params->get('show_lvlftno_option') === 'dropdown') : ?>
                                <?php $options = $flatAndFloorNoList;?>
                                <div class="col-lg-4 col-sm-4">
                                    <div class="form-group">
                                        <select name="lvlftno_dropdown" id="lvlftno_dropdown">
                                            <?php foreach ($flatAndFloorNoList as $key => $flatAndFloor) { ?>
                                                <option value="<?php echo $key; ?>" <?php echo ($key === $levelFlatNoDropdown) ? "selected='selected'" : ''; ?> >
                                                    <?php echo $flatAndFloor; ?>
                                                </option>
                                            <?php } ?>
                                        </select>                                    
                                    </div>
                                </div>
                            <?php endif; ?>  <!-- End of dropdown option -->
                            
                            <!-- if input field option is enabled -->
                            <?php if ($params->get('show_lvlftno_option') === 'input_field'): ?>
                                <div class="col-sm-3 col-lg-4">
                                    <div class="form-group">
                                        <input name="lvlftno_inputfield" class="form-control" id="lvlftno_inputfield" type="text" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_FLAT_FLOOR_AND_FLAT_NO'); ?>" value="<?php echo $levelFlatNoInput; ?>">
                                    </div>
                                </div>
                            <?php endif; ?> <!-- End of  input field option -->
                        <?php endif; ?> <!-- End of Show Flat and Floor Number option -->
                    </div>
                    
                </div><!-- end of advance search -->
            </div>
        </div><!-- end of advance search row -->
        

        <div class="input-box">
            <input type="hidden" name="rooturl" id="url" value="<?php echo Uri::root(); ?>">
            <input type="hidden" name="menuid" id="menuid" value="<?php echo $menuItemId; ?>">
        </div>
    </form> <!-- #spproperty-search -->
</div>
