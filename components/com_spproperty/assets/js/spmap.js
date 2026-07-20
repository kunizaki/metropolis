/**
 * @package com_spproperty
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */


var map; 
var indicators  = [];
var hasVisible = [];
var LCG, GCG;
var _markers = [];
var ajaxSuccess = false;
var _data = null;
var infoWindow;

jQuery(function ($) {

    if (mapType == 'google') {
        var center = {
            lat: 39.964657,
            lng: -102.239510
        };

        if (markers.length > 0) {
            center = markers[0].latlng;
        }
        var data = {
            center: center,
            zoom: parseInt(zoomValue)
        };
        google.maps.event.addListener(window, 'load', initMap(data));
    } else {
        var center = {
            lat: 39.964657,
            lng: -102.239510
        };

        if (markers.length > 0) {
            center = markers[0].latlng;
        }
        var data = {
            center: center,
            zoom: parseInt(zoomValue)
        };
        initMap(data);
    }

    //Toggle list or grid view
    var $list_view = $('#spproperty-map-container .spproperty-list-view');
    var $grid_view = $('#spproperty-map-container .spproperty-grid-view');
    if (localStorage.getItem('gridView') == undefined) {
        localStorage.setItem('gridView', 'true');
    }

    if (localStorage.getItem('gridView') == 'true') {
        $grid_view.addClass('active-view');
        $list_view.removeClass('active-view');
        loadData();
    }
    if (localStorage.getItem('gridView') == 'false') {
        $list_view.addClass('active-view');
        $grid_view.removeClass('active-view');
        loadData();
    }

    $list_view.on('click', function (e) {
        e.preventDefault();
        $this = $(this);
        if (localStorage.getItem('gridView') == 'true') {
            localStorage.setItem('gridView', 'false');
            $list_view.addClass('active-view');
            $grid_view.removeClass('active-view');
            if (mapType == 'google') {
                addMarkers(markers);
                renderProperty();
            } else {
                loadData();
            }
        }
    });

    $grid_view.on('click', function (e) {
        e.preventDefault();
        $this = $(this);
        if (localStorage.getItem('gridView') == 'false') {
            localStorage.setItem('gridView', 'true');
            $grid_view.addClass('active-view');
            $list_view.removeClass('active-view');
            if (mapType == 'google') {
                addMarkers(markers);
                renderProperty();
            } else {
                loadData();
            }
        }
    });

});

var initMap = function (data) {
    if (mapType == 'google') {
        map = new google.maps.Map(document.getElementById('spproperty-map'), {
            center: data.center,
            zoom: data.zoom,
            tilt: 45,
            mapId: "DEMO_MAP_ID"
        });
    } else {
        map = L.map(document.getElementById('spproperty-map'), {
            scrollWheelZoom: false
        }).setView([data.center.lat, data.center.lng], data.zoom);
        L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {
            attribution: "Map data &copy; <a href=https://www.openstreetmap.org/'>OpenStreetMap</a> contributors, <a href='https://creativecommons.org/licenses/by-sa/2.0/'>CC-BY-SA</a>, Imagery © <a href='https://www.mapbox.com/'>Mapbox</a>",
            id: 'mapbox/streets-v11',
            accessToken: mapboxToken
        }).addTo(map);
    }

}



var loadData = function () {
    if (mapType == 'google') {
        addMarkers(markers);
        google.maps.event.addListener(map, 'idle', function () {
            renderProperty();
        });
    } else {
        addMarkers(markers);
        renderProperty();
        map.on('moveend', function () {
            renderProperty();
        });
    }
}

var renderProperty = function () {
    let vMarkers = visibleMarkers();
    let properties = [];
    vMarkers.forEach(function (value) {
        properties.push(value.property.id);
    });
    getPropertyWithinViewportByAjax(properties, localStorage.getItem('gridView'));
}

var getPropertyWithinViewportByAjax = function (properties, gView = 'true') {
    jQuery.ajax({
        type: 'post',
        url: host + '&task=properties.getSelectedProperties',
        dataType: 'json',
        data: {
            'properties': properties,
            'gridView': gView,
            'columns': columns
        },
        beforeSend: function () {
            jQuery('#sp-property-properties-map').empty();
            jQuery('.map-content-loader').show();

        },
        success: function (response) {
            ajaxSuccess = true;
            _data = response;
        },
        complete: function () {
            if (ajaxSuccess && _data != null) {
                jQuery('.map-content-loader').hide();
                jQuery('#sp-property-properties-map').append(_data);
                jQuery('.spproperty-gallery').owlCarousel({
                    items: 1,
                    dotsEach: 1,
                    nav: true,
                    navText: [
                        '<i class="fa fa-angle-left"></i>',
                        '<i class="fa fa-angle-right"></i>'
                    ],
                    loop: true
                });
                ajaxSuccess = false;
                _data = null;
            }
            _data = null;

        },
        error: function (err) {
            console.error(err);
        }
    });
}

