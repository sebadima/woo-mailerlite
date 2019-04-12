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

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $response = false;

        $groups = woo_ml_settings_get_group_options( true );

        if ( $groups )
            $response = true;

        echo $response;
    }

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

        $orders_synced = woo_ml_sync_untracked_orders();

        if ( $orders_synced )
            $response = true;

        echo $response;
    }
    exit;
}
add_action( 'wp_ajax_nopriv_post_woo_ml_sync_untracked_orders', 'woo_ml_admin_ajax_sync_untracked_orders' );
add_action( 'wp_ajax_post_woo_ml_sync_untracked_orders', 'woo_ml_admin_ajax_sync_untracked_orders' );

function woo_ml_email_cookie() {

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        try{
            $email = isset($_POST['email']) ? $_POST['email'] : null;

            @setcookie('mailerlite_checkout_email', $email, time()+2419200, '/');
        }catch(\Exception $e) {
            return true;
        }
    }
    exit;
}
add_action( 'wp_ajax_nopriv_post_woo_ml_email_cookie', 'woo_ml_email_cookie' );
add_action( 'wp_ajax_post_woo_ml_email_cookie', 'woo_ml_email_cookie' );