<?php

/**
 * @package Empire
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

class SppagebuilderAddonThumb_slider extends SppagebuilderAddons {

    public function render() {
        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
        $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

        $slide_height = (isset($this->addon->settings->slide_height) && $this->addon->settings->slide_height) ? $this->addon->settings->slide_height : '';
        $autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? $this->addon->settings->autoplay : '';
        $button_text = (isset($this->addon->settings->button_text) && $this->addon->settings->button_text) ? $this->addon->settings->button_text : '';
        $button_url = (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? $this->addon->settings->button_url : '';
        $arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : 'true';

        //output start
        //autoplay, controllers & arrow
        $slide_autoplay = ($autoplay) ? 'data-sppb-tg-autoplay="true"' : 'data-sppb-tg-autoplay="false"';
        $slide_arrows = ($arrows) ? 'data-sppb-tg-arrows="true"' : 'data-sppb-tg-arrows="false"';

        //slide height
        $doc = JFactory::getDocument();
        $slide_height_style = '.sppb-addon-thumb-gallery #slider .slides > li .thumb-slider-bg{ height: ' . $slide_height . 'px; }';
        $doc->addStyleDeclaration($slide_height_style);


        $output = '<div class="sppb-addon sppb-thumb-gallery-wrapper sppb-addon-thumb-gallery ' . $class . '">';

        if ($title) {
            $output .= '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>';
        }
        $output .= '<div id="slider" class="flexslider sppb-tg-slider" ' . $slide_autoplay . ' ' . $slide_arrows . '>';
        $output .= '<ul class="slides">';

        foreach ($this->addon->settings->sp_thumb_slider_item as $slideItem) {
            // slide image
            $bg_image = "";
            if (isset($slideItem->image)) {
                if (is_object($slideItem->image)) {
                    $bg_image = "background-image: url(" . $slideItem->image->src . ")";
                } elseif (is_string($slideItem->image)) {
                    $bg_image = "background-image: url(" . $slideItem->image . ")";
                }
            }

            // *** animation *** //
            // Title animation
            $title_animation = '';
            if (isset($slideItem->title_animation) && $slideItem->title_animation) {
                $title_animation = ' sppb-wow ' . $slideItem->title_animation;
            }
            // title attr
            $title_data_attr = '';
            if (isset($slideItem->title_animationduration) && $slideItem->title_animationduration)
                $title_data_attr .= ' data-sppb-wow-duration="' . $slideItem->title_animationduration . 'ms"';
            if (isset($slideItem->title_animationdelay) && $slideItem->title_animationdelay)
                $title_data_attr .= ' data-sppb-wow-delay="' . $slideItem->title_animationdelay . 'ms"';

            // sub title animation
            $subtitle_animation = '';
            if (isset($slideItem->subtitle_animation) && $slideItem->subtitle_animation) {
                $subtitle_animation = ' sppb-wow ' . $slideItem->subtitle_animation;
            }
            // subtitle attr
            $subtitle_data_attr = '';
            if (isset($slideItem->subtitle_animationduration) && $slideItem->subtitle_animationduration)
                $subtitle_data_attr .= ' data-sppb-wow-duration="' . $slideItem->subtitle_animationduration . 'ms"';
            if (isset($slideItem->subtitle_animationdelay) && $slideItem->subtitle_animationdelay)
                $subtitle_data_attr .= ' data-sppb-wow-delay="' . $slideItem->subtitle_animationdelay . 'ms"';

            // button animation
            $button_animation = '';
            if (isset($slideItem->button_animation) && $slideItem->button_animation) {
                $button_animation = ' sppb-wow ' . $slideItem->button_animation;
            }

            // button attr
            $button_data_attr = '';
            if (isset($slideItem->subtitle_animationduration) && $slideItem->subtitle_animationduration)
                $button_data_attr .= ' data-sppb-wow-duration="' . $slideItem->subtitle_animationduration . 'ms"';
            if (isset($slideItem->subtitle_animationdelay) && $slideItem->subtitle_animationdelay)
                $button_data_attr .= ' data-sppb-wow-delay="' . $slideItem->subtitle_animationdelay . 'ms"';

            $output .= '<li>';
            $output .= '<div class="thumb-slider-bg" style="' . $bg_image . '">';
            $output .= '<div class="slider-title-wrap">';
            $output .= '<h1 class="slider-title ' . $title_animation . '" ' . $title_data_attr . '>' . $slideItem->title . '</h1>';
            $output .= '<p class="slider-sub-title ' . $subtitle_animation . '" ' . $subtitle_data_attr . '>' . $slideItem->sub_title . '</p>';
            $output .= '<a class="btn btn-primary btn-sm ' . $button_animation . '" href="' . $slideItem->button_url . '" ' . $button_data_attr . '>' . $slideItem->button_text . '</a>';
            $output .= '</div>'; //.slider-title-wrap
            $output .= '</div>'; //.thumb-slider-bg
            $output .= '</li>';
        }

        $output .= '</ul>'; //ul.slides
        $output .= '</div>'; // END /#slider


        $output .= '<div class="slide_thumb_wrap">';
        $output .= '<div id="carousel" class="flexslider">';
        $output .= '<ul class="slides">'; // END /#slider
        foreach ($this->addon->settings->sp_thumb_slider_item as $slideItem) {
            $image = "";
            if (isset($slideItem->image)) {
                if (is_object($slideItem->image)) {
                    $image = "src=" . $slideItem->image->src;
                } elseif (is_string($slideItem->image)) {
                    $image = "src=" . $slideItem->image;
                }
            }
            $output .= '<li>';
            $output .= '<div class="thumb-wrap">';
            $output .= '<div class="thumb-text">';
            $output .= '<h4 class="slider-title" ' . $title_data_attr . '>' . $slideItem->title . '</h4>';
            $output .= '</div>';
            $output .= '<img ' . $image . '  alt="' . $slideItem->title . '"/>';
            $output .= '</div>';
            $output .= '</li>';
        }
        $output .= '</ul>'; // END /.slides
        $output .= '</div>'; // END /#carousel
        $output .= '</div>'; // END /.slide_thumb_wrap

        $output .= '</div>'; // END /.flexslider

        return $output;
    }

    public function scripts() {
        $app = JFactory::getApplication();
        return array(JURI::base() . '/templates/' . $app->getTemplate() . '/js/jquery.flexslider-min.js');
    }

    public function js() {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        return '
        jQuery(function ($) {
            if ($("' . $addon_id . ' #carousel").is(".flexslider")) {

                var $sppbTgOptions = $("' . $addon_id . ' .sppb-tg-slider");

                var $autoplay = $sppbTgOptions.data("sppb-tg-autoplay");
                var $arrows = $sppbTgOptions.data("sppb-tg-arrows");

                $(window).on("load", function () {
                    $("' . $addon_id . ' #carousel").flexslider({
                        animation: "slide",
                        controlNav: true,
                        directionNav: $arrows,
                        animationLoop: false,
                        slideshow: $autoplay,
                        itemWidth: 320,
                        itemMargin: 0,
                        asNavFor: "' . $addon_id . ' #slider"
                    });

                    $("' . $addon_id . ' #slider").flexslider({
                        animation: "fade",
                        controlNav: false,
                        directionNav: $arrows,
                        animationLoop: false,
                        slideshow: $autoplay,
                        sync: "' . $addon_id . ' #carousel"
                    });
                });
            }
        });
        ';
    }

    public function stylesheets() {
        $app = JFactory::getApplication();
        return array(JURI::base() . '/templates/' . $app->getTemplate() . '/css/flexslider.css');
    }

    public static function getTemplate() {
        $output = '
            <style type="text/css">
                    <# _.each (data.sp_thumb_slider_item, function(slide_item, item_key) { #>
                        #sppb-addon-{{ data.id }} .item-{{ data.id }}-{{ item_key }} .thumb-slider-bg{
                            background-repeat: no-repeat;
                            background-size: cover;
                            background-position: center center;
                        }
                    <# }); #>
                </style>
            <#
                var contentClass = (!_.isEmpty(data.class) && data.class) ? data.class : "";
                var title = (!_.isEmpty(data.title) && data.title) ? data.title : "";
                var heading_selector = (!_.isEmpty(data.heading_selector) && data.heading_selector) ? data.heading_selector : "h3";

                var slide_height = (!_.isEmpty(data.slide_height) && data.slide_height) ? data.slide_height : "";
                var autoplay = (!_.isEmpty(data.autoplay) && data.autoplay) ? data.autoplay : 1;
                var button_text = (!_.isEmpty(data.button_text) && data.button_text) ? data.button_text : "";
                var button_url = (!_.isEmpty(data.button_url) && data.button_url) ? data.button_url : "";
                var arrows = (!_.isEmpty(data.arrows) && data.arrows) ? data.arrows : 1;

                var slide_autoplay = (autoplay > 0) ? \'data-sppb-tg-autoplay="true"\' : \'data-sppb-tg-autoplay="false"\';
                var slide_arrows = (arrows > 0) ? \'data-sppb-tg-arrows="true"\' : \'data-sppb-tg-arrows="false"\';
                #>

                <div class="sppb-addon sppb-thumb-gallery-wrapper sppb-addon-thumb-gallery {{contentClass}}" style="height:{{data.slide_height}}px;">

                <# if (title) { #>
                    <{{heading_selector}} class="sppb-addon-title">{{{title}}}</{{heading_selector}}>
                <# } #>
                <div id="slider" class="flexslider sppb-tg-slider" {{{slide_autoplay}}} {{{slide_arrows}}}>
                <ul class="slides">

                <# _.each (data.sp_thumb_slider_item, function (slideItem, item_key) {

                    var title_animation = "";
                    if (!_.isEmpty(slideItem.title_animation)) {
                        title_animation = \' sppb-wow \' + slideItem.title_animation;
                    }

                    var title_data_attr = "";
                    if (!_.isEmpty(slideItem.title_animationduration)){
                        title_data_attr += \' data-sppb-wow-duration="\' + slideItem.title_animationduration + \'ms"\';
                    }
                    if (!_.isEmpty(slideItem.title_animationdelay)){
                        title_data_attr += \' data-sppb-wow-delay="\' + slideItem.title_animationdelay + \'ms"\';
                    }

                    var subtitle_animation = "";
                    if (!_.isEmpty(slideItem.subtitle_animation)) {
                        subtitle_animation = \' sppb-wow \' + slideItem.subtitle_animation;
                    }

                    var subtitle_data_attr = "";
                    if (!_.isEmpty(slideItem.subtitle_animationduration)){
                        subtitle_data_attr += \' data-sppb-wow-duration="\' + slideItem.subtitle_animationduration + \'ms"\';
                    }
                    if (!_.isEmpty(slideItem.subtitle_animationdelay)){
                        subtitle_data_attr += \' data-sppb-wow-delay="\' + slideItem.subtitle_animationdelay + \'ms"\';
                    }

                    var button_animation = "";
                    if (!_.isEmpty(slideItem.button_animation) && slideItem.button_animation) {
                        button_animation = \' sppb-wow \' + slideItem.button_animation;
                    }

                    var button_data_attr = "";
                    if (!_.isEmpty(slideItem.subtitle_animationduration)){
                        button_data_attr += \' data-sppb-wow-duration="\' + slideItem.subtitle_animationduration + \'ms"\';
                    }
                    if (!_.isEmpty(slideItem.subtitle_animationdelay) && slideItem.subtitle_animationdelay){
                        button_data_attr += \' data-sppb-wow-delay="\' + slideItem.subtitle_animationdelay + \'ms"\';
                    }
                    var activeClass ="";
                    if(item_key==0){
                        activeClass = "flex-active-slide";
                    } else {
                        activeClass = "";
                    }

                    var main_bg = "";
                    if((typeof slideItem.image !== "undefined") && (typeof slideItem.image.src !== "undefined")){
                        main_bg = "background-image: url(" + slideItem.image.src + ")";
                    } else {
                        main_bg = "background-image: url(" + slideItem.image + ")";;
                    }
                #>
                    <li class="item-{{ data.id }}-{{ item_key }}">
                    <div class="thumb-slider-bg" style="{{main_bg}};height:{{data.slide_height}}px;">
                    <div class="slider-title-wrap">
                    <h1 class="slider-title {{title_animation}}" {{{title_data_attr}}}>{{{slideItem.title}}}</h1>
                    <p class="slider-sub-title {{subtitle_animation}}" {{{subtitle_data_attr}}}>{{{slideItem.sub_title}}}</p>
                    <a class="btn btn-primary btn-sm {{button_animation}}" href="{{slideItem.button_url}}" {{button_data_attr}}>{{slideItem.button_text}}</a>
                    </div>
                    </div>
                    </li>
                <# }) #>

                </ul>
                </div>
                <div class="slide_thumb_wrap">
                <div id="carousel" class="flexslider">
                <ul class="slides">
                <# _.each (data.sp_thumb_slider_item, function(slideItem) { 
                    var image = "";
                    if((typeof slideItem.image !== "undefined") && (typeof slideItem.image.src !== "undefined")){
                        image = "src=" + slideItem.image.src;
                    } else {
                        image = "src=" + slideItem.image;;
                    }
                #>
                    <li>
                    <div class="thumb-wrap">
                    <div class="thumb-text">
                    <h4 class="slider-title">{{{slideItem.title}}}</h4>
                    </div>
                    <img {{image}}  alt="{{{slideItem.title}}}"/>
                    </div>
                    </li>
                <# }) #>
                </ul>
                </div>
                </div>
                </div>
                ';
        return $output;
    }

}
