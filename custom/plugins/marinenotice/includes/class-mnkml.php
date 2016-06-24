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

        $noticeTypes = MarineNotice::getNoticeTypes();

        $result = '<?xml version="1.0" encoding="UTF-8"?>
                <kml xmlns="http://earth.google.com/kml/2.2">
                <Document>
                    <name>Notices to Mariners</name>
                    <description><![CDATA[Notices to Mariners. Generated by marinenotice.net, copyright ' . date('Y') . ' all rights reserved.]]></description>';

        foreach ($noticeTypes as $noticeType) {
            $result .= '<Style id="marker-' . $noticeType . '">
                        <IconStyle>
                            <Icon><href>' . MarineNotice::getNoticeIconURL($noticeType) . '</href></Icon>
                            <scale>0.05</scale>
                            <hotSpot x="0" y="0" xunits="fraction" yunits="fraction"/>
                        </IconStyle>
                    </Style>';
        }

        $args = array(
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type' => 'notice');

        $authorityID = get_query_var('aID', '');

        if ($authorityID != '') {
            $args['author'] = $authorityID;
        }

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $data = get_post_meta($post->ID, "marinenotice-locations");

            if (isset($data[0])) {
                $locations = unserialize($data[0]);

                $index = 1;
                $count = count($locations);

                foreach ($locations as $location) {
                    if ($location['lat'] == "" || $location['long'] == "") {
                        continue;
                    }

                    $result .= "<Placemark>
                            <name>" . $post->post_title . ($count > 1 ? " (" . $index . " of " . $count . ")" : "") . "</name>
                            <description><![CDATA[For more information <a href='" . $post->guid . "'>click here</a>.  Source: <a href='" . get_author_posts_url($post->post_author, $author_nicename) . "'>" . get_the_author_meta("display_name", $post->post_author) . "</a>]]></description>
                            <styleUrl>#marker-" . MarineNotice::getNoticeTypeFromPost($post) . "</styleUrl>
                            <Point>
                            <coordinates>" . $location['long'] . "," . $location['lat'] . "</coordinates>
                            </Point>
                        </Placemark>";

                    $index++;
                }
            }
        }

        $result .= "</Document></kml>";

        echo $result;
    }
}