<?php
/**
 * Ajax
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

/**
 * Refresh groups
 */
function woo_ml_admin_ajax_refresh_groups() {

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        /*
         * Action
         */
        $groups = woo_ml_settings_get_group_options( true );

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

/**
 * Refresh groups
 */
function woo_ml_admin_ajax_sync_untracked_orders() {

    // AJAX Call
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        /*
         * Action
         */
        //sleep(1); // Debugging only

        $orders_synced = woo_ml_sync_untracked_orders();

        if ( $orders_synced )
            $response = true;

        // response output
        //header( "Content-Type: application/json" );
        echo $response;
    }

    // IMPORTANT: don't forget to "exit"
    exit;
}
add_action( 'wp_ajax_nopriv_post_woo_ml_sync_untracked_orders', 'woo_ml_admin_ajax_sync_untracked_orders' );
add_action( 'wp_ajax_post_woo_ml_sync_untracked_orders', 'woo_ml_admin_ajax_sync_untracked_orders' );