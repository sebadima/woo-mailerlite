<?php
/**
 * Get settings page url
 *
 * @return string
 */
function woo_ml_get_settings_page_url() {
    return admin_url( 'admin.php?page=wc-settings&tab=integration' );
}

/**
 * Get complete integration setup url
 *
 * @return string
 */
function woo_ml_get_complete_integration_setup_url() {
    return add_query_arg( 'woo_ml_action', 'setup_integration', woo_ml_get_settings_page_url() );
}