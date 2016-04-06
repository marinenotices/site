<?php

/*
    Copyright 2016 Austin Goudge (email: austin@goudges.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
 * KML and geo code inspired by Post Geo Tag plugin by pgogy
 */

/**
 * Main plugin class
 *
 * @author austin
 */
class MarineNotice {
    protected $pluginDirPath;

    public function run($pluginDirPath) {
        $this->pluginDirPath = $pluginDirPath;

        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'addScriptsAndStyles'));
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('save_post', array($this, 'saveLocations'));
        add_action('do_feed_kml', array($this, 'kmlFeed'));
        add_action('wp_before_admin_bar_render', array($this, 'adminMenuBar'));
        add_filter('wp_nav_menu_args', array($this, 'navMenuArgs'));


        add_shortcode('navionics', array($this, 'navionicsMapShortcode'));
    }

    /**
     * Initialisation. Set up custom post type and taxonomies.
     */
    function init() {
        register_post_type( 'notice',
            array(
                'label' => __( 'notices', 'notices' ),
                'description' => __( 'Marine Notices', 'notices' ),
                'labels' => array(
                    'name' => __( 'Marine Notices' ),
                    'singular_name' => __( 'Marine Notice' )
                ),
                'public' => true,
                'has_archive' => true,
                'supports' => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'revisions'
                ),
                'show_ui' => true,
                'show_in_admin_bar' => true,
                'capability_type' => array('notice', 'notices'),
                'map_meta_cap' => true,
            )
        );

        register_taxonomy( 'notice_category', // register custom taxonomy - category
			'notice',
			array(
				'hierarchical' => true,
				'labels' => array(
					'name' => 'Notice category',
					'singular_name' => 'Notice category',
				)
			)
		);
		register_taxonomy( 'notice_tag', // register custom taxonomy - tag
			'notice',
			array(
				'hierarchical' => false,
				'labels' => array(
					'name' => 'Notice tag',
					'singular_name' => 'Notice tag',
				)
			)
		);

// Once stabilised, get rid of remove_role and move the rest into a register_activation_hook()
remove_role('harbourmaster');
add_role('harbourmaster', "Harbourmaster", array(
                                                        'read' => false,
                                                        'edit_posts' => false,
                                                        'delete_posts' => false,
                                                        'publish_posts' => false,
                                                        'upload_files' => true,
                                                        ) );

$roles = array('harbourmaster', 'editor', 'administrator');

