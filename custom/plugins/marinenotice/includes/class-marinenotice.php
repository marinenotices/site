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

        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_filter('admin_bar_menu', array($this, 'adminBarMenu'), 25);
        add_action('do_feed_kml', array($this, 'doFeedKML'));
        add_action('init', array($this, 'init'));
        add_action('pre_get_posts', array($this, 'preGetPosts'));
        add_action('save_post', array($this, 'savePost'));
        add_action('wp_before_admin_bar_render', array($this, 'beforeAdminBarRender'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));

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

    /**
     * Add all extra JS and CSS to the page
     */
    function enqueueScripts() {
        wp_register_script('navionics', '//webapiv2.navionics.com/dist/webapi/webapi.min.no-dep.js', array(), false, false);
        wp_register_script('googleMaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyCrRIr0X30B_d2Xp-s7ufCB5wSlvfKHoZI', array(), false, false);
        wp_register_style('navionics', '//webapiv2.navionics.com/dist/webapi/webapi.min.css', array(), false, 'all');

        wp_enqueue_script('navionics');
        wp_enqueue_script('googleMaps');

        wp_enqueue_style('navionics');
        wp_enqueue_style('marinenotice', plugins_url('../css/style.css', __FILE__), array(), '1.1', 'all' );
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

    function savePost($post_id) {
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

    function doFeedKML() {
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
                            <description><![CDATA[For more information <a href='" . $post->guid . "'>click here</a>.  Source: <a href='" . get_author_posts_url( $post->post_author, $author_nicename ) . "'>" . get_the_author_meta( "display_name", $post->post_author ) . "</a>]]></description>
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

    function beforeAdminBarRender() {
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

    function preGetPosts($query) {
        if ( $query->is_main_query() && $query->is_author() ) {
            $query->set( 'post_type', 'notice' );
        }
    }

    function adminBarMenu($wp_admin_bar) {
        $my_account = $wp_admin_bar->get_node('my-account');
        $newtitle = str_replace('Howdy,', '', $my_account->title);
        $wp_admin_bar->add_node(array(
            'id' => 'my-account',
            'title' => $newtitle,
        ) );
    }
}
