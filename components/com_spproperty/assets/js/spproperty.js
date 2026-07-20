/**
 * @package     SP Property
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

jQuery(function ($) {
    'use strict';

    if ($("#spproperty-slider").length) {
        // properety slider
        $('#spproperty-slider').owlCarousel({
            loop: true,
            center: true,
            margin: 0,
            autoplay: true,
            autoplayTimeout: 8000,
            smartSpeed: 700,
            dots: false,
            nav: true,
            navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
            navElement: 'div',
            responsiveClass: true,
            responsive: {
                0: {
                    items: 1
                },
                600: {
                    items: 2
                },
                1000: {
                    items: 3
                }
            }
        });
    }

    $('#spproperty-slider-alt').owlCarousel({
        items: 1,
        dots: true,
        dotsData: true,
        dotsContainer: '.property-dots-container',
        nav: true,
        navText: ['<i class="fa fa-angle-left"></i>', '<i class="fa fa-angle-right"></i>'],
        navElement: 'div',
        loop: true,
        center: true,
        margin: 0,
        autoplay: true,
        autoplayTimeout: 8000,
        smartSpeed: 700
    });

    //Ajax property visit request form
    $('.spproperty-widget-form').on('submit', function (event) {
        event.preventDefault();
        var $this = $(this);

        var $self = $(this);
        var value = $(this).serializeArray();
        var request = {
            'option': 'com_spproperty',
            'task': 'ajax',
            'data': value
        };

        $.ajax({
            type: 'POST',
            url: spproperty_url + "&task=properties.booking",
            data: value,
            beforeSend: function () {
                $self.addClass('booking-proccess');
                $self.children('.spproperty-req-submit').prepend('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (response) {
                var data = $.parseJSON(response);

                if (data.status) {
                    $self.removeClass('booking-proccess').addClass('booked');
                    $self.children('.spproperty-req-submit').children('.fa-spinner').remove();
                    $self.children('.spproperty-req-submit').prop('disabled', true);
                    $self.next('.spproperty-req-status').html('<p class="pbooking-success">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                    $self.trigger('reset');
                } else {
                    $self.next('.spproperty-req-status').html('<p class="pbooking-error">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                }
            }
        });
    });

    function checkPid($json, $pid)
    {
        for (let i = 0; i < $json.length; i++) {
            if ($json[i].property_id == $pid) {
                return true;
            }
        }
        return false;
    }

    //Check for previous request
    var requestChk = localStorage.getItem('priceRequest');
    if (requestChk != undefined && requestChk != '') {
        requestChk = JSON.parse(requestChk);
        let pid = $('.request-title').data('pid');
        if (checkPid(requestChk, pid)) {
            $('.request-title').parent().parent().prop('disabled', true).css('cursor', 'not-allowed');
            $('.request-title').html('Request Sent');
        }
    }

    //Ajax property price request form
    $('.spproperty-widget-form-request').on('submit', function (event) {
        event.preventDefault();
        var $this = $(this);

        var $self = $(this);
        var value = $(this).serializeArray();
        var request = {
            'option': 'com_spproperty',
            'task': 'ajax',
            'data': value
        };
        var dataObj = {};
        $(value).each(function (i, val) {
            dataObj[val.name] = val.value;
        });

        var storeObject = {
            property_id : dataObj.pid,
            user_email  : dataObj.email
        };
        $('.spproperty-widget-form-request .content-body').hide();
        $('.spproperty-display-tick').show();

        $.ajax({
            type: 'POST',
            url: spproperty_url + "&task=properties.booking",
            data: value,
            beforeSend: function () {
                $self.addClass('booking-proccess');
                $self.find('.spproperty-req-submit-price').prepend('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (response) {

                var data = $.parseJSON(response);

                if (data.status) {
                    $self.removeClass('booking-proccess').addClass('booked');
                    $self.trigger('reset');
                    var store = JSON.parse(localStorage.getItem('priceRequest')) || [];
                    store.push(storeObject);
                    localStorage.setItem('priceRequest', JSON.stringify(store));
                    $('.request-title').html('Request Sent');
                    $('.request-title').parent().parent().prop('disabled', true).css('cursor', 'not-allowed');
                    $('.circle-loader').toggleClass('load-complete');
                    $('.checkmark').toggle();
                    setTimeout(function () {
                        $("#request-for-price-form").modal('hide');
                        $('.spproperty-widget-form-request .content-body').show();
                        $('.spproperty-display-tick').hide();
                        $('.circle-loader').toggleClass('load-complete');
                        $('.checkmark').toggle();
                    }, 1000);
                } else {
                    $self.find('.spproperty-req-status-price').html('<p class="pbooking-error">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                }
            }
        });
    });


    //Ajax Agent contact form
    $('.spproperty-agent-form').on('submit', function (event) {
        event.preventDefault();
        var $this = $(this);

        var $self = $(this);
        var value = $(this).serializeArray();
        var request = {
            'option': 'com_spproperty',
            'task': 'ajax',
            'data': value
        };

        $.ajax({
            type: 'POST',
            url: spproperty_url + "&task=agents.contact",
            format: 'json',
            data: value,
            beforeSend: function () {
                $self.addClass('contact-proccess');
                $self.find('#contact-submit').prepend('<i class="fa fa-spinner fa-spin"></i>');
            },
            success: function (response) {

                var data = $.parseJSON(response);

                if (data.status) {
                    $self.removeClass('contact-proccess').addClass('sent');
                    $self.find('#contact-submit').children('.fa-spinner').remove();
                    $self.find('#contact-submit').prop('disabled', true);
                    $self.next('.spproperty-cont-status').html('<p class="contact-sent">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                    $self.trigger('reset');
                } else {
                    $self.next('.spproperty-cont-status').html('<p class="contact-error">' + data.content + '</p>').fadeIn().delay(7000).fadeOut(500);
                }
            }
        });
    });

    $('.spproperty-gallery').owlCarousel({
        items: 1,
        dotsEach: 1,
        nav: true,
        navText: [
            '<i class="fa fa-angle-left"></i>',
            '<i class="fa fa-angle-right"></i>'
        ],
        loop: true
    });

    //Add to favorite
    $(document).on('submit', '.property-fav-form', function (e) {
        e.preventDefault();

        let $this              = $(this);
        let $heart             = $this.find('.fav-heart');
        let spproperty_url     = $this.find('.property-fav-url').val();
        let $property_fav_flag = $this.find('.property_fav_flag');
        let data               = $this.serializeArray();

       //Add to fagvourite ajax request
        let url = spproperty_url + '&task=properties.addToFavourite';

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            beforeSend: function () {

            },
            success: function (response) {
                if (response) {
                    $heart.toggleClass('fa-heart fa-heart-o');
                    let flag_value = $property_fav_flag.val();
                    if (flag_value == 0) {
                        $property_fav_flag.val(1);
                    } else {
                        $property_fav_flag.val(0);
                    }
                }
            },
            error: function (err) {
                console.error(err);
            }
        })

    });


    // tab selection in property view
    $(".spproperty-floor-tab-nav li a").click(function (e) {
        e.preventDefault();

        var layoutId = $(this).attr('href');

        $('.active').removeClass('active');
        $('.show').removeClass('show');

        $(layoutId).addClass('active show')

    })

});