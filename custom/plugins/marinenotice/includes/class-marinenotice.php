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

    private $mnShortcodes;
    private $mnPostmeta;
    private $mnPosttypes;
    private $mnRoles;
    private $mnKML;

    public function run($pluginDirPath) {
        $this->pluginDirPath = $pluginDirPath;

        $this->mnShortcodes = new MNShortcodes();
        $this->mnPostmeta = new MNPostmeta();
        $this->mnPosttypes = new MNPosttypes();
        $this->mnRoles = new MNRoles();
        $this->mnKML = new MNKML();

        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_filter('admin_bar_menu', array($this, 'adminBarMenu'), 25);
        add_action('do_feed_kml', array($this->mnKML, 'doFeed'));
        add_action('init', array($this, 'init'));
        add_action('pre_get_posts', array($this, 'preGetPosts'));
        add_action('save_post', array($this, 'savePost'));
        add_action('wp_before_admin_bar_render', array($this, 'beforeAdminBarRender'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));

        add_filter('wp_nav_menu_args', array($this, 'navMenuArgs'));

        add_shortcode('navionics', array($this->mnShortcodes, 'navionicsMapShortcode'));
    }

    /**
     * Initialisation. Set up custom post type and taxonomies.
     */
    function init() {
        $this->mnPosttypes->init();
        $this->mnRoles->init();
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
		add_meta_box('marinenotice-locations', 'Locations', array($this->mnPostmeta, 'generateLocationsMetabox'), 'notice', 'normal', 'high');
	}


    function savePost($post_id) {
        if (isset($_POST['marinenotice-locations'])) {
            return $this->mnPostmeta->savePost($post_id);
        }

        return $post_id;
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

    function beforeAdminBarRender() {
        if (!current_user_can( 'manage_options' ) ) {
            global $wp_admin_bar;

            $wp_admin_bar->remove_menu('wp-logo');
            $wp_admin_bar->remove_menu('site-name');
            $wp_admin_bar->remove_menu('new-content');
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
