<?php
/*
Plugin Name: Marine Notice
Description: Custom functionality to support marinenotice.net site
Version: 0.1
Author: Austin Goudge
Network: True
*/

if (!defined( 'WPINC')) {
    die;
}

/**
 * Abort if WordPress is upgrading
 */
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-marinenotice.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mnshortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mnpostmeta-locations.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mnposttype-notice.php';

$osx = new MarineNotice();
$osx->run(plugin_dir_path(__FILE__));
