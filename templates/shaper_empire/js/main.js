/**
 * @package Helix3 Framework
 * @template Shaper Empire
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
jQuery(function ($) {

    $('#offcanvas-toggler').on('click', function (event) {
        event.preventDefault();
        $('body').addClass('offcanvas');
    });

    $('<div class="offcanvas-overlay"></div>').insertBefore('.body-innerwrapper > .offcanvas-menu');

    $('.close-offcanvas, .offcanvas-overlay').on('click', function (event) {
        event.preventDefault();
        $('body').removeClass('offcanvas');
    });

    var stopBubble = function (e) {
        e.stopPropagation();
        return true;
    };

    //Mega Menu
    $('.sp-megamenu-wrapper').parent().parent().css('position', 'static').parent().css('position', 'relative');
    $('.sp-menu-full').each(function () {
        $(this).parent().addClass('menu-justify');
    });

    //Sticky Menu
    $(document).ready(function () {
        $("body.sticky-header").find('#sp-header').sticky({topSpacing: 0})
    });

    //Tooltip
	var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
	var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl);
	});

    $(document).on('click', '.sp-rating .star', function (event) {
        event.preventDefault();

        var data = {
            'action': 'voting',
            'user_rating': $(this).data('number'),
            'id': $(this).closest('.post_rating').attr('id')
        };

        var request = {
            'option': 'com_ajax',
            'plugin': 'helix3',
            'data': data,
            'format': 'json'
        };

        $.ajax({
            type: 'POST',
            data: request,
            beforeSend: function () {
                $('.post_rating .ajax-loader').show();
            },
            success: function (response) {
                var data = $.parseJSON(response.data);

                $('.post_rating .ajax-loader').hide();

                if (data.status == 'invalid') {
                    $('.post_rating .voting-result').text('You have already rated this entry!').fadeIn('fast');
                } else if (data.status == 'false') {
                    $('.post_rating .voting-result').text('Somethings wrong here, try again!').fadeIn('fast');
                } else if (data.status == 'true') {
                    var rate = data.action;
                    $('.voting-symbol').find('.star').each(function (i) {
                        if (i < rate) {
                            $(".star").eq(-(i + 1)).addClass('active');
                        }
                    });

                    $('.post_rating .voting-result').text('Thank You!').fadeIn('fast');
                }

            },
            error: function () {
                $('.post_rating .ajax-loader').hide();
                $('.post_rating .voting-result').text('Failed to rate, try again!').fadeIn('fast');
            }
        });
    });

    // testimonial pro
    $('.sppb-testimonial-pro .sppb-item').each(function () {
        var next = $(this).next();
        if (!next.length) {
            next = $(this).siblings(':first');
        }
        //next.children(':first-child').clone().appendTo($(this));

        if (next.next().length > 0) {
            next.next().children(':first-child').clone().appendTo($(this));
        } else {
            $(this).siblings(':first').children(':first-child').clone().appendTo($(this));
        }
    });

});

//For react template
jQuery(function ($) {
    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            var newNodes = mutation.addedNodes;
            if (newNodes !== null) {
                var $nodes = $(newNodes);

                $nodes.each(function () {
                    var $node = $(this);
                    $node.find('#slider').each(function () {
                        //Thumb Slider
                        if ($('#carousel').is('.flexslider')) {

                            // Thumb Gallery
                            var $sppbTgOptions = $('.sppb-tg-slider');

                            // Autoplay
                            var $autoplay = $sppbTgOptions.data('sppb-tg-autoplay');

                            // arrows
                            var $arrows = $sppbTgOptions.data('sppb-tg-arrows');

                            $('#carousel').flexslider({
                                animation: 'slide',
                                controlNav: true,
                                directionNav: $arrows,
                                animationLoop: false,
                                slideshow: $autoplay,
                                itemWidth: 320,
                                itemMargin: 0,
                                asNavFor: '#slider'
                            });

                            $('#slider').flexslider({
                                animation: "fade",
                                controlNav: false,
                                directionNav: $arrows,
                                animationLoop: false,
                                slideshow: $autoplay,
                                sync: "#carousel"
                            });

                        }
                        // END:: Thumb Slider

                    });
                });
            }
        });
    });

    var config = {
        childList: true,
        subtree: true
    };
    // Pass in the target node, as well as the observer options
    observer.observe(document.body, config);
});
