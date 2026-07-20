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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', '#p_features', null, array(
    'width' => '100%',
    'placeholder_text_multiple' => Text::_('MOD_SPPROPERTY_SEARCH_PROPERTY_FEATURES'),
    'display_selected_options' => true,
    'inherit_select_classes' => true
));
$input      = Factory::getApplication()->input;
$keyword    = $input->get('keyword', null, 'STRING');
$pstatus    = $input->get('pstatus', null, 'STRING');
$city_name  = $input->get('city', null, 'STRING');
$minsize    = $input->get('minsize', null, 'INT');
$maxsize    = $input->get('maxsize', null, 'INT');
$beds       = $input->get('beds', null, 'INT');
$baths      = $input->get('baths', null, 'INT');
$parking    = $input->get('parking', null, 'INT');
$zipcode    = $input->get('zipcode', null, 'INT');
$catid      = $input->get('catid', null, 'INT');
$min_price  = $input->get('min_price', null, 'INT');
$max_price  = $input->get('max_price', null, 'INT');
$p_features = $input->get('p_features', null, 'STRING');
if (!is_null($p_features)) {
    $p_features = explode('-', $p_features);
} else {
    $p_features = array();
}

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
$reset_uri = '';
if ($itemid) {
    $reset_uri = Route::_('index.php?option=com_spproperty&view=' . $view . SppropertyHelper::getItemid($view));
}

$isReset = false;
if (
    !empty($keyword) || !empty($pstatus) || !empty($city_name)
    || !empty($minsize) || !empty($maxsize) || !empty($beds)
    || !empty($baths) || !empty($parking) || !empty($zipcode)
    || !empty($catid) || !empty($max_price) || !empty($min_price) || !empty($p_features)
) {
    $isReset = true;
}

?>

<div id="mod-sp-property-search<?php echo $module->id; ?>" class="sp-property-search <?php echo $params->get('moduleclass_sfx') ?>">
    <div class="row">
        <form class="spproperty-search">
            <?php if ($params->get('show_keyword', 1)) {
                ?>
            <div class="col-sm-4 col-lg-2">
                <div class="keyword">
                    <input type="text" name="keyword" id="keyword" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_KEYWORD'); ?>" value="<?php echo $keyword; ?>">
                </div>
            </div>
                <?php
            } ?>
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
            } ?>

            <?php if ($params->get('show_status', 1)) {
                ?>
            <div class="col-lg-2 col-sm-4">
                <div class="pstatus">
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
                <?php
            } ?>

            <?php if ($params->get('show_minsize', 1)) {
                ?>
            <div class="col-sm-4 col-lg-2">
                <div class="area">
                    <input type="number" name="min-size" id="min-size" class="form-control" placeholder="<?php echo empty(trim($cParams['measurement'])) ? Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE_SQFT') : Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE') . '/' . $cParams['measurement']; ?>" value="<?php echo $minsize; ?>">
                </div>
            </div>
                <?php
            } ?>

            <?php if ($params->get('show_maxsize', 1)) {
                ?>
            <div class="col-sm-4 col-lg-2">
                <div class="max-price">
                    <input type="number" name="max-price" id="max-price" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_MAX_PRICE'); ?>" value="<?php echo $max_price; ?>">
                </div>
            </div>
                <?php
            } ?>

            <div class="input-box">
                <input type="hidden" name="rooturl" id="url" value="<?php echo Uri::root(); ?>">
                <input type="hidden" name="menuid" id="menuid" value="<?php echo SppropertyHelper::getItemid($view); ?>">
            </div>

            <div class="col-sm-4 col-lg-2">
                <div class="property-search-button">
                    <button type="submit" id="mod-spproperty-search-submit" class="sppb-btn sppb-btn-primary btn-sm">
                        <?php echo Text::_('MOD_SPPROPERTY_SEARCH_BTN_TEXT'); ?>
                    </button>
                </div>
            </div>
        </form>   <!-- #spproperty-search -->
    </div><!--/.row-->
    <div calss="row">
        <?php if ($params->get('show_advance', 1)) {
            ?>
        <div class="property-advance-search">
            <a href="#" data-toggle="modal" data-target=".sp-modal-lg"><i class="fa fa-plus-square"></i><span class="btn-text"><?php echo Text::_('MOD_SPPROPERTY_SEARCH_ADVANCED_SEARCH'); ?></span></a>
        </div>
            <?php
        } ?>
    </div>