// Loop through each role and assign capabilities
foreach($roles as $the_role) {
    $role = get_role($the_role);

    $role->add_cap( 'read_notice');
    $role->add_cap( 'read_private_notices' );
    $role->add_cap( 'edit_notice' );
    $role->add_cap( 'edit_notices' );
    $role->add_cap( 'edit_others_notices' );
    $role->add_cap( 'edit_published_notices' );
    $role->add_cap( 'publish_notices' );
    $role->add_cap( 'delete_notices' );
    $role->add_cap( 'delete_others_notices' );
    $role->add_cap( 'delete_private_notices' );
    $role->add_cap( 'delete_published_notices' );
}
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
                        fullscreenControl: false
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
                        fullscreenControl: false
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
                    });

                    jQuery(document).ready(function() {
                        jQuery('#marinenotice-location-lat-0').change(function() {
                            jQuery('#marinenotice-location-lat-0').val(parseFloat(jQuery('#marinenotice-location-lat-0').val()) || 0);
                            var center = new google.maps.LatLng(jQuery('#marinenotice-location-lat-0').val(), jQuery('#marinenotice-location-long-0').val());
                            gMapNautical.panTo(center);
                            marker.setPosition(center);
                        });
                        jQuery('#marinenotice-location-long-0').change(function() {
                            jQuery('#marinenotice-location-long-0').val(parseFloat(jQuery('#marinenotice-location-long-0').val()) || 0);
                            var center = new google.maps.LatLng(jQuery('#marinenotice-location-lat-0').val(), jQuery('#marinenotice-location-long-0').val());
                            gMapNautical.panTo(center);
                            marker.setPosition(center);
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

    /**
     * Add all extra JS and CSS to the page
     */
    function addScriptsAndStyles() {
        wp_register_script('navionicsJS', '//webapiv2.navionics.com/dist/webapi/webapi.min.no-dep.js', array(), false, false);
        wp_register_script('googleMapsJS', '//maps.googleapis.com/maps/api/js?key=AIzaSyCrRIr0X30B_d2Xp-s7ufCB5wSlvfKHoZI', array(), false, false);
        wp_register_style('navionicsCSS', '//webapiv2.navionics.com/dist/webapi/webapi.min.css', array(), false, 'all');

        wp_enqueue_script('navionicsJS');
        wp_enqueue_script('googleMapsJS');
        wp_enqueue_style('navionicsCSS');
    }

    function addMetaBoxes($output) {
		add_meta_box('marinenotice-locations', 'Locations', array($this, 'generateLocationsMetabox'), 'notice', 'normal', 'high');
	}

	function generateLocationsMetabox() {
        echo "<form>";
        wp_nonce_field('marinenotice-locations', 'marinenotice-locations');

        $postMeta = get_post_meta(filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT), "marinenotice-locations");
        if ($postMeta != "") {
            $locations = unserialize($postMeta[0]);
		}

		echo "<strong>Add Location</strong>
            <p>
                <label for='marinenotice-location-lat-0'>Latitude</label> <input name='marinenotice-location-lat-0' type='text' />
                <label for='marinenotice-location-long-0'>Longitude</label> <input name='marinenotice-location-long-0' type='text' />
            </p>";

		if ($locations != "") {
            echo "<strong>Existing Locations</strong>";
            $index = 0;
			foreach($locations as $location) {
                if ($location['lat'] != "" && $location['long'] != "") {
					$index++;
					echo "<p>
                            <label for='marinenotice-location-lat-" . $index . "'>Latitude</label> <input name='marinenotice-location-lat-" . $index . "' type='text' value='" . $location['lat'] . "' />
                            <label for='marinenotice-location-long-" . $index . "'>Longitude</label> <input name='marinenotice-location-long-" . $index . "' type='text' value='" . $location['long'] . "' />
                            <label for='marinenotice-location-delete-" . $index . "'>Delete</label> <input name='marinenotice-location-delete-" . $index . "' type='checkbox' />
                        </p>";
				}
			}
		}

        echo "</form>";
	}

    function saveLocations($post_id) {
		if (!isset($_POST['marinenotice-locations']) || !wp_verify_nonce($_POST['marinenotice-locations'], 'marinenotice-locations')) {
    		return $post_id;
  		}

        if (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
        }

        self::processLocationsPostMeta($post_id);
    }

    static function processLocationsPostMeta($post_id) {
		$locations = array();

  		foreach($_POST as $key => $value) {
            if (strpos($key, 'marinenotice-location') === FALSE) {
                continue;
            }

            if ($value == "") {
                continue;
            }

			$parts = explode("-", $key);

			if (!isset($parts[3])) {
                continue;
            }

            $id = $parts[3];

            switch ($parts[2]) {
                case 'lat':
                    $locations[$id]['lat'] = $value;
                    break;
                case 'long':
                    $locations[$id]['long'] = $value;
                    break;
                case 'delete':
                    unset($locations[$id]);
                    break;
                default:
                    continue;
            }
		}

		update_post_meta($post_id, "marinenotice-locations", serialize($locations));
    }

    function kmlFeed() {
		header('Content-Type: application/xml');

	    echo '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://earth.google.com/kml/2.2">
                <Document>
                    <name>Notices to Mariners</name>
                    <description><![CDATA[Notices to Mariners. Generated by marinenotice.net, copyright ' . date('Y') . ' all rights reserved.]]></description>
                    <Style id="style1">
                        <IconStyle>
                            <Icon>
                                <href>http://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png</href>
                            </Icon>
                        </IconStyle>
                    </Style>';

	    $args = array(
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type' => 'notice');

	    $posts = get_posts($args);

	    foreach($posts as $post) {
            $data = get_post_meta($post->ID, "marinenotice-locations");

            if (isset($data[0])) {
                $locations = unserialize($data[0]);

                $index = 1;
                $count = count($locations);

                foreach($locations as $location) {
                    if ($location['lat'] == "" || $location['long'] == "") {
                        continue;
                    }

                    echo "<Placemark>
                            <name>" . $post->post_title . ($count > 1 ? " (" . $index . " of " . $count . ")" : "") . "</name>
                            <description><![CDATA[For more information <a href='" . $post->guid . "'>click here</a>.  Source: " . get_the_author_meta( "display_name", $post->post_author ) . "]]></description>
                            <styleUrl>#style1</styleUrl>
                            <Point>
                            <coordinates>" . $location['long'] . "," . $location['lat'] . "</coordinates>
                            </Point>
                        </Placemark>";

                    $index++;
                }
            }
	    }

	    echo "</Document></kml>";
	}

    function adminMenuBar() {
        if (!current_user_can( 'manage_options' ) ) {
            global $wp_admin_bar;

            $wp_admin_bar->remove_menu('wp-logo');
            $wp_admin_bar->remove_menu('site-name');
            $wp_admin_bar->remove_menu('new-content');
        }
    }

    function navMenuArgs($args = '') {
        if( is_user_logged_in() ) {
            $args['menu'] = 'main-logged-in';
        } else {
            $args['menu'] = 'main';
        }

        return $args;
    }
}
