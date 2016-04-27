<?php

class MNKML {
    protected $plugin;

    function __construct($plugin) {
        $this->plugin = $plugin;
    }

    /**
     * do_feed_kml action to generate the KML feed
     */
    function doFeed() {
        header('Content-Type: application/xml');

        echo '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://earth.google.com/kml/2.2">
                <Document>
                    <name>Notices to Mariners</name>
                    <description><![CDATA[Notices to Mariners. Generated by marinenotice.net, copyright ' . date('Y') . ' all rights reserved.]]></description>
                    <Style id="marker-buoy-new">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-new.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-buoy-missing">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-missing.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-buoy-incorrect-position">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-moved.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-buoy-moved">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-moved.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-buoy-removed">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-removed.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-buoy-temporary">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-buoy-temporary.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-wreck-moved">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-wreck-moved.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-wreck-new">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-wreck-new.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-wreck-removed">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-wreck-removed.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-light-new">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-light-new.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-light-changed">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-light-changed.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-light-removed">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-light-removed.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-light-inoperative">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-light-removed.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-work-construction">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-work-construction.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-work-demolition">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-work-demolition.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-work-underwater">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-work-underwater.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    <Style id="marker-work-dredging">
                        <IconStyle>
                            <Icon><href>' . plugins_url('../images/marker-work-dredging.svg', __FILE__) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>
                    ';

        $args = array(
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type' => 'notice');

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $data = get_post_meta($post->ID, "marinenotice-locations");
            $categories = get_the_terms($post, 'notice_category');
            error_log(print_r($category), true);
            if (is_array($categories) && count($categories) > 0) {
                $markerStyle = 'marker-' . $categories[0]->slug;
            } else {
                $markerStyle = 'marker-green-tick';
            }

            if (isset($data[0])) {
                $locations = unserialize($data[0]);

                $index = 1;
                $count = count($locations);

                foreach ($locations as $location) {
                    if ($location['lat'] == "" || $location['long'] == "") {
                        continue;
                    }

                    echo "<Placemark>
                            <name>" . $post->post_title . ($count > 1 ? " (" . $index . " of " . $count . ")" : "") . "</name>
                            <description><![CDATA[For more information <a href='" . $post->guid . "'>click here</a>.  Source: <a href='" . get_author_posts_url($post->post_author, $author_nicename) . "'>" . get_the_author_meta("display_name", $post->post_author) . "</a>]]></description>
                            <styleUrl>#" . $markerStyle . "</styleUrl>
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
}