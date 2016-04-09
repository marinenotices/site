<?php

class MNShortcodes {
    function __construct() {
    }

    /**
     * Shortcode for displaying a Navionics Map
     * @param type $attrs
     * @return string
     */
    function navionicsMapShortcode($attrs)
    {
        $gmap = false;
        $pin = false;
        $lat = false;
        $long = false;

        if (is_array($attrs)) {
            if (array_key_exists('gmap', $attrs) && $attrs['gmap'] == 'true') {
                $gmap = true;
            }

            if (array_key_exists('pin', $attrs) && $attrs['pin'] == 'true') {
                $pin = true;
            }

            if (array_key_exists('lat', $attrs)) {
                $lat = $attrs['lat'];
            }

            if (array_key_exists('long', $attrs)) {
                $long = $attrs['long'];
            }
        }

        if ($gmap) {
            $result = "
                <style>
                    #nautical-map-container {
                        border: 1px solid gray;
                        min-height: 500px;
                        width: 100%;
                        height: 100%;
                        margin-top: 10px;
                        margin-bottom: 10px;
                    }
                </style>

                <div id='nautical-map-container' class='map'></div>
                <script>
                    // Google Map Engine options";
            if ($lat && $long) {
                $result .= "
                    var centerLatLong = new google.maps.LatLng(" . $lat . "," . $long . ");
                    var gMapNauticalOptions = {
                        zoom: 12,
                        center: centerLatLong,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        streetViewControl: false,
                        rotateControl: false,
                        fullscreenControl: false,
                        zoomControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_TOP
                        },
                    };";
            } else {
                $result .= "
                    var centerLatLong = new google.maps.LatLng(0, 0);
                    var gMapNauticalOptions = {
                        zoom: 2,
                        center: centerLatLong,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        streetViewControl: false,
                        rotateControl: false,
                        fullscreenControl: false,
                        zoomControlOptions: {
                            position: google.maps.ControlPosition.RIGHT_TOP
                        },
                    };";
            }

            $result .= "
                    // Create Google Map Engine
                    var gMapNautical = new google.maps.Map(document.getElementById('nautical-map-container'), gMapNauticalOptions);

                    // Create Navionics NauticalChart
                    var navionics_nauticalchart_layer = new JNC.Views.gNavionicsOverlay({
                        navKey: 'Navionics_webapi_01136',
                        chartType: JNC.Views.gNavionicsOverlay.CHARTS.NAUTICAL
                    });";

