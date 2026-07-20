/**
 * @package mod_spproperty_search
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */


jQuery(function ($) {

    /**
     * show or hide advance search fields
     */
    $('.spproperty-show-advance').on('click', function (e) {
        e.preventDefault();
        $(this).toggleClass("active")
        $('.spproperty-search-advance').slideToggle(500);
    });

    /**
     * init noUiSlider
     */


    if (ranger == 'range-slider') {
        let query = new URLSearchParams(window.location.search);
        if ($('.sp-property-search .spproperty-search .price-range').length) {
            let priceRanger = initNoUiSlider($, 'price', maxPrice, currency);
            if (query.get('min_price') !== null) {
                priceRanger.noUiSlider.set([query.get('min_price'), null]);
            }
            if (query.get('max_price') !== null) {
                priceRanger.noUiSlider.set([null, query.get('max_price')]);
            }
        }

        if ($('.sp-property-search .spproperty-search .size-range').length) {
            let sizeRanger = initNoUiSlider($, 'size', maxSize, measurement);
            if (query.get('minsize') !== null) {
                sizeRanger.noUiSlider.set([query.get('minsize'), null]);
            }
            if (query.get('maxsize') !== null) {
                sizeRanger.noUiSlider.set([null, query.get('maxsize')]);
            }
        }
    }

    $('.sp-property-search .spproperty-search').submit(function (e) {
        e.preventDefault();

        var search_queries = '';
        var searchitem = '&searchitem=' + 1;
        var rooturl = '';
        var menuid = '';
        var data = $(this).serializeArray();
        var p_features = $(this).find('#p_features').chosen().val();


        if (typeof p_features != 'undefined' && p_features != null && p_features.length > 0) {
            search_queries += '&p_features=' + p_features.join('-');
        }

        if (typeof data != 'undefined' && data.length) {
            data.forEach(function (value) {
                let v = value.value;
                if (v) {
                    if ((value.name != 'rooturl' && value.name != 'menuid' && value.name != 'p_features[]')) {
                        let _query = '&' + value.name + '=' + value.value;
                        search_queries += _query;
                    }
                }

                if (value.name == 'rooturl') {
                    rooturl = v;
                }
                if (value.name == 'menuid') {
                    menuid = v;
                }
            });
        }

        window.location = rooturl + 'index.php?option=com_spproperty&view=' + view + search_queries + searchitem + menuid + '';
        return false;
    });
});


var initNoUiSlider = function ($, vFor, maxVal, curr = '$') {
    var ranger = document.getElementById(vFor + '-range');
    noUiSlider.create(ranger, {
        connect: true,
        behaviour: 'tap',
        start: [0, Math.round(maxVal)],
        tooltips: [true, true],
        step: 1,
        range: {
            'min': [0],
            'max': [maxVal]
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

    ranger.noUiSlider.on('update', function (values, handle) {
        $('.sp-property-search .spproperty-search').find('#adv-min-' + vFor).val(values[0]);
        $('.sp-property-search .spproperty-search').find('#adv-max-' + vFor).val(values[1]);
        $('.sp-property-search .spproperty-search').find('.spproperty-' + vFor + '-range').html(values[0] + ' ' + curr + ' - ' + values[1] + ' ' + curr);
    });

    return ranger;
}
