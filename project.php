<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>OpenStreetMap &amp; OpenLayers - Marker Example</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

    <link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css" />
    <script src="https://openlayers.org/en/v4.6.5/build/ol.js" type="text/javascript"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js" type="text/javascript"></script>

       
    <style>
        /*
            .map, .righ-panel {
                height: 500px;
                width: 80%;
                float: left;
            }
            */
        .map,
        .righ-panel {
            height: 98vh;
            width: 80vw;
            float: left;
        }

        .map {
            border: 1px solid #000;
        }

        .ol-popup {
            position: absolute;
            background-color: white;
            -webkit-filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            filter: drop-shadow(0 1px 4px rgba(0, 0, 0, 0.2));
            padding: 15px;
            border-radius: 10px;
            border: 1px solid #cccccc;
            bottom: 12px;
            left: -50px;
            min-width: 180px;
        }

        .ol-popup:after,
        .ol-popup:before {
            top: 100%;
            border: solid transparent;
            content: " ";
            height: 0;
            width: 0;
            position: absolute;
            pointer-events: none;
        }

        .ol-popup:after {
            border-top-color: white;
            border-width: 10px;
            left: 48px;
            margin-left: -10px;
        }

        .ol-popup:before {
            border-top-color: #cccccc;
            border-width: 11px;
            left: 48px;
            margin-left: -11px;
        }

        .ol-popup-closer {
            text-decoration: none;
            position: absolute;
            top: 2px;
            right: 8px;
        }

        .ol-popup-closer:after {
            content: "✖";
        }
    </style>
</head>

