jQuery(function ($) {

    //===================================================================
    // Init variables
    //===================================================================

    var markersBag = [];
    var default_center = $("#geo-search");


    //===================================================================
    // Init map
    //===================================================================

    var map = L.map('geomap').setView([default_center.data('lat'), default_center.data('lon')], 13);
    L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
        attribution: "",
        maxZoom: 18,
        id: 'mapbox.' + view,
        accessToken: token
    }).addTo(map);


    //===================================================================
    // Default map location marker
    //===================================================================

    var default_location = new L.LatLng(default_center.data('lat'), default_center.data('lon'));
    map.setView(default_location, 13);
    var default_marker = L.marker(default_location).addTo(map);
    let latlng = default_center.data('lat') + ',' + default_center.data('lon');
    const provider = new window.GeoSearch.OpenStreetMapProvider();
    provider.search({query: latlng})
        .then(function (response) {
            default_marker.bindPopup(response[0].label).openPopup();
            $("#geo-search").val(response[0].label);
        });
    markersBag.push(default_marker);


    $(document).mouseup(function (e) {
        var container = $(".show-location-list, #geo-search");
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $('.show-location-list').hide();
        }
    });

    //===================================================================
    // Search location from place name or keyup
    //===================================================================

    $(document).on('keyup', '#geo-search', function (e) {
        $(".show-location-list").show();
        var queryVal = $(this).val();
        const provider = new  window.GeoSearch.OpenStreetMapProvider();
        provider.search({query: queryVal})
        .then(function (result) {
            $list = $(".show-location-list");
            $list.empty();
            if (result.length > 0) {
                for (let i = 0; i < result.length; i++) {
                    let raw = result[i].raw;
                    let index = result[i];
                    $list.append($("<li>", {
                        "data-info": index.y + "|" + index.x + "|" + index.label
                    })
                    .append($("<img>", {
                        src: raw.icon
                        }))
                        .append($("<span>", {
                            text: index.label
                        })));
                }
            }
        });
    });


    //===================================================================
    // Select a location by clicking the name
    //===================================================================

    $(document).on('click', '.show-location-list li', function (event) {
        event.preventDefault();

        // Clear previous
        $(".show-location-list").hide();
        clearMarkers(map);

        let info = $(this).data("info").split("|");
        let location  = new L.LatLng(info[0], info[1]);
        map.setView(location, 13);
        let searchMarker = L.marker(location).addTo(map);
        searchMarker.bindPopup(info[2]).openPopup();
        map.setZoom(13);
        markersBag.push(searchMarker);

        $("#geo-search").val(info[2]);
        $("#geo-location").val(info[0] + ", " + info[1]);
        $(this).parent().empty();
    });


    //===================================================================
    // Clear markers
    //===================================================================

    function clearMarkers(map)
    {
        for (let i = 0; i < markersBag.length; i++) {
            map.removeLayer(markersBag[i]);
        }
    }

});