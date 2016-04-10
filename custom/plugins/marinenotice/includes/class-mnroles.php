<?php

class MNRoles {
    /**
     * Register the 'harbourmaster' role and its capabilities.
     */
    function init() {
        // TODO Once stabilised, get rid of remove_role and move the rest into a register_activation_hook()
        remove_role('harbourmaster');
        add_role('harbourmaster', "Harbourmaster", array(
                                                                'read' => false,
                                                                'edit_posts' => false,
                                                                'delete_posts' => false,
                                                                'publish_posts' => false,
                                                                'upload_files' => true,
                                                                ) );

        $roles = array('harbourmaster', 'editor', 'administrator');

        // Loop through each role and assign capabilities
        foreach($roles as $the_role) {
            $role = get_role($the_role);

            $role->add_cap( 'read_notice');
            $role->add_cap( 'read_private_notices' );
            $role->add_cap( 'edit_notice' );
            $role->add_cap( 'edit_notices' );
            $role->add_cap( 'edit_others_notices' );
            $role->add_cap( 'edit_published_notices' );
            $role->add_cap( 'publish_notices' );
            $role->add_cap( 'delete_notices' );
            $role->add_cap( 'delete_others_notices' );
            $role->add_cap( 'delete_private_notices' );
            $role->add_cap( 'delete_published_notices' );
        }
    }
}