var addMarkers = function (markers) {
    if (mapType == 'google') {
        var local_markers = [];
        // Create an array of Promises representing the asynchronous createMarker tasks
        var markerPromises = markers.map(async function (value, index) {
            let marker = await createMarker(value, map);
            local_markers.push(marker);
            _markers.push(marker);
            let infoWindow = createInfoWindow(value.info);
            marker.addListener('click', function () {
                infoWindow.open(map, marker);
            });
            let markerInfo = {};
            markerInfo.marker = marker;
            markerInfo.property = value;
            indicators.push(markerInfo);
        });

        // Wait for all the createMarker tasks to complete
        Promise.all(markerPromises)
            .then(() => {
                // Once all markers are created, create the MarkerClusterer
                GCG = new markerClusterer.MarkerClusterer({
                    markers: local_markers,
                    map: map,
                    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                });
            })
            .catch(error => {
                console.error('Error creating markers:', error);
            });

    } else {
        LCG = L.markerClusterGroup({
            maxClusterRadius: 120,
            iconCreateFunction: function (cluster) {
                let childCount = cluster.getChildCount();
                let className = ' marker-cluster-';
                if (childCount <= 10) {
                    className += 'small';
                } else if (childCount <= 50) {
                    className += 'medium';
                } else {
                    className += 'large';
                }
                return new L.DivIcon({
                    html: '<div><span>' + childCount + '</span></div>',
                    className: 'marker-cluster' + className,
                    iconSize: new L.Point(40, 40)
                    });
            }
        });
        markers.forEach(function (value, index) {
            let markerImage = baseUrl + 'components/com_spproperty/assets/images/custom-marker-1.png';
            let marker;
            if (value.price != '') {
                marker = L.marker([value.latlng.lat, value.latlng.lng], {
                    icon: new L.divIcon({
                        className: 'sp-marker-icon',
                        html: "<img src='" + markerImage + "' class='sp-marker-image'> <span class='marker-text'>" + value.price + "</span>"
                    })
                });
            } else {
                marker = L.marker([value.latlng.lat, value.latlng.lng], {
                    icon: new L.divIcon({
                        className: 'sp-marker-icon',
                        html: "<img src='" + markerImage + "' class='sp-marker-image'> <span class='marker-text'>" + value.curr + "</span>"
                    })
                });
            }

            marker.bindPopup(value.info);

            LCG.addLayer(marker);

            let markerInfo = {};
            markerInfo.marker = marker;
            markerInfo.property = value;
            indicators.push(markerInfo);
        });
        map.addLayer(LCG);
    }
}

var visibleMarkers = function () {
    if (mapType == 'google') {
        let bounds;
        bounds = map.getBounds();

        hasVisible = [];
        indicators.forEach(function (value) { 
            let coordinate = new google.maps.LatLng(value.marker.position.lat, value.marker.position.lng)
            if (bounds.contains(coordinate)) {
                hasVisible.push(value);
            }
        });
    } else {
        let bounds = map.getBounds();
        hasVisible = [];
        indicators.forEach(function (value) {
            let marker = value.marker.getLatLng();
            if (bounds.contains(marker)) {
                hasVisible.push(value);
            }
        });
    }

    return hasVisible;
}


var createMarker = async function (marker, map) {
    // Request needed libraries.
    const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary(
        "marker"
    );
    let markerIcon = {
        url: baseUrl + 'components/com_spproperty/assets/images/custom-marker-1.png',
        scaledSize: new google.maps.Size(60,30)
    };

    const labelText    = marker.price ? marker.price : marker.curr;
    const priceTag     = document.createElement("div");
    priceTag.className = "sp-marker-icon";
    priceTag.innerHTML = `<img src="${markerIcon.url}" class="sp-marker-image"> <span class="marker-text">${labelText}</span>`;


    const indicator = new google.maps.marker.AdvancedMarkerElement({
        position: marker.latlng,
        map: map,
        title: marker.title,
        content: priceTag
    });


    return indicator;
}

var createInfoWindow = function (content, map) {
    iw = new google.maps.InfoWindow({
        content: content
    });
    google.maps.event.addListener(iw, 'domready', function () {
        var iwOuter = jQuery('.gm-style-iw');
        var iwBackground = iwOuter.prev();
        iwBackground.children(':nth-child(2)').css({
            'display': 'none'
        });
        iwBackground.children(':nth-child(4)').css({
            'display': 'none'
        });
        iwBackground.children(':nth-child(1)').attr('style', function (i, s) {
            return s + 'left: 76px !important;'
        });
        iwBackground.children(':nth-child(3)').attr('style', function (i, s) {
            return s + 'left: 76px !important;'
        });
        iwBackground.children(':nth-child(3)').find('div').children().css({
            'box-shadow': 'rgba(72, 181, 233, 0.6) 0px 1px 6px',
            'z-index': '1'
        });

        var iwCloseBtn = iwOuter.next();
        var iwCloseBtnImg = iwCloseBtn.find("img");
        iwCloseBtn.css({
            opacity: '1',
            top: '22px',
            right: '60px',
            width: '30px',
            height: '30px',
            background: '#fff',
            boxShadow: '0px 0px 3px #c1c1c1'
        });
        iwCloseBtnImg.css({
            margin: "9px"
        });
        iwCloseBtn.mouseout(function () {
            jQuery(this).css({
                opacity: '1'
            });
        });
    });
    return iw;
}