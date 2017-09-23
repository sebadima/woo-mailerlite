<?php
/**
 * Check if checkout action is active
 *
 * @return mixed
 */
function woo_ml_is_active() {
    $api_status = woo_ml_get_option( 'api_status', false );

    return $api_status;
}

/**
 * Get settings api key status
 *
 * @return string
 */
function woo_ml_settings_get_api_key_status() {

    $api_status = woo_ml_get_option( 'api_status', false );

    return ( $api_status ) ? '<span style="color: green;">' . __('Valid', 'woo-mailerlite' ) . '</span>' : '<span style="color: red;">' . __('Invalid', 'woo-mailerlite' ) . '</span>';
}

/**
 * Get settings group options
 *
 * @return array
 */
function woo_ml_settings_get_group_options( $force_refresh = false ) {

    $options = array();

    $groups = get_transient( 'woo_ml_groups' );

    if ( $force_refresh || empty( $groups ) ) {
        $groups = flowdee_ml_get_groups();

        if ( ! empty( $groups ) )
            set_transient( 'woo_ml_groups', $groups, 60 * 60 * 24 ); // 24 hours
    }

    // Groups found
    if ( is_array( $groups ) && sizeof( $groups ) > 0 ) {

        $options[''] = __('Please select...', 'woo-mailerlite' );

        foreach ( $groups as $group ) {

            if ( isset( $group['id'] ) &&  isset( $group['name'] ) ) {
                $options[$group['id']] = $group['name'];
            }
        }

    // No groups found
    } else {
        $options[''] = __('No groups found', 'woo-mailerlite' );
    }

    return $options;
}

/**
 * Validate given API key
 *
 * @param $api_key
 * @return bool
 */
function woo_ml_validate_api_key( $api_key ) {

    if ( empty( $api_key ) )
        return false;

    $validation = flowdee_ml_api_key_validation( $api_key );

    return $validation;
}

/**
 * Process group signup(s)
 *
 * @param $order_id
 */
function woo_ml_process_signup( $order_id ) {

    woo_ml_debug_log( '>> processing signup' );

    $group = woo_ml_get_option('group' );

    woo_ml_debug_log('$group >> ' . $group );

    if ( empty( $group ) )
        return;

    $order = wc_get_order( $order_id );

    if( method_exists( $order, 'get_billing_email' ) ) {
        $data = array(
            'email' => $order->get_billing_email(),
            'name' => "{$order->get_billing_first_name()} {$order->get_billing_last_name()}",
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
        );
    } else {
        // NOTE: for compatibility with WooCommerce < 3.0
        $data = array(
            'email' => $order->billing_email,
            'name' => "{$order->billing_first_name} {$order->billing_last_name}",
            'first_name' => $order->billing_first_name,
            'last_name' => $order->billing_last_name,
        );
    }

    woo_ml_debug_log( '$data' );
    woo_ml_debug_log( $data );

    $double_option = woo_ml_get_option('double_optin', false );

    woo_ml_debug_log( 'Double Optin? ' . $double_option );

    if ( empty( $data['email'] ) )
        return;

    $subscriber = array(
        'email' => $data['email'],
        'name' => ( ! empty( $data['name'] ) ) ? $data['name'] : '',
        'fields' => array(
            'name' => ( ! empty( $data['first_name'] ) ) ? $data['first_name'] : '',
            'last_name' => ( ! empty( $data['last_name'] ) ) ? $data['last_name'] : '',
        ),
        'type' => ( 'yes' === $double_option ) ? 'unconfirmed' : 'subscribed' // subscribed, active, unconfirmed
    );

    woo_ml_debug_log( '$subscriber' );
    woo_ml_debug_log( $subscriber );

    $added = flowdee_ml_add_subscriber( $group, $subscriber );

    woo_ml_debug_log( '$added >> ' . $added );

    if ( $added )
        $order->add_order_note( __( 'Customer successfully subscribed to mailing list(s).', 'woo-mailerlite' ) );
}

/**
 * Get settings option
 *
 * @param $key
 * @param null $default
 * @return null
 */
function woo_ml_get_option( $key, $default = null ) {

    $settings = get_option( 'woocommerce_mailerlite_settings' );

    return ( isset( $settings[$key] ) ) ? $settings[$key] : $default;
}

/**
 * Debug
 *
 * @param $args
 * @param bool $title
 */
function woo_ml_debug( $args, $title = false ) {

    if ( $title )
        echo '<h3>' . $title . '</h3>';

    echo '<pre>';
    print_r( $args );
    echo '</pre>';
}

/**
 * Debug to log file
 *
 * @param $message
 */
function woo_ml_debug_log( $message ) {

    if ( WP_DEBUG === true && defined( 'WOO_ML_DEBUG' ) && WOO_ML_DEBUG === true ) {
        if (is_array( $message ) || is_object( $message ) ) {
            error_log( print_r( $message, true ) );
        } else {
            error_log( $message );
        }
    }
}