            if ($pin) {
                $result .= "
                    var marker = new google.maps.Marker({
                        position: centerLatLong,
                        map: gMapNautical,
                        draggable: true,
                        title: 'Drag me!'
                    });

                    google.maps.event.addListener(marker, 'dragend', function (event) {
                        jQuery('#marinenotice-location-lat-0').val(event.latLng.lat());
                        jQuery('#marinenotice-location-long-0').val(event.latLng.lng());
                        var center = new google.maps.LatLng(event.latLng.lat(), event.latLng.lng());
                        gMapNautical.panTo(center);
                        dToDM();
                        dToDMS();
                    });

                    function dmsToD() {
                        var deg = jQuery('#dms-degrees-lat').val();
                        var min = jQuery('#dms-minutes-lat').val();
                        var sec = jQuery('#dms-seconds-lat').val();
                        jQuery('#marinenotice-location-lat-0').val(deg * 1.0 + ((min * 1.0 + (sec / 60.0)) / 60.0));

                        deg = jQuery('#dms-degrees-long').val();
                        min = jQuery('#dms-minutes-long').val();
                        sec = jQuery('#dms-seconds-long').val();
                        jQuery('#marinenotice-location-long-0').val(deg * 1.0 + ((min * 1.0 + (sec / 60.0)) / 60.0));
                    };

                    function dmToD() {
                        var deg = jQuery('#dm-degrees-lat').val();
                        var min = jQuery('#dm-minutes-lat').val();
                        jQuery('#marinenotice-location-lat-0').val((deg * 1.0) + min / 60.0);

                        deg = jQuery('#dm-degrees-long').val();
                        min = jQuery('#dm-minutes-long').val();
                        jQuery('#marinenotice-location-long-0').val((deg * 1.0) + min / 60.0);
                    };

                    function dToDMS() {
                        var value = jQuery('#marinenotice-location-lat-0').val();
                        var degrees = parseInt(value || 0);
                        var minutes = Math.abs((value - (degrees * 1.0)) * 60.0);
                        jQuery('#dms-degrees-lat').val(degrees);
                        jQuery('#dms-minutes-lat').val(parseInt(minutes));
                        jQuery('#dms-seconds-lat').val(parseInt((minutes - (parseInt(minutes) * 1.0)) * 60.0));

                        value = jQuery('#marinenotice-location-long-0').val();
                        degrees = parseInt(value || 0);
                        minutes = Math.abs((value - (degrees * 1.0)) * 60.0);
                        jQuery('#dms-degrees-long').val(degrees);
                        jQuery('#dms-minutes-long').val(parseInt(minutes));
                        jQuery('#dms-seconds-long').val(parseInt((minutes - (parseInt(minutes) * 1.0)) * 60.0));
                    };

                    function dToDM() {
                        var value = jQuery('#marinenotice-location-lat-0').val();
                        var degrees = parseInt(value || 0);
                        jQuery('#dm-degrees-lat').val(degrees);
                        jQuery('#dm-minutes-lat').val(Math.abs((value - (degrees * 1.0)) * 60.0));

                        value = jQuery('#marinenotice-location-long-0').val();
                        degrees = parseInt(value || 0);
                        jQuery('#dm-degrees-long').val(degrees);
                        jQuery('#dm-minutes-long').val(Math.abs((value - (degrees * 1.0)) * 60.0));
                    };

                    function recenterMapAndPin() {
                        var center = new google.maps.LatLng(jQuery('#marinenotice-location-lat-0').val(), jQuery('#marinenotice-location-long-0').val());
                        gMapNautical.panTo(center);
                        marker.setPosition(center);
                    };

                    function dChanged() {
                        dToDM();
                        dToDMS();
                        recenterMapAndPin();
                    };

                    function dmChanged() {
                        dmToD();
                        dToDMS();
                        recenterMapAndPin();
                    };

                    function dmsChanged() {
                        dmsToD();
                        dToDM();
                        recenterMapAndPin();
                    };

                    jQuery(document).ready(function() {
                        jQuery('#marinenotice-location-lat-0').change(function() {
                            jQuery('#marinenotice-location-lat-0').val(parseFloat(jQuery('#marinenotice-location-lat-0').val()) || 0);
                            dChanged();
                        });
                        jQuery('#marinenotice-location-long-0').change(function() {
                            jQuery('#marinenotice-location-long-0').val(parseFloat(jQuery('#marinenotice-location-long-0').val()) || 0);
                            dChanged();
                        });
                        jQuery('#dm-degrees-lat').change(function() {
                            dmChanged();
                        });
                        jQuery('#dm-minutes-lat').change(function() {
                            dmChanged();
                        });
                        jQuery('#dm-degrees-long').change(function() {
                            dmChanged();
                        });
                        jQuery('#dm-minutes-long').change(function() {
                            dmChanged();
                        });
                        jQuery('#dms-degrees-lat').change(function() {
                            dmsChanged();
                        });
                        jQuery('#dms-minutes-lat').change(function() {
                            dmsChanged();
                        });
                        jQuery('#dms-seconds-lat').change(function() {
                            dmsChanged();
                        });
                        jQuery('#dms-degrees-long').change(function() {
                            dmsChanged();
                        });
                        jQuery('#dms-minutes-long').change(function() {
                            dmsChanged();
                        });
                        jQuery('#dms-seconds-long').change(function() {
                            dmsChanged();
                        });
                    });";
            }

            $result .= "
                    gMapNautical.overlayMapTypes.insertAt(0, navionics_nauticalchart_layer);
                </script>
            ";
        } else {
            $result = "
                <style>
                    #nautical-map-container {
                        border: 1px solid gray;
                        min-height: 500px;
                        width: 100%;
                        height: 100%;
                        margin-top: 10px;
                        margin-bottom: 10px;
                    }
                </style>
                <div id='nautical-map-container'></div>
                <script>
                    var webapi = new JNC.Views.BoatingNavionicsMap({
                        tagId: '#nautical-map-container',
                        center: [  12.0, 46.0 ],
                        zoom: 0,
                        /* locale: 'CA', */
                        ZoomControl: true,
                        DistanceControl: false,
                        SonarControl: false,
                        LayerControl: false,
                        navKey: 'Navionics_webapi_01136'
                    });
                    /*webapi.showSonarControl(true);*/
                    webapi.loadKml('/?feed=kml', false);
                </script>";
        }

        return $result;
    }
}