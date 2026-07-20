/**
 * @package mod_spproperty_properties
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

jQuery(function ($) {
   //Favorite check
    $(document).on('submit', '.property-fav-form-mod', function (e) {
        e.preventDefault();

        let $this = $(this);
        let $heart = $this.find('.fav-heart');
        let spproperty_url = $this.find('.property-fav-url').val();
        let $property_fav_flag = $this.find('.property_fav_flag');
        let data = $this.serializeArray();

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
});