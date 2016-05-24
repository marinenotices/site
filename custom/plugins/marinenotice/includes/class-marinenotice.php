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
class MarineNotice
{
    public $pluginDirPath;

    private $mnShortcodes;
    private $mnPostmeta;
    private $mnPosttypes;
    private $mnRoles;
    private $mnKML;

    public function run($pluginDirPath)
    {
        $this->pluginDirPath = $pluginDirPath;

        $this->mnShortcodes = new MNShortcodes();
        $this->mnPostmeta = new MNPostmeta();
        $this->mnPosttypes = new MNPosttypes();
        $this->mnRoles = new MNRoles();
        $this->mnKML = new MNKML($this);

        // Actions
        add_action('add_meta_boxes', array($this, 'addMetaBoxes'));
        add_action('do_feed_kml', array($this->mnKML, 'doFeed'));
        add_action('init', array($this, 'init'));
        add_action('pre_get_posts', array($this, 'preGetPosts'));
        add_action('save_post', array($this, 'savePost'));
        add_action('wp_before_admin_bar_render', array($this, 'beforeAdminBarRender'));
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_action('widgets_init', array($this, 'widgetsInit'));

        // Filters
        add_filter('wp_nav_menu_args', array($this, 'navMenuArgs'));
        add_filter('query_vars', array($this, 'queryVars'));
        add_filter('embed_site_title_html', array($this, 'embedSiteTitleHTML'));

        // Shortcodes
        add_shortcode('navionics', array($this->mnShortcodes, 'navionicsMapShortcode'));
        add_shortcode('mnlogo', array($this->mnShortcodes, 'logoShortcode'));
    }

    /**
     * init action to call init functions for our classes.
     */
    function init()
    {
        $this->mnPosttypes->init();
        $this->mnRoles->init();
    }


    /**
     * wp_enqueue_scripts action to add all extra JS and CSS to the page
     */
    function enqueueScripts()
    {
        wp_register_script('navionics', '//webapiv2.navionics.com/dist/webapi/webapi.min.no-dep.js', array(), false, false);
        wp_register_script('googleMaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyCrRIr0X30B_d2Xp-s7ufCB5wSlvfKHoZI', array(), false, false);
        wp_register_style('navionics', '//webapiv2.navionics.com/dist/webapi/webapi.min.css', array(), false, 'all');

        wp_enqueue_script('navionics');
        wp_enqueue_script('googleMaps');

        wp_enqueue_style('navionics');
        wp_enqueue_style('marinenotice', plugins_url('../css/style.css', __FILE__), array(), '1.1', 'all');
    }

    /**
     * add_meta_boxes action to add admin meta boxes for custom postmeta editing.
     */
    function addMetaBoxes()
    {
        add_meta_box('marinenotice-locations', 'Locations', array($this->mnPostmeta, 'generateLocationsMetabox'), 'notice', 'normal', 'high');
    }


    /**
     * save_post action to handle our custom post meta.
     *
     * @param integer $post_id The post ID
     */
    function savePost($post_id)
    {
        if (isset($_POST['marinenotice-locations'])) {
            $this->mnPostmeta->savePost($post_id);
        }
    }

    /**
     * pre_get_posts action to show Notices on author's archive pages
     *
     * @param WP_Query $query The query object
     */
    function preGetPosts($query)
    {
        if ($query->is_main_query() && $query->is_author()) {
            $query->set('post_type', 'notice');
        }
    }

    /**
     * wp_before_admin_bar_render action to remove various items from the admin menu bar
     *
     * @global WP_Admin_Bar $wp_admin_bar The admin bar object
     */
    function beforeAdminBarRender()
    {
        global $wp_admin_bar;

        if (!current_user_can('manage_options')) {
            $wp_admin_bar->remove_menu('wp-logo');
            $wp_admin_bar->remove_menu('site-name');
            $wp_admin_bar->remove_menu('new-content');
        }

        $my_account = $wp_admin_bar->get_node('my-account');
        $newtitle = str_replace('Howdy,', '', $my_account->title);
        $wp_admin_bar->add_node(array(
            'id' => 'my-account',
            'title' => $newtitle,
        ));
    }

    /**
     * wp_nav_menu_args filter to change the nav menu based on whether the user is logged in.
     *
     * @param array $args The nav menu args
     * @return array The modified args
     */
    function navMenuArgs($args = '')
    {
        if (is_array($args) && $args['theme_location'] == 'primary-menu') {
            if (is_user_logged_in()) {
                $args['menu'] = 'main-logged-in';
            } else {
                $args['menu'] = 'main';
            }
        }

        return $args;
    }

    /**
     * query_vars filter to add custom query variables
     *
     * @param array $vars The current query variables
     * @return array The modified query variables
     */
    function queryVars($vars)
    {
        $vars[] = 'aID';
        return $vars;
    }

    /**
     * widgets_init action to register our widgets
     */
    function widgetsInit()
    {
        register_widget('MNAuthorWidget');
    }

    /**
     * embed_site_title_html filter to change the title HTML for embeds (changes the site logo)
     *
     * @param type $site_title The site title
     * @return string The modified site title
     */
    function embedSiteTitleHTML($site_title)
    {
        $new_site_title = sprintf(
            '<a href="%s" target="_top"><img src="%s" srcset="%s 2x" width="32" height="32" alt="" class="wp-embed-site-icon"/><span>%s</span></a>',
            esc_url( home_url() ),
            esc_url( get_site_icon_url( 32, plugins_url('../images/logo-mini.svg', __FILE__ ) ) ),
            esc_url( get_site_icon_url( 64, plugins_url('../images/logo-mini.svg', __FILE__ ) ) ),
            esc_html( get_bloginfo( 'name' ) )
        );

        return '<div class="wp-embed-site-title">' . $new_site_title . '</div>';
    }
}