</div>
<!-- Modal Content -->
<div class="modal fade sp-modal-lg sp-property-search" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="sp-advaced-search">
                <div class="container">
                    <div class="row">
                        <div class="sp-advance-search-wrap">
                            <div class="sp-advance-serach-title text-center">
                                <div class="sp-advance-icon">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </div>
                                <h4><?php echo Text::_('MOD_SPPROPERTY_SEARCH_ADVANCED_SEARCH'); ?></h4>
                            </div>
                            <form class="spproperty-adv-search">
                                <div class="row">
                                    <div class="col-sm-3 col-lg-6">
                                        <div class="keyword">
                                            <input type="text" id="keyword" class="form-control" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_KEYWORD'); ?>" value="<?php echo $keyword; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-2 col-sm-3">
                                        <div class="location">
                                            <div class="form-group">
                                                <select id="city" class="form-control">
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
                                    <div class="col-lg-2 col-sm-3">
                                        <div class="zip-code">
                                            <input name="zip-code" id="zip-code" type="text" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_ZIP_CODE'); ?>" value="<?php echo $zipcode; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-3 col-lg-2">
                                        <div class="category">
                                            <select id="category" class="form-control">
                                                <option value="" <?php echo ($catid == '') ? 'selected="selected"' : ''; ?>>
                                                    <?php echo Text::_('MOD_SPPROPERTY_SEARCH_CATEGORY'); ?>
                                                </option>
                                                <?php foreach ($cats as $cat) {
                                                    ?>
                                                    <option value="<?php echo $cat->id; ?>" <?php echo ($catid == $cat->id) ? 'selected="selected"' : ''; ?>><?php echo $cat->title; ?></option>
                                                    <?php
                                                } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                  <div class="col-lg-2 col-sm-4">
                                      <div class="pstatus">
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
                                    <div class="col-lg-2 col-sm-4">
                                        <div class="bed">
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
                                    </div>
                                    <div class="col-lg-2 col-sm-4">
                                        <div class="bath">
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
                                    <?php if ($params->get('ranger', 'inputbox') === 'inputbox') {
                                        ?>
                                    <div class="col-lg-3 col-sm-4">
                                        <div class="area">
                                            <input type="number" id="adv-min-size" class="form-control" name="minsize" placeholder="<?php echo empty(trim($cParams['measurement'])) ? Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE_SQFT') : Text::_('MOD_SPPROPERTY_SEARCH_MIN_SIZE') . '/' . $cParams['measurement']; ?>" value="<?php echo $minsize; ?>">
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-sm-4">
                                        <div class="area">
                                            <input type="number" id="adv-max-size" class="form-control" name="maxsize" placeholder="<?php echo empty(trim($cParams['measurement'])) ? Text::_('MOD_SPPROPERTY_SEARCH_MAX_SIZE_SQFT') : Text::_('MOD_SPPROPERTY_SEARCH_MAX_SIZE') . '/' . $cParams['measurement']; ?>" value="<?php echo $maxsize; ?>">
                                        </div>
                                    </div>
                                        <?php
                                    } else {
                                        ?>
                                    <div class="col-lg-6 col-sm-12">
                                        <label for="">Size Range</label>
                                        <small class="spproperty-size-range"></small>
                                        <div class="size-range" id="size-range"></div>
                                        <input type="hidden" name="minsize" id="adv-min-size" >
                                        <input type="hidden" name="maxsize" id="adv-max-size" >
                                    </div>
                                        <?php
                                    } ?>
                                    <div class="input-box">
                                        <input type="hidden" id="url" name="rooturl" value="<?php echo Uri::root(); ?>">
                                        <input type="hidden" id="menuid" name="menuid" value="<?php echo SppropertyHelper::getItemid($view); ?>">
                                    </div>
                                </div>

                                <div class="row p-features-rows">
                                    <div class="col-lg-4 col-sm-8">
                                        <select name="p_features[]" id="p_features" multiple="multiple">
                                        <?php foreach ($features as $key => $feature) {
                                            ?>
                                            <option value="<?php echo $feature->id; ?>" <?php echo in_array($feature->id, $p_features) ? "selected='selected'" : ''; ?> ><?php echo $feature->title; ?></option>
                                            <?php
                                        } ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 col-sm-4">
                                        <div class="parking">
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
                                    </div>
                                    <?php if ($params->get('ranger', 'inputbox') === 'inputbox') {
                                        ?>
                                    <div class="col-sm-4 col-lg-3">
                                        <div class="min-price">
                                            <input name="min-price" id="adv-min-price" type="number" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_MIN_PRICE'); ?>" value="<?php echo $min_price; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-4 col-lg-3">
                                        <div class="max-price">
                                            <input name="max-price" id="adv-max-price" type="number" placeholder="<?php echo Text::_('MOD_SPPROPERTY_SEARCH_MAX_PRICE'); ?>" value="<?php echo $max_price; ?>">
                                        </div>
                                    </div>
                                        <?php
                                    } else {
                                        ?>
                                    <div class="col-sm-12 col-lg-6">
                                        <label for="">Price Range</label>
                                        <small class="spproperty-price-range"></small>
                                        <div class="price-range" id="price-range"></div>
                                        <input type="hidden" name="min-price" id="adv-min-price">
                                        <input type="hidden" name="max-price" id="adv-max-price">
                                    </div>
                                        <?php
                                    } ?>
                                </div>

                                <div class="row sp-property-search-button-wrap">
                                    <div class="col-sm-12">
                                        <div class="property-search-button">
                                            <button type="submit" id="mod-spproperty-advsearch-submit" class="sppb-btn sppb-btn-primary btn-sm">
                                                <?php echo Text::_('MOD_SPPROPERTY_SEARCH_BTN_TEXT'); ?>
                                            </button>
                                        </div>
                                        <?php if ($isReset) {
                                            ?>
                                            <!--adding a reset button -->
                                            <div class="property-search-button">
                                                <a href="<?php echo Route::_($reset_uri);?>" id="mod-spproperty-advsearch-reset"
                                                class="sppb-btn sppb-btn-danger btn-sm text-center">
                                                    <?php echo Text::_('MOD_SPPROPERTY_RESET_BTN_TEXT') ;?>
                                                </a>
                                            </div>
                                            <?php
                                        } ?>
                                    </div>
                                </div>
                            </form> <!-- /.spproperty-search -->
                            <span class="close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times" aria-hidden="true"></i></span>
                        </div>
                    </div><!--/.row-->
                </div>
            </div>
        </div>
    </div>
</div>
