<?php

/*
    Copyright 2016 Austin Goudge  (email : austin@opensceneryx.com)

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

        add_shortcode('navionics', array($this, 'navionicsMapShortcode'));
    }

    /**
     * Hook for 'the_posts' filter - detect whether we are on an OSX library item or category page and manufacture
     * a post if so.
     *
     * @param array $posts The array of posts to filter
     * @return array Filtered posts array (always either the passed-in array or an array with a single OSX post in it)
     */
    function init() {
        register_post_type( 'notice',
            array(
                'labels' => array(
                    'name' => __( 'Marine Notices' ),
                    'singular_name' => __( 'Marine Notice' )
                ),
                'public' => true,
                'has_archive' => true,
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
    }

    function navionicsMapShortcode($attrs)
    {
        return "<div class='test_map_div'></div>\n" .
                "<script>\n" .
                "var webapi = new JNC.Views.BoatingNavionicsMap({\n" .
                "tagId: '.test_map_div',\n" .
                "center: [  12.0, 46.0 ],\n" .
                "navKey: 'Navionics_webapi_01136'\n" .
                "});\n" .
                "webapi.showSonarControl(false);\n" .
                "</script>\n";
    }

    function addScriptsAndStyles() {
        wp_register_script('navionicsJS', 'http://webapiv2.navionics.com/dist/webapi/webapi.min.no-dep.js', array(), false, false);
        wp_register_style('navionicsCSS', 'http://webapiv2.navionics.com/dist/webapi/webapi.min.css', array(), false, 'all');
        wp_enqueue_script('navionicsJS');
        wp_enqueue_style('navionicsCSS');
    }
}
