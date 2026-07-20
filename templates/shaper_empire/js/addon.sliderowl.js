/**
* @package Helix3 Framework
* @author JoomShaper https://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2023 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

jQuery(document).ready(function($){'use strict';
    
    // Full width Slideshow
    var $spslideowl = $('#sp-slide-owl');

    // Autoplay
    var $autoplay   = $spslideowl.attr('data-sppb-slide-ride');
    if ($autoplay == 'true') { var $autoplay = true; } else { var $autoplay = false};

    // controllers
    var $controllers   = $spslideowl.attr('data-sppb-slide-controllers');
    if ($controllers == 'true') { var $controllers = true; } else { var $controllers = false};


    $spslideowl.owlCarousel({
        loop: true,
        margin:0,
        touchDrag  : false,
        mouseDrag  : false,
        autoplay: $autoplay,
        animateIn: 'fadeIn',
        animateOut: 'fadeOut',
        items:2,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 1
            },
            1000: {
                items: 2
            }
        },
        dots: $controllers,
    });


    $('.sppbSlidePrev').click(function(){
        $spslideowl.trigger('prev.owl.carousel', [400]);
    });

    $('.sppbSlideNext').click(function(){
        $spslideowl.trigger('next.owl.carousel',[400]);
    });

});

