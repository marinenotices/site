<?php
/*
Plugin Name: Marine Notice
Description: Custom functionality to support marinenotice.net site
Version: 0.1
Author: Austin Goudge
Network: True
*/

if (!defined('WPINC')) {
    die;
}

/**
 * Abort if WordPress is upgrading
 */
if (defined('WP_INSTALLING') && WP_INSTALLING) {
    return;
}

if (!defined('MARINENOTICE_PATH')) {
    define('MARINENOTICE_PATH', plugin_dir_path(__FILE__));
}

require_once(MARINENOTICE_PATH . 'includes/class-marinenotice.php');
require_once(MARINENOTICE_PATH . 'includes/class-mnshortcodes.php');
require_once(MARINENOTICE_PATH . 'includes/class-mnpostmeta.php');
require_once(MARINENOTICE_PATH . 'includes/class-mnposttypes.php');
require_once(MARINENOTICE_PATH . 'includes/class-mnroles.php');
require_once(MARINENOTICE_PATH . 'includes/class-mnkml.php');

$osx = new MarineNotice();
$osx->run(MARINENOTICE_PATH);
