<?php
/**
 * Ajax
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate screenshot
 */
function woo_ml_admin_ajax_refresh_groups() {

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        /*
         * Action
         */
        $groups = woo_ml_settings_get_group_options( true );

        woo_ml_debug_log( $groups );

        if ( $groups )
            $response = true;

        // response output
        //header( "Content-Type: application/json" );
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
add_action( 'wp_ajax_nopriv_post_woo_ml_refresh_groups', 'woo_ml_admin_ajax_refresh_groups' );
add_action( 'wp_ajax_post_woo_ml_refresh_groups', 'woo_ml_admin_ajax_refresh_groups' );