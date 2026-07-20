var _markers = [];
function initOpenStreetMap(t)
{
    var d;
    jQuery(".sppb-addon-openstreetmap", t).each(function (t) {
        var a, o = jQuery(this).attr("id"),
            r = jQuery(this).attr("data-mapzoom"),
            i = 1 === Number(jQuery(this).attr("data-zoomcontrol")),
            e = 1 === Number(jQuery(this).attr("data-dragging")),
            n = 1 === Number(jQuery(this).attr("data-mousescroll")),
            s = 1 === Number(jQuery(this).attr("data-attribution")),
            p = jQuery(this).attr("data-mapstyle"),
            m = JSON.parse(jQuery(this).attr("data-location")),
            u = m[0].latitude,
            l = m[0].longitude;

        let _markers = [];
        m.forEach(function (v) {
            let obj = {};
            obj.text = v.address;
            obj.lat = v.latitude;
            obj.lon = v.longitude;
            _markers.push(obj);
        });

        jQuery(document).spleaflet({
            map: o,
            view: 'streets',
            token: 'pk.eyJ1Ijoic2FqZWViMDdhaGFtZWQiLCJhIjoiY2prdGRuYXp0MDR0aTNxbWcyM3NhdWEyeiJ9.7n8l9YM67ufOgz8VVjWgaw',
            markers: _markers
        });

    });

    // var LCG = L.markerClusterGroup();
    // _markers.forEach(function (value) {
    //     LCG.addLayer(value);
    // });

    // console.log(LCG);

}! function (t, a) {
    "function" == typeof define && define.amd ? define(["leaflet"], a) : "object" == typeof modules && module.exports ? module.exports = a(require("leaflet")) : a(L)
}(0, function (l) {
    "use strict";
    return l.TileLayer.Provider = l.TileLayer.extend({
        initialize: function (t, a) {
            var o = l.TileLayer.Provider.providers,
                r = t.split("."),
                i = r[0],
                e = r[1];
            if (!o[i]) {
                throw "No such provider (" + i + ")";
            }
            var n = {
                url: o[i].url,
                options: o[i].options
            };
            if (e && "variants" in o[i]) {
                if (!(e in o[i].variants)) {
                    throw "No such variant of " + i + " (" + e + ")";
                }
                var s, p = o[i].variants[e];
                s = "string" == typeof p ? {
                    variant: p
                } : p.options, n = {
                    url: p.url || n.url,
                    options: l.Util.extend({}, n.options, s)
                }
            }
            var m = function (t) {
                return -1 === t.indexOf("{attribution.") ? t : t.replace(/\{attribution.(\w*)\}/, function (t, a) {
                    return m(o[a].options.attribution)
                })
            };
            n.options.attribution = m(n.options.attribution);
            var u = l.Util.extend({}, n.options, a);
            l.TileLayer.prototype.initialize.call(this, n.url, u)
        }
    }), l.TileLayer.Provider.providers = {
        OpenStreetMap: {
            url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
            options: {
                maxZoom: 19,
                attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            },
            variants: {
                Mapnik: {},
                BlackAndWhite: {
                    url: "http://{s}.tiles.wmflabs.org/bw-mapnik/{z}/{x}/{y}.png",
                    options: {
                        maxZoom: 18
                    }
                },
                HOT: {
                    url: "https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png",
                    options: {
                        attribution: '{attribution.OpenStreetMap}, Tiles courtesy of <a href="http://hot.openstreetmap.org/" target="_blank">Humanitarian OpenStreetMap Team</a>'
                    }
                }
            }
        },
        Hydda: {
            url: "https://{s}.tile.openstreetmap.se/hydda/{variant}/{z}/{x}/{y}.png",
            options: {
                maxZoom: 18,
                variant: "full",
                attribution: 'Tiles courtesy of <a href="http://openstreetmap.se/" target="_blank">OpenStreetMap Sweden</a> &mdash; Map data {attribution.OpenStreetMap}'
            },
            variants: {
                Full: "full"
            }
        },
        Stamen: {
            url: "https://stamen-tiles-{s}.a.ssl.fastly.net/{variant}/{z}/{x}/{y}{r}.{ext}",
            options: {
                attribution: 'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data {attribution.OpenStreetMap}',
                subdomains: "abcd",
                minZoom: 0,
                maxZoom: 20,
                variant: "toner",
                ext: "png"
            },
            variants: {
                Toner: "toner",
                TonerHybrid: "toner-hybrid",
                TonerLite: "toner-lite",
                Terrain: {
                    options: {
                        variant: "terrain",
                        minZoom: 0,
                        maxZoom: 18
                    }
                }
            }
        },
        Esri: {
            url: "https://server.arcgisonline.com/ArcGIS/rest/services/{variant}/MapServer/tile/{z}/{y}/{x}",
            options: {
                variant: "World_Street_Map",
                attribution: "Tiles &copy; Esri"
            },
            variants: {
                WorldStreetMap: {
                    options: {
                        attribution: "{attribution.Esri} &mdash; Source: Esri, DeLorme, NAVTEQ, USGS, Intermap, iPC, NRCAN, Esri Japan, METI, Esri China (Hong Kong), Esri (Thailand), TomTom, 2012"
                    }
                },
                DeLorme: {
                    options: {
                        variant: "Specialty/DeLorme_World_Base_Map",
                        minZoom: 1,
                        maxZoom: 11,
                        attribution: "{attribution.Esri} &mdash; Copyright: &copy;2012 DeLorme"
                    }
                },
                WorldTopoMap: {
                    options: {
                        variant: "World_Topo_Map",
                        attribution: "{attribution.Esri} &mdash; Esri, DeLorme, NAVTEQ, TomTom, Intermap, iPC, USGS, FAO, NPS, NRCAN, GeoBase, Kadaster NL, Ordnance Survey, Esri Japan, METI, Esri China (Hong Kong), and the GIS User Community"
                    }
                },
                OceanBasemap: {
                    options: {
                        variant: "Ocean_Basemap",
                        maxZoom: 13,
                        attribution: "{attribution.Esri} &mdash; Sources: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri"
                    }
                },
                NatGeoWorldMap: {
                    options: {
                        variant: "NatGeo_World_Map",
                        maxZoom: 16,
                        attribution: "{attribution.Esri} &mdash; National Geographic, Esri, DeLorme, NAVTEQ, UNEP-WCMC, USGS, NASA, ESA, METI, NRCAN, GEBCO, NOAA, iPC"
                    }
                },
                WorldGrayCanvas: {
                    options: {
                        variant: "Canvas/World_Light_Gray_Base",
                        maxZoom: 16,
                        attribution: "{attribution.Esri} &mdash; Esri, DeLorme, NAVTEQ"
                    }
                }
            }
        },
        CartoDB: {
            url: "https://cartodb-basemaps-{s}.global.ssl.fastly.net/{variant}/{z}/{x}/{y}{r}.png",
            options: {
                attribution: '{attribution.OpenStreetMap} &copy; <a href="http://cartodb.com/attributions">CartoDB</a>',
                subdomains: "abcd",
                maxZoom: 19,
                variant: "light_all"
            },
            variants: {
                Positron: "light_all",
                DarkMatter: "dark_all",
                Voyager: "rastertiles/voyager"
            }
        },
        HikeBike: {
            url: "http://{s}.tiles.wmflabs.org/{variant}/{z}/{x}/{y}.png",
            options: {
                maxZoom: 19,
                attribution: "{attribution.OpenStreetMap}",
                variant: "hikebike"
            },
            variants: {
                HikeBike: {}
            }
        },
        NASAGIBS: {
            url: "https://map1.vis.earthdata.nasa.gov/wmts-webmerc/{variant}/default/{time}/{tilematrixset}{maxZoom}/{z}/{y}/{x}.{format}",
            options: {
                attribution: 'Imagery provided by services from the Global Imagery Browse Services (GIBS), operated by the NASA/GSFC/Earth Science Data and Information System (<a href="https://earthdata.nasa.gov">ESDIS</a>) with funding provided by NASA/HQ.',
                bounds: [
                    [-85.0511287776, -179.999999975],
                    [85.0511287776, 179.999999975]
                ],
                minZoom: 1,
                maxZoom: 9,
                format: "jpg",
                time: "",
                tilematrixset: "GoogleMapsCompatible_Level"
            },
            variants: {
                ViirsEarthAtNight2012: {
                    options: {
                        variant: "VIIRS_CityLights_2012",
                        maxZoom: 8
                    }
                }
            }
        },
        Wikimedia: {
            url: "https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}{r}.png",
            options: {
                attribution: '<a href="https://wikimediafoundation.org/wiki/Maps_Terms_of_Use">Wikimedia</a>',
                minZoom: 1,
                maxZoom: 19
            }
        }
    }, l.tileLayer.provider = function (t, a) {
        return new l.TileLayer.Provider(t, a)
    }, l
}), jQuery(window).on("load", function () {
    initOpenStreetMap(document);
});
