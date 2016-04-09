<?php

class MNPosttypes {
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
    }
}