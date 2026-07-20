/**
 * @package     SP Property Search
 * @subpackage  mod_sppropertysearch
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
jQuery(function ($) {

    /**
     * show or hide advance search fields
     */
    $('.spproperty-show-advance').on('click', function (e) {
        e.preventDefault();
        $('.spproperty-search-advance').slideToggle('slow');
    });

    /**
     * init noUiSlider
     */
    if (ranger == 'range-slider') {
        initNoUiSlider($);
    }

    $('.sp-property-search .spproperty-search').submit(function (e) {
        e.preventDefault();

        var rooturl     = $('.spproperty-search #url').val();
        var menuid      = $('.spproperty-search #menuid').val();
        var keyword     = $('.spproperty-search #keyword').val();
        var city        = $('.spproperty-search #city').val();
        var pstatus     = $('.spproperty-search #pstatus').val();
        var minsize     = $('.spproperty-search #min-size').val();
        var maxprice    = $('.spproperty-search #max-price').val();

        var parking     = $('.spproperty-search #parking').val();
        var zipcode     = $('.spproperty-search #zip-code').val();
        var category    = $('.spproperty-search #category').val();
        var min_price   = $('.spproperty-search #min-price').val();
        var max_price = $('.spproperty-search #max-price').val();
        var search_queries = '';
        var data = $(this).serializeArray();
        console.log(data); return;

        var searchitem     = '&searchitem=' + 1;

        if (keyword) {
            keyword = '&keyword=' + keyword;
            search_queries += keyword;
        }

        if (city) {
            city = '&city=' + city;
            search_queries += city;
        }

        if (pstatus) {
            pstatus = '&pstatus=' + pstatus;
            search_queries += pstatus;
        }

        if (minsize) {
            minsize = '&minsize=' + minsize;
            search_queries += minsize;
        }

        if (max_price) {
            max_price = '&max_price=' + max_price;
            search_queries += max_price;
        }

        window.location = rooturl + 'index.php?option=com_spproperty&view=' + view + search_queries + searchitem + menuid + '';

        return false;
    });


    $('.sp-property-search .spproperty-adv-search').submit(function (e) {
        e.preventDefault();

        var rooturl     = $('.spproperty-adv-search #url').val();
        var menuid      = $('.spproperty-adv-search #menuid').val();
        var keyword     = $('.spproperty-adv-search #keyword').val();
        var city        = $('.spproperty-adv-search #city').val();
        var pstatus     = $('.spproperty-adv-search #pstatus').val();
        var minsize     = $('.spproperty-adv-search #adv-min-size').val();
        var maxsize     = $('.spproperty-adv-search #adv-max-size').val();
        var beds        = $('.spproperty-adv-search #beds').val();
        var baths       = $('.spproperty-adv-search #baths').val();

        var parking     = $('.spproperty-adv-search #parking').val();
        var zipcode     = $('.spproperty-adv-search #zip-code').val();
        var category    = $('.spproperty-adv-search #category').val();
        var min_price   = $('.spproperty-adv-search #adv-min-price').val();
        var max_price   = $('.spproperty-adv-search #adv-max-price').val();
        var p_features = [];
        var search_queries = '';
        //Handles property features
        var property_features = '';
        p_features = $('#p_features').chosen().val();

        if (typeof p_features !== 'undefined' && p_features != null && p_features.length > 0) {
            p_features = p_features.join('-');
        }

        var searchitem     = '&searchitem=' + 1;

        if (keyword) {
            keyword = '&keyword=' + keyword;
            search_queries += keyword;
        }

        if (city) {
            city = '&city=' + city;
            search_queries += city;
        }

        if (pstatus) {
            pstatus = '&pstatus=' + pstatus;
            search_queries += pstatus;
        }

        if (minsize) {
            minsize = '&minsize=' + (minsize);
            search_queries += minsize;
        }

        if (maxsize) {
            maxsize = '&maxsize=' + (maxsize);
            search_queries += maxsize;
        }

        if (beds) {
            beds = '&beds=' + beds;
            search_queries += beds;
        }

        if (baths) {
            baths = '&baths=' + baths;
            search_queries += baths;
        }

        if (parking) {
            parking = '&parking=' + parking;
            search_queries += parking;
        }

        if (zipcode) {
            zipcode = '&zipcode=' + zipcode;
            search_queries += zipcode;
        }

        if (category) {
            category = '&catid=' + category;
            search_queries += category;
        }

        if (min_price) {
            min_price = '&min_price=' + min_price;
            search_queries += min_price;
        }

        if (max_price) {
            max_price = '&max_price=' + max_price;
            search_queries += max_price;
        }

        if (p_features) {
            property_features = '&p_features=' + p_features;
            search_queries += p_features;
        }

        window.location = rooturl + 'index.php?option=com_spproperty&view=' + view + search_queries + searchitem + menuid + '';

        return false;
    });

});

var initNoUiSlider = function ($) {
    var priceRange = document.getElementById('price-range');
    noUiSlider.create(priceRange, {
        connect: true,
        behaviour: 'tap',
        start: [0, Math.round(maxPrice)],
        tooltips: [true, true],
        step: 1,
        range: {
            'min': [0],
            'max': [maxPrice]
        },
        format: {
            to: function (value) {
                return parseInt(value, 10);
            },
            from: function (value) {
                return value;
            }
        }
    });

    priceRange.noUiSlider.on('update', function (values, handle) {
        $('#adv-min-price').val(values[0]);
        $('#adv-max-price').val(values[1]);
        $('.spproperty-price-range').html('( ' + values[0] + ' ' + currency + ' - ' + values[1] + ' ' + currency + ' )');
    });

    var sizeRange = document.getElementById('size-range');
    noUiSlider.create(sizeRange, {
        connect: true,
        behaviour: 'tap',
        start: [0, Math.round(maxSize)],
        tooltips: [true, true],
        step: 10,
        range: {
            'min': [0],
            'max': [maxSize]
        },
        format: {
            to: function (value) {
                return parseInt(value, 10);
            },
            from: function (value) {
                return value;
            }
        }
    });

    sizeRange.noUiSlider.on('update', function (values, handle) {
        $('#adv-min-size').val(values[0]);
        $('#adv-max-size').val(values[1]);
        $('.spproperty-size-range').html('( ' + values[0] + ' ' + measurement + ' - ' + values[1] + ' ' + measurement + ' )');
    });


    let query = new URLSearchParams(window.location.search);
    if (query.get('minsize') !== null) {
        sizeRange.noUiSlider.set([query.get('minsize'), null]);
    }
    if (query.get('maxsize') !== null) {
        sizeRange.noUiSlider.set([null, query.get('maxsize')]);
    }

    if (query.get('min_price') !== null) {
        priceRange.noUiSlider.set([query.get('min_price'), null]);
    }
    if (query.get('max_price') !== null) {
        priceRange.noUiSlider.set([null, query.get('max_price')]);
    }
}
