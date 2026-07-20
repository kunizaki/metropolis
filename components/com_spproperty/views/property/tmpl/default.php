<?php

/**
* @package com_spproperty
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2025 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;

// No Direct Access
defined('_JEXEC') or die('Restricted Access');


$doc = Factory::getDocument();
$doc->addScriptdeclaration('var spproperty_url="' . Uri::base() . 'index.php?option=com_spproperty";');

?>

<div id="spproperty" class="spproperty spproperty-view-property">
    <?php if ($this->carousel == 'alternative') { ?>
    <div class="owl-carousel owl-theme" id="spproperty-slider">
        <?php if (!empty($this->item->gallery)) { ?>
            <?php foreach ($this->item->gallery as $key => $image) { ?>
                <div class="spproperty-img item">
                    <img alt="<?php echo $image['alt_text'] ?>" src="<?php echo SppropertyHelper::getThumbs($image['photo'], 'property_thumbnail_tower', '640x715'); ?>">
                </div>
            <?php } ?>
        <?php } ?>
    </div><!-- /.spproperty-slider -->
    <?php } else { ?>
    <div class="owl-carousel owl-theme" id="spproperty-slider-alt">
        <?php if (!empty($this->item->gallery)) { ?>
            <?php foreach ($this->item->gallery as $key => $image) { ?>
                <div data-dot="<button role='button' class='owl-dot' style='background-image: url(<?php echo SppropertyHelper::getThumbs($image['photo'], 'property_thumbnail', '640x715') ?>); width: 180px; height: 103px; background-repeat: round;' ></button>">
                    <img src="<?php echo Uri::root() . $image['photo']; ?>" alt="<?php echo $image['alt_text'] ?>">
                </div>
            <?php } ?>
        <?php } ?>
    </div><!-- /.spproperty-slider -->
    <div class="row">
        <div class="col-sm-12">
            <div class="property-dots-container"></div>
        </div>
    </div>
    <?php } ?>

    <div class="spproperty-details-title text-center">
        <?php if ($this->item->cat_info->icon_image) { ?>
            <div class="spproperty-details-icon icon-image">
                <img src="<?php echo Uri::root() . $this->item->cat_info->image; ?>" alt="<?php echo $this->item->cat_info->title; ?>" />
            </div>
            <?php
        } else {
            $cicon = ($this->item->cat_info->icon) ? $this->item->cat_info->icon : 'fa fa-building';
            ?>
            <div class="spproperty-details-icon">
                <i class="<?php echo $cicon; ?>" aria-hidden="true"></i>
            </div>
        <?php } ?>

        <h2>
            <span><?php echo $this->item->cat_info->title; ?></span>
            <?php echo $this->item->title; ?>
        </h2>

        <?php echo LayoutHelper::render('properties.social_share', array('url' => $this->item->url, 'title' => $this->item->title)); ?>

    </div><!-- /.spproperty-details-title -->
    <?php

    ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-8 col-xs-12">
                <div class="spproperty-short-overview">
                    <h5><?php echo Text::_('COM_SPPROPERTY_SHORT_OVERVIEW'); ?></h5>

                    <?php if ($this->item->description) { ?>
                        <div class="spproperty-short-overview-text">
                            <?php echo $this->item->description; ?>
                        </div>
                    <?php } ?>

                    <?php if (
                    !empty($this->item->lvl_fltno) ||
                    !empty($this->item->beds) ||
                    !empty($this->item->baths) ||
                    !empty($this->item->garages) ||
                    !empty($this->item->psize) || 
                    !empty($this->item->building_year)
                    ) { ?>
                        <div class="spproperty-overview-list">
                            <?php if ($this->item->lvl_fltno) { ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-building" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left"><?php echo $this->item->lvl_fltno; ?></p>
                                </div>
                            <?php } if ($this->item->psize) { ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-object-group" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left">
                                        <?php echo $this->item->psize; ?> <?php echo empty($this->cParams['measurement']) ?  Text::_('COM_SPPROPERTY_PROPERTIES_SQFT') : $this->cParams['measurement']; ?>
                                    </p>
                                </div>
                            <?php } if ($this->item->beds) { ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-bed" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left">
                                        <?php echo $this->item->beds . ' ' . Text::_('COM_SPPROPERTY_PROPERTIES_BEDROOMS'); ?>
                                    </p>
                                </div>
                            <?php } if ($this->item->baths) { ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-sign-language" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left">
                                        <?php echo $this->item->baths . ' ' . Text::_('COM_SPPROPERTY_PROPERTIES_BATHS'); ?>
                                    </p>
                                </div>
                            <?php } if ($this->item->garages) { ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-bus" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left">
                                        <?php echo $this->item->garages . ' ' . Text::_('COM_SPPROPERTY_PROPERTIES_PARKING'); ?>
                                    </p>
                                </div>
                            <?php } ?>
                            <?php if ($this->item->building_year) : ?>
                                <div class="spproperty-single-list pull-left">
                                    <span class="pull-left">
                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                    </span>
                                    <p class="pull-left">
                                        <?php echo Text::_('COM_SPPROPERTY_BUILDING_YEAR') . ' ' . $this->item->building_year; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <div class="clearfix"></div>
                        </div>
                    <?php } ?>
                </div>

                <?php if (count($this->featureinfos) && $this->featureinfos) {
                    ?>
                    <div class="spproperty-feature-overview">
                        <h5><?php echo Text::_('COM_SPPROPERTY_FEATURES_OVERVIEW') ?></h5>
                        <p>
                            <?php echo $this->item->features_text; ?>
                        </p>
                        <ul class="spproperty-feature-overview-list">

                            <?php foreach ($this->featureinfos as $featureinfo) { ?>
                                <li>
                                    <div class="spproperty-feature-overview-signle-list">
                                        <?php if ($featureinfo->icon_type == 0) { ?>
                                            <?php if (!empty($featureinfo->icon)) { ?>
                                                <i class="fa fa-<?php echo $featureinfo->icon; ?>" aria-hidden="true"></i>
                                            <?php } else { ?>
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            <?php } ?>
                                        <?php } elseif ($featureinfo->icon_type == 1) { ?>
                                            <?php if (!empty($featureinfo->image)) { ?>
                                                <img src="<?php echo $featureinfo->image; ?>" alt="" class="spproperty-features-image">
                                            <?php } else { ?>
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            <?php } ?>
                                        <?php } elseif ($featureinfo->icon_type == 2) { ?>
                                            <?php if (!empty($featureinfo->icon_sp)) { ?>
                                                <i class="icon-sp <?php echo $featureinfo->icon_sp; ?>" aria-hidden="true"></i>
                                            <?php } else { ?>
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            <?php } ?>
                                        <?php } elseif ($featureinfo->icon_type == 3) { ?>
                                            <?php if (!empty($featureinfo->icon_fa)) { ?>
                                                <i class="<?php echo $featureinfo->icon_fa; ?>" aria-hidden="true"></i>
                                            <?php } else { ?>
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                            <?php } ?>
                                        <?php } ?>
                                        <span><?php echo $featureinfo->title; ?></span>
                                        <div class="clearfix"></div>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div> <!-- /.spproperty-feature-overview -->
                <?php } // has features  ?>

                <?php if (filter_var($this->item->video, FILTER_VALIDATE_URL)) { ?>
                    <!-- Featue Video Overview -->
                    <div class="spproperty-video-overview">
                        <h5><?php echo Text::_('COM_PROPERTY_PROPERTY_VIDEO_TITLE'); ?></h5>
                        <p>
                            <?php echo $this->item->video_text; ?>
                        </p>
                        <div class="spproperty-video">
                            <iframe class="spproperty-embed-responsive-item" src="<?php echo $this->videosrc; ?>" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>
                            <div class="clearfix"></div>
                        </div><!-- /.spproperty-video -->
                    </div> <!-- /.spproperty-feature-overview -->
                <?php } ?>

                <?php if ($this->item->floor_plans && count($this->item->floor_plans)) { ?>
                    <div class="spproperty-floor-plan">
                        <h5><?php echo Text::_('COM_SPPROPERTY_EXPLORE_FLOOR_PLAN'); ?></h5>
                        <p><?php echo $this->item->fp_text; ?></p>
                        <div class="spproperty-floor-plan-tab">
                        <!-- Nav tabs -->
                        <ul class="spproperty-floor-tab-nav" role="tablist">
                            <?php
                            $i = 0;
                            foreach ($this->item->floor_plans as $key => $floor_plan) {
                                $tav_active = ($i == 0) ? 'active' : '';
                                ?>
                                <li role="presentation" class="<?php echo $tav_active; ?>"><a href="#layout-<?php echo $key; ?>" aria-controls="layout-<?php echo $key; ?>" role="tab" data-toggle="tab">
                                        <?php echo $floor_plan['layout_name']; ?>
                                    </a></li>
                                <?php $i++;
                            } ?>
                        </ul>
                        <div class="tab-content">

                            <?php
                            $i = 0;
                            foreach ($this->item->floor_plans as $key => $floor_plan) {
                                $tav_active = ($i == 0) ? 'active show' : '';
                                ?>
                                <div role="tabpanel" class="tab-pane fade in <?php echo $tav_active; ?> text-center" id="layout-<?php echo $key; ?>">
                                    <div class="spproperty-floor-img">
                                        <img alt="" src="<?php echo Uri::root() . $floor_plan['img']; ?>">
                                    </div>

                                    <div class="spproperty-floor-text">
                                        <?php echo $floor_plan['text']; ?>
                                    </div>
                                </div> <!-- /.tab-pane -->
                                <?php $i++;
                            } ?>
                        </div> <!-- /.tab-content -->

                        <!-- if data in new format -->
                        <?php if (!array_key_exists('layout_name', $this->item->floor_plans) && !array_key_exists('img', $this->item->floor_plans) && !array_key_exists('text', $this->item->floor_plans)) { ?>
                        <?php } ?>
                        </div> <!-- /.spproperty-floor-plan-tab -->
                    </div> <!-- /.spproperty-floor-plan -->
                <?php } // has floor plans   ?>
            </div> <!-- /.col-sm-8 -->

            <div class="col-sm-4 col-xs-12">
                <aside class="spproperty-call-us-widget">
                    <?php if (!is_null($this->item->property_id) && $this->cParams->get('show_property_id', true)) { ?>
                        <h3>
                            <span><?php echo Text::_('COM_SPPROPERTY_PROPERTY_ID'); ?></span>
                            <small><?php echo $this->item->property_id; ?></small>    
                        </h3>
                    <?php } ?>
                    <?php if (!is_null($this->item->price_request) && $this->item->price_request == 'request') { ?>
                        <h3>
                            <span><?php echo $this->item->property_status == 'rent' ? Text::_('COM_SPPROPERTY_PROPERTY_RENT') : Text::_('COM_SPPROPERTY_PROPERTY_PRICE'); ?></span>
                        </h3>
                        <small>
                            <a href="#" onclick="return false" data-bs-toggle="modal" data-bs-target="#request-for-price-form" class="btn btn-success btn-block">
                                <small>
                                    <span class="request-title" data-pid="<?php echo $this->item->id; ?>">
                                        <?php echo Text::_('COM_SPPROPERTY_REQUEST_PRICE'); ?>
                                    </span>
                                </small>
                            </a>
                        </small>
                        
                        <form class="spproperty-widget-form-request">
                            <div class="modal fade" tabindex="-1" role="dialog" id="request-for-price-form">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                        <h4 class="modal-title"><?php echo Text::_('COM_SPPROPERTY_REQUEST_PRICE'); ?></h4>
                                    </div>
                                    <div class="modal-body">
                                        <div class="content-body">
                                        <div class="form-group">
                                            <input type="text" class="form-control" name="name" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_FULLNAME'); ?>" required="required">
                                        </div>
                                        <div class="form-group">
                                            <input type="email" class="form-control" name="email" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_EMAIL'); ?>" required="required">
                                        </div>
                                        <div class="form-group">
                                            <input type="tel" class="form-control" name="phone" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_PHONE'); ?>" required="required">
                                        </div>
                                        <div class="form-group">
                                            <textarea name="message" class="form-control" rows="10" cols="5" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_MESSAGE'); ?>" required="required"><?php echo Text::sprintf('COM_SPPROPERTY_PRICE_REQ_MESSGE', $this->item->agent->title, $this->item->title, !empty($this->item->property_id) ? $this->item->property_id : '');?></textarea>
                                        </div>

                                        <input type="hidden" name="sender" value="<?php echo base64_encode($this->recipient); ?>">
                                        <input type="hidden" name="pid" value="<?php echo $this->item->id; ?>">
                                        <input type="hidden" name="visitor_ip" value="<?php echo $this->visitorip; ?>">
                                        <input type="hidden" name="pname" value="<?php echo $this->item->title; ?>">
                                        <input type="hidden" name="request_type" value="price" >
                                        <input type="hidden" name="agent_email" value="<?php echo base64_encode($this->item->agent->email); ?>" >
                                        <input type="hidden" name="property_id" value="<?php echo isset($this->item->property_id) ? $this->item->property_id : ''; ?>" >

                                        <div class="spproperty-captcha">
                                            <input type="hidden" id="showcaptcha" name="showcaptcha" value="<?php echo $this->captcha; ?>">
                                            <?php if ($this->captcha) { ?>
                                                <div class="input-field">
                                                    <?php
                                                        PluginHelper::importPlugin('captcha', $this->captchaPlugin);
                                                        $dispatcher = Factory::getApplication();
                                                        $dispatcher->triggerEvent('onInit', ['dynamic_recaptcha_spmedical']);
                                                        $recaptcha = $dispatcher->triggerEvent('onDisplay', array(null, 'dynamic_recaptcha_spmedical', 'class="spproperty-dynamic-recaptcha"'));

                                                        echo (isset($recaptcha[0])) ? $recaptcha[0] : '<p class="spproperty-alert-warning">' . Text::sprintf('COM_SPMEDICAL_CAPTCHA_NOT_INSTALLED', strtoupper($this->captchaPlugin)) . '</p>';
                                                    ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                        
                                        <?php if ($this->contact_tac) { ?>
                                            <div class="input-field">
                                                <label class="form-checkbox">
                                                    <input type="checkbox" id="tac" name="tac" value="tac" required="true" data-apptac="true">
                                                    <?php echo empty(trim($this->contact_tac_text)) ?  Text::_('COM_SPPROPERTY_TERMS_AND_CONDITIONS') : $this->contact_tac_text; ?>
                                                </label>
                                            </div>
                                        <?php } ?>
                                        </div>
                                        <div class="spproperty-display-tick" style="display: none;">
                                            <div class="circle-loader">
                                                <div class="checkmark draw"></div>
                                            </div>
                                        </div>
                                        <div style="display:none;margin-top:10px;" class="spproperty-req-status-price"></div>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary btn-sm spproperty-req-submit-price"><?php echo Text::_('COM_PROPERTY_FORMBTN_SUBMIT'); ?></button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">Close</button>
                                    </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                        </form>
                    <?php } else { ?>
                        <?php if ($this->item->price) { ?>
                            <?php echo LayoutHelper::render('properties.price_detail', array('property' => $this->item)); ?>
                        <?php } ?>
                    <?php } ?>
                    <hr>

                    <?php if ((isset($this->item->agent->phone) && $this->item->agent->phone) || (isset($this->item->agent->mobile) && $this->item->agent->mobile)) { ?>
                        <p><?php echo Text::_('COM_SPPROPERTY_AGENT_CALL_FOR_BOOKING'); ?></p>
                            <?php if ($this->item->agent->phone) { ?>
                                <a href="tel: <?php echo $this->item->agent->phone ? $this->item->agent->phone : $this->item->agent->mobile; ?>" class="btn btn-primary btn-sm"><i class="fa fa-phone" aria-hidden="true">&nbsp;<span><?php echo $this->item->agent->phone; ?></span></i>
                            <?php } else { ?>
                                <span><?php echo $this->item->agent->mobile; ?></span>
                            <?php } ?>
                        </a>
                    <?php } ?>

                </aside>

                <?php if ($this->item->address) { ?>
                    <aside class="spproperty-map-widget">
                        <div class="spproperty-map">
                            <?php if ($this->map_type == 'google') { ?>
                                <div class="spproperty-property-map">
                                    <div id="spproperty-property-map" class="spproperty-gmap-canvas" data-lat="<?php echo $this->map[0]; ?>" data-lng="<?php echo $this->map[1]; ?>" style="height:300px;">
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div id="map"></div>
                            <?php } ?>
                            <p><i class="fa fa-map-marker" aria-hidden="true"></i></p>
                        </div>
                        <div class="spproperty-map-widget-content">
                            <span><?php echo Text::_('COM_SPPROPERTY_PROJECT_ADDRESS'); ?></span>
                            <p class="spproperty-project-address-text">
                                <?php echo $this->item->address; ?>
                            </p>
                        </div>
                    </aside>
                <?php } ?>

                <?php if (isset($this->item->agent) && $this->item->agent) { ?>
                    <aside class="spproperty-agent-widget-wrap">
                        <?php echo LayoutHelper::render('agents.agent', array('agent' => $this->item->agent, 'desc_limit' => true)); ?>
                    </aside>
                <?php } ?>

                <?php if ($this->cParams->get('req_visit', 1)) { ?>
                    <aside class="spproperty-contact-us-widget">
                        <h3>
                            <span><?php echo Text::_('COM_SPPROPERTY_PROPERTY_ENQUIRY'); ?></span>
                            <?php echo Text::_('COM_SPPROPERTY_PROPERTY_REQUREST_FOR_VISIT'); ?>
                        </h3>
                        <p><?php echo Text::_('COM_SPPROPERTY_PROPERTY_REQUREST_FOR_VISIT_DESC'); ?></p>
                        <form class="spproperty-widget-form">
                            <input type="text" name="name" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_FULLNAME'); ?>" required="required">
                            <input type="email" name="email" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_EMAIL'); ?>" required="required">
                            <input type="tel" name="phone" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_PHONE'); ?>" required="required">
                            <textarea name="message" placeholder="<?php echo Text::_('COMPROPERTY_PLACEHOLDER_MESSAGE'); ?>" required="required"></textarea>
                            <input type="hidden" name="sender" value="<?php echo base64_encode($this->recipient); ?>">
                            <input type="hidden" name="pid" value="<?php echo $this->item->id; ?>">
                            <input type="hidden" name="visitor_ip" value="<?php echo $this->visitorip; ?>">
                            <input type="hidden" name="pname" value="<?php echo $this->item->title; ?>">
                            <input type="hidden" name="request_type" value="visit" >

                            <div class="spproperty-captcha">
                                <input type="hidden" id="showcaptcha" name="showcaptcha" value="<?php echo $this->captcha; ?>">
                                <?php if ($this->captcha) { ?>
                                    <div class="input-field">
                                        <?php
                                            PluginHelper::importPlugin('captcha', $this->captchaPlugin);
                                            $dispatcher = Factory::getApplication();
                                            $dispatcher->triggerEvent('onInit', ['dynamic_recaptcha_spmedical']);
                                            $recaptcha = $dispatcher->triggerEvent('onDisplay', array(null, 'dynamic_recaptcha_spmedical', 'class="spproperty-dynamic-recaptcha"'));

                                            echo (isset($recaptcha[0])) ? $recaptcha[0] : '<p class="spproperty-alert-warning">' . Text::sprintf('COM_SPMEDICAL_CAPTCHA_NOT_INSTALLED', strtoupper($this->captchaPlugin)) . '</p>';
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <?php if ($this->contact_tac) { ?>
                                <div class="input-field">
                                    <label class="form-checkbox">
                                        <input type="checkbox" id="tac" name="tac" value="tac" required="true" data-apptac="true">
                                        <?php $contact_tac_text = !empty($this->contact_tac_text) ? trim($this->contact_tac_text) : '';
                                        echo empty($contact_tac_text) ? Text::_('COM_SPPROPERTY_TERMS_AND_CONDITIONS') : $contact_tac_text; ?>
                                    </label>
                                </div>
                            <?php } ?>
                            <button type="submit" class="btn btn-primary btn-sm spproperty-req-submit"><?php echo Text::_('COM_PROPERTY_FORMBTN_SUBMIT'); ?></button>
                        </form>
                        <div style="display:none;margin-top:10px;" class="spproperty-req-status"></div>
                    </aside>
                <?php } ?>

            </div>
        </div>
    </div>
</div> <!-- /.spproperty -->
<?php if ($this->map_type == 'leaflet') { ?>
<script>
    jQuery(function($){
        var lat = "<?php echo $this->map[0];?>";
        var lon = "<?php echo $this->map[1];?>";
        var mapbox_token = "<?php echo $this->mapbox_token; ?>";
        var map_view    = "<?php echo $this->map_view; ?>";
        var title = "<?php echo $this->item->title; ?>";

        $("#map").spleaflet({
            'markers': [
                {
                    'lat': lat,
                    'lon': lon,
                    'text': title
                }
            ],
            'token' : mapbox_token,
            'view'  : map_view
        });
    });
</script>
<?php } ?>

<script>
    jQuery(function($){
        $("#request-for-price-form").on('show.bs.modal', function(e){
            $('#sp-header-sticky-wrapper').css('z-index', '0');
        });

        $("#request-for-price-form").on('hide.bs.modal', function(e){
            $('#sp-header-sticky-wrapper').css('z-index', '999');
        });
    });
    
</script>