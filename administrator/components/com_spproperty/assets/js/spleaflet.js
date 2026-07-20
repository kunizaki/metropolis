//===================================================================
// Leaflet.js
// Init map and add markers
//===================================================================

( function ($) {
    $.fn.spleaflet = function (options) {

    //===================================================================
    // Init settings/options
    //===================================================================
        var options = $.extend({
            markers: [],
            token: '',
            view: '',
            map: 'map',
            zoom: 13,
            maxZoom: 50,
            openPopup: false,
        }, options);

    //===================================================================
    // Apply plugin functionality
    //===================================================================
        return this.each(function () {
            var markers  = options.markers;
            var token   = options.token;
            var view    = options.view;
            var popup   = options.popup;
            var map     = options.map;
            var zoom    = options.zoom;
            var maxZoom = options.maxZoom;
            var openPopup = options.openPopup;

            var mymap = L.map(map).setView([markers[0].lat, markers[0].lon], zoom);
            L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
                attribution: "Map data &copy; <a href=https://www.openstreetmap.org/'>OpenStreetMap</a> contributors, <a href='https://creativecommons.org/licenses/by-sa/2.0/'>CC-BY-SA</a>, Imagery © <a href='https://www.mapbox.com/'>Mapbox</a>",
                maxZoom: maxZoom,
                id: 'mapbox/streets-v11',
                accessToken: token
            }).addTo(mymap);

            for (let i = 0; i < markers.length; i++) {
                let marker = L.marker([markers[i].lat, markers[i].lon]);
                marker.addTo(mymap);
                if (openPopup) {
                    marker.bindPopup(markers[i].text).openPopup();
                } else {
                    marker.bindPopup(markers[i].text);
                }
            }
        });
    }
})(jQuery);