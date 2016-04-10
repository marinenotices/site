<?php
class MNKML {
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
}