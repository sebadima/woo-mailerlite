<?php
/**
 * Check if order tracking setup was finished
 */
function woo_ml_integration_setup_completed() {

    $integration_setup = get_option( 'woo_ml_integration_setup', false );

    return ( '1' == $integration_setup ) ? true : false;
}

/**
 * Mark order tracking setup as completed
 */
function woo_ml_complete_integration_setup() {
    add_option( 'woo_ml_integration_setup', true );
}

/**
 * Revoke order tracking setup completion
 */
function woo_ml_revoke_integration_setup() {
    delete_option( 'woo_ml_integration_setup' );
}

/**
 * Setup MailerLite integration
 */
function woo_ml_setup_integration() {
    $setup_custom_fields = woo_ml_setup_integration_custom_fields();

    if ($setup_custom_fields)
        woo_ml_complete_integration_setup();
}

/**
 * Setup Integration Custom Fields
 *
 * - Get existing custom fields via API
 * - Check if our custom fields were already created
 * - Create missing custom fields
 *
 * @return bool
 */
function woo_ml_setup_integration_custom_fields($fields = null) {
    $ml_fields = mailerlite_wp_get_custom_fields();
    if (! $fields)
        $fields = woo_ml_get_integration_custom_fields();

    if (is_array($ml_fields)) {
        foreach ($ml_fields as $ml_field) {
            if (isset($ml_field->key ) && isset( $fields[$ml_field->key]))
                unset($fields[$ml_field->key]);
        }
    }
    if ( sizeof($fields) > 0 ) {
        foreach ($fields as $field_data) {
            mailerlite_wp_create_custom_field($field_data);
        }
    }

    return true;
}

/**
 * Get integration custom fields
 *
 * @return array
 */
function woo_ml_get_integration_custom_fields() {

    return array(
        'woo_orders_count' => array( 'title' => 'Woo Orders Count', 'type' => 'NUMBER' ),
        'woo_total_spent' => array( 'title' => 'Woo Total Spent', 'type' => 'NUMBER' ),
        'woo_last_order' => array( 'title' => 'Woo Last Order', 'type' => 'DATE' ),
        'woo_last_order_id' => array('title' => 'Woo Last Order ID', 'type' => 'NUMBER')
    );
}

function woo_ml_old_integration()
{
    return ! get_option('new_plugin_enabled');
}

function woo_ml_shop_not_active()
{
    return get_option('ml_shop_not_active');
}