<body onload="initialize_map();">

    <table>

        <tr>

            <td>
                <div id="map" class="map"></div>
                <div id="map" style="width: 50vw; height: 50vh;"></div>
                <div id="popup" class="ol-popup">
                    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
                    <div id="popup-content"></div>
                </div>
                <!--<div id="map" style="width: 80vw; height: 100vh;"></div>-->
            </td>
            <td>
                <input type="textinput" id="ctiy"><br/>
                <button id="btnSeacher"> Tìm kiếm</button>
                <br />
                <br />
                <br />

                <input onclick="oncheckhydropower();" type="checkbox" id="hydropower" name="layer" value="hydropower"> Thủy điện<br />
                <input onclick="oncheckriver();" type="checkbox" id="river" name="layer" value="river"> Sông <br />
                <input onclick="oncheckvn()" type="checkbox" id="VN" name="layer" value="VN"> Việt Nam<br />
                <button id="btnRest"> Reset</button>

            </td>
        </tr>
    </table>
    <?php include 'VN_pgsqlAPI.php' ?>

    <script>
        var format = 'image/png';
        var map;
        var minX = 102.144584655762;
        var minY = 8.38135528564453;
        var maxX = 109.469177246094;
        var maxY = 23.3926944732666;
        var cenX = (minX + maxX) / 2;
        var cenY = (minY + maxY) / 2;
        var mapLat = cenY;
        var mapLng = cenX;
        var mapDefaultZoom = 5;
        var layerVN_adm1;
        var layer_river;
        var layer_hydropower;
        var vectorLayer;
        var styleFunction;
        var styles;
        var container = document.getElementById('popup');
        var content = document.getElementById('popup-content');
        var closer = document.getElementById('popup-closer');
        var ctiy = document.getElementById("ctiy");
        var chkVN = document.getElementById("vn");
        var chkHydro = document.getElementById("hydropower");
        var chkRiver = document.getElementById("river");
        var value ;
        /**
         * Create an overlay to anchor the popup to the map.
         */
        var overlay = new ol.Overlay( /** @type {olx.OverlayOptions} */ ({
            element: container,
            autoPan: true,
            autoPanAnimation: {
                duration: 250
            }
        }));
        closer.onclick = function() {
            overlay.setPosition(undefined);
            closer.blur();
            return false;
        };
        function handleOnCheck(id, layer) {
            if (document.getElementById(id).checked) {
                value = document.getElementById(id).value;
                // map.setLayerGroup(new ol.layer.Group())
                map.addLayer(layer)
                vectorLayer = new ol.layer.Vector({});
                map.addLayer(vectorLayer);
            } else {
                map.removeLayer(layer);
                map.removeLayer(vectorLayer);
            }
        }
        function myFunction() {
            var popup = document.getElementById("popup");
            popup.classList.toggle("show");
        }
        function oncheckhydropower() {
            handleOnCheck('hydropower', layer_hydropower);

        }
        function oncheckriver() {
            handleOnCheck('river', layer_river);

        }
        function oncheckvn() {
            handleOnCheck('VN', layerVN_adm1);
        }

        function initialize_map() {

            //*
            layerBG = new ol.layer.Tile({
                source: new ol.source.OSM({})
            });

            //*/
            layerVN_adm1 = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/dams_VietNam/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'gadm36_vnm_1',
                    }
                })

            });

            layer_river = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/dams_VietNam/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'river',
                    }
                })

            });

            layer_hydropower = new ol.layer.Image({
                source: new ol.source.ImageWMS({
                    ratio: 1,
                    url: 'http://localhost:8080/geoserver/dams_VietNam/wms?',
                    params: {
                        'FORMAT': format,
                        'VERSION': '1.1.1',
                        STYLES: '',
                        LAYERS: 'hydropower_dams',
                    }
                })

            });

            var viewMap = new ol.View({
                center: ol.proj.fromLonLat([mapLng, mapLat]),
                zoom: mapDefaultZoom
            });
            
            map = new ol.Map({
                target: "map",
                layers: [layerBG],
                view: viewMap,
                overlays: [overlay], //them khai bao overlays
            });
            styles = {
                'Point': new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 3
                    })
                }),
                'MultiLineString': new ol.style.Style({

                    stroke: new ol.style.Stroke({
                        color: 'red',
                        width: 3
                    })
                }),
                'Polygon': new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: 'red',
                        width: 3
                    })
                }),
                'MultiPolygon': new ol.style.Style({
                    fill: new ol.style.Fill({
                        color: 'orange'
                    }),
                    stroke: new ol.style.Stroke({
                        color: 'yellow',
                        width: 2
                    })
                })
            };
            styleFunction = function(feature) {
                return styles[feature.getGeometry().getType()];
            };
            var stylePoint = new ol.style.Style({
                image: new ol.style.Icon({
                    anchor: [0.5, 0.5],
                    anchorXUnits: "fraction",
                    anchorYUnits: "fraction",
                    src: "http://localhost/project/Yellow_dot.svg"
                })
            });
            vectorLayer = new ol.layer.Vector({
                style: styleFunction
            });
            map.addLayer(vectorLayer);
            var buttonReset = document.getElementById("btnRest").addEventListener("click", () => {
                location.reload();
            })
            var button = document.getElementById("btnSeacher").addEventListener("click",
                () => {
                    vectorLayer.setStyle(styleFunction);
                    ctiy.value.length ?
                        $.ajax({
                            type: "POST",
                            url: "VN_pgsqlAPI.php",
                            data: {
                                name: ctiy.value
                            },
                            success: function(result, status, erro) {

                                if (result == 'null')
                                    alert("Không tìm thấy đối tượng");
                                else
                                    highLightObj(result);
                            },
                            error: function(req, status, error) {
                                alert(req + " " + status + " " + error);
                            }
                        }) : alert("Nhập dữ liệu tìm kiếm")
                });
            function createJsonObj(result) {
                var geojsonObject = '{' +
                    '"type": "FeatureCollection",' +
                    '"crs": {' +
                    '"type": "name",' +
                    '"properties": {' +
                    '"name": "EPSG:4326"' +
                    '}' +
                    '},' +
                    '"features": [{' +
                    '"type": "Feature",' +
                    '"geometry": ' + result +
                    '}]' +
                    '}';
                return geojsonObject;
            }
            function highLightGeoJsonObj(paObjJson) {
                var vectorSource = new ol.source.Vector({
                    features: (new ol.format.GeoJSON()).readFeatures(paObjJson, {
                        dataProjection: 'EPSG:4326',
                        featureProjection: 'EPSG:3857'
                    })
                });
                vectorLayer.setSource(vectorSource);
            }

            function highLightObj(result) {
                var strObjJson = createJsonObj(result);
                var objJson = JSON.parse(strObjJson);
                highLightGeoJsonObj(objJson);
            }

            function displayObjInfo(result, coordinate) {
                $("#popup-content").html(result);
                overlay.setPosition(coordinate);

            }

            map.on('singleclick', function(evt) {
                var myPoint = 'POINT(12,5)';
                var lonlat = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
                var lon = lonlat[0];
                var lat = lonlat[1];
                var myPoint = 'POINT(' + lon + ' ' + lat + ')';
                
                if (value == 'VN') {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoVNToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoVNToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == "hydropower") {
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoHyproPowerToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoHyproPowerToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                if (value == "river") {
                    //river
                    vectorLayer.setStyle(stylePoint);
                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getInfoRiverToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            displayObjInfo(result, evt.coordinate);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });

                    $.ajax({
                        type: "POST",
                        url: "VN_pgsqlAPI.php",
                        data: {
                            functionname: 'getGeoRiverToAjax',
                            paPoint: myPoint
                        },
                        success: function(result, status, erro) {
                            highLightObj(result);
                        },
                        error: function(req, status, error) {
                            alert(req + " " + status + " " + error);
                        }
                    });
                }
                
            });
        };
    </script>
</body>

</html>