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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

// get component params
$cParams         = ComponentHelper::getParams('com_spproperty');
$property_rm_btn = $cParams->get('prpry_rm_btn_text', Text::_('COM_SPPROPERTY_PROPERTIES_BTN_TEXT'));

$property     = $displayData['property'];
if (!empty($property)) {
    $gallery  = json_decode($property->gallery, true);
    if (!is_bool($photo = SppropertyHelper::old2new($gallery, ['photo', 'alt_text'], 'gallery'))) {
        $gallery = $photo;
    }
}
$user = Factory::getUser();

if ($property) { ?>
    <div class="sp-properties-list-wrapper sp-properties-wrapper property-status-<?php echo $property->property_status; ?>">
        <div class="row">
            <div class="col-lg-5">
                <?php if ($property->property_status == 'sold') { ?>
                    <span class="spproperty-badge-sold"><?php echo Text::_('COM_SPPROPERTY_PROPERTIES_SOLD'); ?></span>
                <?php } ?>
                <div class="property-image">
                    <?php if (!isset($gallery) || empty($gallery)) { ?>
                        <img src="<?php echo $property->thumb; ?>" alt="<?php echo $property->title; ?>">
                    <?php } else { ?>
                        <div class="owl-carousel owl-theme spproperty-gallery" >
                            <?php if (!empty($property->image)) { ?>
                                <div class="item">
                                    <img src="<?php echo $property->thumb; ?>" alt="<?php echo $property->title; ?>">
                                </div>
                            <?php } ?>
                            <?php foreach ($gallery as $v) { ?>
                                <div class="item">
                                    <?php if (isset($v['photo']) && !empty($v['photo'])) { ?>
                                        <img src="<?php echo SppropertyHelper::getThumbs($v['photo'], 'property_thumbnail', '360x207'); ?>" alt="<?php echo $v['alt_text']; ?>">
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
                <?php if ($cParams['enable_add_to_fav']) { ?>
                    <!-- Add or remove to favourite -->
                    <div class="property-favourite">
                        <form action="#" class="property-fav-form">
                            <input type="hidden" name="fav-url" class='property-fav-url' value='<?php echo Uri::root() . 'index.php?option=com_spproperty'; ?>'>
                            <input type="hidden" name="property_id" class="property-fav-id" value="<?php echo $property->id; ?>">
                            <input type="hidden" name="property_fav_flag" class="property_fav_flag" value="<?php echo (isset($property->fav_user_id) && !empty($property->fav_user_id) && $property->fav_user_id == $user->id) ? 0 : 1; ?>">
                            <?php echo HTMLHelper::_('form.token'); ?>
                            <?php if ($user->id) { ?>
                                <button class="property-fav-btn" type="submit">
                                    <?php if (isset($property->fav_user_id) && !empty($property->fav_user_id) && $property->fav_user_id == $user->id) { ?>
                                        <span class="property-fav-icon-fill fa fa-heart"></span>
                                    <?php } else { ?>
                                        <span class="property-fav-icon-o fa fa-heart-o"></span>
                                    <?php } ?>
                                </button>
                            <?php } else { ?>
                                <a href="<?php echo Route::_('index.php?option=com_users&view=login'); ?>"><span class="fa fa-heart-o"></span></a>
                            <?php } ?>
                        </form>
                    </div>
                <?php } ?>
            </div>
            <!-- right side info -->
            <div class="col-lg-7">
                <div class="property-details">
                    <span class="property-category">
                        <?php
                        $category_name = ($property->property_status) ? $property->category_name . ', ' . $property->property_status_txt : $property->category_name;
                        echo $category_name;
                        ?>
                    </span>
                    <h3 class="property-title">
                        <a href="<?php echo $property->url; ?>">
                            <?php echo $property->title; ?>
                        </a>
                    </h3>
                    <?php if ($property->address || $property->city || $property->country) { ?>
                        <p class="property-address">
                            <i class="fa fa-map-marker"></i>
                            <?php echo $property->address ?? $property->address . ($property->city) ?? ',' . $property->city . ($property->country) ?? $property->country; ?>
                        </p>
                    <?php } ?>
                    <span class="property-price">
                        <?php if ($property->price_request == 'show' || is_null($property->price_request)) { ?>
                            <?php if ($property->price) { ?>
                                <?php
                                $calc_price = (float)$property->solid_price * (float)$property->psize;
                                if ($calc_price == 0) {
                                    $calc_price = $property->solid_price;
                                }
                                $total_price = SppropertyHelper::generateCurrency($calc_price, $property->currency, $property->currency_position, $property->currency_format);
                                ?>

                                <?php if ($property->fixed_price) { ?>
                                    <?php echo $property->price; ?>
                                <?php } else { ?>
                                    <?php if ($property->property_status == 'rent') { ?>
                                        <?php echo $total_price; ?>
                                    <?php } else { ?>
                                        <?php echo $property->price; ?>
                                    <?php } ?>
                                <?php } ?>

                                <span class="price-tag">
                                    <?php if ($property->property_status == 'rent') { ?>
                                        <?php
                                        echo !empty($property->rent_period) ? ' / ' . $property->rent_period : ' / ' . Text::_('COM_SPPROPERTY_RENT_PERIOD_DEFAULT');
                                        ?>
                                    <?php } else { ?>
                                        <?php
                                        if (!$property->fixed_price) {
                                            echo empty($cParams['measurement']) ?  ' / ' . Text::_('COM_SPPROPERTY_PROPERTIES_SQFT') : ' / ' . $cParams['measurement'];
                                        }
                                        ?>

                                    <?php } //end of else
                                    ?>
                                </span>
                            <?php } ?>
                        <?php } else { ?>
                            <?php echo Text::_('COM_SPPROPERTY_PRICE_PREVIEW_NOT_AVAILABLE'); ?>
                        <?php } ?>
                    </span>

                    <?php if ($property->psize || $property->beds || $property->baths || $property->garages) { ?>
                        <span class="property-summery">
                            <ul>
                                <?php if ($property->psize) { ?>
                                    <li class="area-size"><?php echo $property->psize; ?> <?php echo empty($cParams['measurement']) ?  Text::_('COM_SPPROPERTY_PROPERTIES_SQFT') : $cParams['measurement']; ?></li>
                                <?php }
                                if ($property->beds) { ?>
                                    <li class="bedroom"><?php echo $property->beds; ?> <?php echo Text::_('COM_SPPROPERTY_PROPERTIES_BEDROOMS'); ?></li>
                                <?php }
                                if ($property->baths) { ?>
                                    <li class="bathroom"><?php echo $property->baths; ?> <?php echo Text::_('COM_SPPROPERTY_PROPERTIES_BATHS'); ?></li>
                                <?php }
                                if ($property->garages) { ?>
                                    <li class="parking"><?php echo $property->garages; ?> <?php echo Text::_('COM_SPPROPERTY_PROPERTIES_PARKING'); ?></li>
                                <?php } ?>
                            </ul>
                        </span>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div> <!-- /.sp-properties-wrapper -->
<?php } ?>

