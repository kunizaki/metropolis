/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */


jQuery(function ($) {
    window.addEventListener( 'load', function () {

      // get locations
        var agntLocations = $('#spproperty-agent-map').data('locations');

      //first item location
        var first_location = agntLocations[0].location;
        var first_location = first_location.split(',');

      // make array of locations
        var locations = [];
        $.each(agntLocations, function (i, v) {
            var location_split = v.location.split(',');
            locations.push([v.title, location_split[0], location_split[1], i]);
        });

        var map = new google.maps.Map(document.getElementById('spproperty-agent-map'), {
            zoom: 3,
            center: new google.maps.LatLng(first_location[0], first_location[1]),
            scrollwheel: false,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        var infowindow = new google.maps.InfoWindow();

      // create marker
        var marker, i;
        for (i = 0; i < locations.length; i++) {
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                map: map
            });

            google.maps.event.addListener(marker, 'click', (function (marker, i) {
                  return function () {
                    infowindow.setContent(locations[i][0]);
                    infowindow.open(map, marker);
                  }
            })(marker, i));
        }

      //styles
        var styles = [
        {
            "featureType": "water",
            "elementType": "geometry.fill",
            "stylers": [
            {
                "color": "#d3d3d3"
            }
            ]
        },
        {
            "featureType": "transit",
            "stylers": [
                {
                    "color": "#808080"
            },
                {
                    "visibility": "off"
            }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "visibility": "on"
            },
                {
                    "color": "#b3b3b3"
            }
            ]
        },
        {
            "featureType": "road.highway",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#ffffff"
            }
            ]
        },
        {
            "featureType": "road.local",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "visibility": "on"
            },
                {
                    "color": "#ffffff"
            },
                {
                    "weight": 1.8
            }
            ]
        },
        {
            "featureType": "road.local",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#d7d7d7"
            }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "visibility": "on"
            },
                {
                    "color": "#ebebeb"
            }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "geometry",
            "stylers": [
                {
                    "color": "#a7a7a7"
            }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#ffffff"
            }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#ffffff"
            }
            ]
        },
        {
            "featureType": "landscape",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "visibility": "on"
            },
                {
                    "color": "#efefef"
            }
            ]
        },
        {
            "featureType": "road",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "color": "#696969"
            }
            ]
        },
        {
            "featureType": "administrative",
            "elementType": "labels.text.fill",
            "stylers": [
                {
                    "visibility": "on"
            },
                {
                    "color": "#737373"
            }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "off"
            }
            ]
        },
        {
            "featureType": "poi",
            "elementType": "labels",
            "stylers": [
                {
                    "visibility": "off"
            }
            ]
        },
        {
            "featureType": "road.arterial",
            "elementType": "geometry.stroke",
            "stylers": [
                {
                    "color": "#d6d6d6"
            }
            ]
        },
        {
            "featureType": "road",
            "elementType": "labels.icon",
            "stylers": [
                {
                    "visibility": "off"
            }
            ]
        },
        {},
        {
            "featureType": "poi",
            "elementType": "geometry.fill",
            "stylers": [
                {
                    "color": "#dadada"
            }
            ]
        }

        ]; // END gmap styles

      // Set styles to map
        map.setOptions({styles: styles});



    });


});