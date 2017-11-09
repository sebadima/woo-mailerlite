<?php
/**
 * Get settings page url
 *
 * @return string
 */
function woo_mailerlite_get_settings_page_url() {
    return admin_url( 'admin.php?page=wc-settings&tab=integration' );
}