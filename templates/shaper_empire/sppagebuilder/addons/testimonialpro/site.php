<?php

/**
 * @package SP Page Builder
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted aceess');

class SppagebuilderAddonTestimonialpro extends SppagebuilderAddons {

    public function render() {

        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : '';

        //Options
        $autoplay = (isset($this->addon->settings->autoplay) && $this->addon->settings->autoplay) ? ' data-sppb-ride="sppb-carousel"' : '';
        $arrows = (isset($this->addon->settings->arrows) && $this->addon->settings->arrows) ? $this->addon->settings->arrows : '';
        $controls = (isset($this->addon->settings->controls) && $this->addon->settings->controls) ? $this->addon->settings->controls : 0;

        //Output
        $output = '<div id="sppb-testimonial-pro-' . $this->addon->id . '" class="sppb-carousel sppb-testimonial-pro sppb-slide sppb-text-center' . $class . '"' . $autoplay . '>';

        if ($controls) {
            $output .= '<ol class="sppb-carousel-indicators">';
            foreach ($this->addon->settings->sp_testimonialpro_item as $key1 => $value) {
                $output .= '<li data-sppb-target="#sppb-carousel-' . $this->addon->id . '" ' . (($key1 == 0) ? ' class="active"' : '' ) . '  data-sppb-slide-to="' . $key1 . '"></li>' . "\n";
            }
            $output .= '</ol>';
        }

        $output .= '<div class="sppb-carousel-inner">';

        foreach ($this->addon->settings->sp_testimonialpro_item as $key => $value) {
            $output .= '<div class="sppb-item ' . (($key == 0) ? ' active' : '') . '">';
            $output .= '<div class="col-sm-6">';
            $output .= '<div class="sppb-testimonial-wrapper">';
            $title = '<strong class="pro-client-name">' . $value->title . '</strong>';

            if ($value->url)
                $title .= ' - <span class="pro-client-url">' . $value->url . '</span>';
            $output .= '<div class="sppb-client-wrap">';
                $avatar = "";
                if (isset($value->avatar)) {
                    if (is_object($value->avatar)) {
                        $avatar = $value->avatar->src;
                    } elseif (is_string($value->avatar)) {
                        $avatar = $value->avatar;
                    }
                }
            if ($avatar)
                $output .= '<img class="sppb-img-responsive sppb-avatar ' . $value->avatar_style . '" src="' . $avatar . '" alt="' . $value->title . '">';
            if ($title)
                $output .= '<div class="sppb-testimonial-client">' . $title . '</div>';
            $output .= '</div>'; // End:: .sppb-client-wrap
            $output .= '<div class="sppb-testimonial-message">' . $value->message . '</div>';


            $output .= '</div>'; //End:: .sppb-testimonial-wrapper
            $output .= '</div>'; //End:: .col-sm-6
            $output .= '</div>';
        }
        $output .= '</div>';

        if ($arrows) {
            $output .= '<a href="#sppb-testimonial-pro-' . $this->addon->id . '" class="left sppb-carousel-control" data-slide="prev"><i class="fa fa-angle-left"></i></a>';
            $output .= '<a href="#sppb-testimonial-pro-' . $this->addon->id . '" class="right sppb-carousel-control" data-slide="next"><i class="fa fa-angle-right"></i></a>';
        }

        $output .= '</div>';

        return $output;
    }

    public static function getTemplate() {
        $output = '
                <#
                    var contentClass = (!_.isEmpty(data.class)) ? data.class : "";
                    var style = (!_.isEmpty(data.style)) ? data.style : "";
                    var autoplay = (!_.isEmpty(data.autoplay)) ? \' data-sppb-ride="sppb-carousel"\' : \' data-sppb-ride="sppb-carousel"\';
                    var arrows = (!_.isEmpty(data.arrows)) ? data.arrows : 0;
                    var controls = (!_.isEmpty(data.controls)) ? data.controls : 0;
                #>

		<div id="sppb-testimonial-pro-{{data.id}}" class="sppb-carousel sppb-testimonial-pro sppb-slide sppb-text-center {{contentClass}}"{{{autoplay}}}>

		<# if(controls > 0) { #>
			<ol class="sppb-carousel-indicators">
			<# _.each (data.sp_testimonialpro_item, function(slide_item, slide_key){
                            var activeClass = "";
                            if(slide_key == 0){
                                activeClass = " active";
                            } else {
                                activeClass = "";
                            }
                            #>
                            <li data-sppb-target="#sppb-carousel-{{data.id}}" class="{{activeClass}}" data-sppb-slide-to="{{slide_key}}"></li>
			<# })#>
			</ol>
		<# } #>

		<div class="sppb-carousel-inner">

		<# _.each (data.sp_testimonialpro_item, function (slide_item, slide_key){
                    var activeClass = "";
                    if(slide_key == 0){
                        activeClass = " active";
                    } else {
                        activeClass = "";
                    }
                    var avatar = "";
                    if((typeof slide_item.avatar !== "undefined") && (typeof slide_item.avatar.src !== "undefined")){
                        avatar = "src=" + slide_item.avatar.src;
                    } else {
                        avatar = "src=" + slide_item.avatar;
                    }
                #>
                    <div class="sppb-item {{activeClass}}">
                        <div class="col-sm-6">
                            <div class="sppb-testimonial-wrapper">
                            <#
                            var title = \'<strong class="pro-client-name">\' + slide_item.title + \'</strong>\';
                            if(slide_item.url){ title += \' - <span class="pro-client-url">\'+ slide_item.url +\'</span>\';}
                            #>
                            <div class="sppb-client-wrap">
                            <# if(slide_item.avatar){ #>
                            <img class="sppb-img-responsive sppb-avatar {{slide_item.avatar_style}}" {{avatar}} alt="{{slide_item.title}}">
                            <#}#>
                            <# if(title){ #>
                            <div class="sppb-testimonial-client">{{{title}}}</div>
                            <# } #>
                            </div>
                            <div class="sppb-testimonial-message">{{slide_item.message}}</div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="sppb-testimonial-wrapper">
                            <#
                            var title = \'<strong class="pro-client-name">\' + slide_item.title + \'</strong>\';
                            if(slide_item.url){ title += \' - <span class="pro-client-url">\'+ slide_item.url +\'</span>\';}
                            #>
                            <div class="sppb-client-wrap">
                            <# if(slide_item.avatar){ #>
                            <img class="sppb-img-responsive sppb-avatar {{slide_item.avatar_style}}" src="{{slide_item.avatar}}" alt="">
                            <#}#>
                            <# if(title){ #>
                            <div class="sppb-testimonial-client">{{{title}}}</div>
                            <# } #>
                            </div>
                            <div class="sppb-testimonial-message">{{slide_item.message}}</div>
                            </div>
                        </div>
                    </div>
		<# }) #>
		</div>
		<# if(arrows > 0) { #>
                    <a href="#sppb-testimonial-pro-{{data.id}}" class="left sppb-carousel-control" data-bs-slide="prev"><i class="fa fa-angle-left"></i></a>
                    <a href="#sppb-testimonial-pro-{{data.id}}" class="right sppb-carousel-control" data-bs-slide="next"><i class="fa fa-angle-right"></i></a>
		<# } #>
		</div>
                ';
        return $output;
    }

}
