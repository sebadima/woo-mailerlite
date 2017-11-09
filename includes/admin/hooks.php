<?php
/**
 * Handle admin actions
 */
function woo_mailerlite_admin_actions() {

    if ( ! isset( $_GET['woo_mailerlite_action'] ) )
        return;

    // Handle admin actions here
}
add_action( 'admin_init', 'woo_mailerlite_admin_actions' );