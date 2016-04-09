<?php

class MNPostmetaLocations {
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
		if (!wp_verify_nonce($_POST['marinenotice-locations'], 'marinenotice-locations')) {
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

}