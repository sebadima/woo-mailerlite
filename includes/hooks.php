<?php
/**
 * Shows the final purchase total at the bottom of the checkout page
 *
 * @since 1.5
 * @return void
 */
function woo_ml_checkout_label() {

    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() ***');

    if ( ! woo_ml_is_active() )
        return;

    $checkout = woo_ml_get_option('checkout', 'no' );

    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() >> $checkout: ' . $checkout . ' ***');

    if ( 'yes' != $checkout )
        return;

    $group = woo_ml_get_option('group' );

    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() >> $group: ' . $group . ' ***');

    if ( empty( $group ) )
        return;

    $label = woo_ml_get_option('checkout_label' );
    $preselect = woo_ml_get_option('checkout_preselect', 'no' );
    $hidden = woo_ml_get_option('checkout_hide', 'no' );

    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() >> $label: ' . $label . ' ***');
    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() >> $preselect: ' . $preselect . ' ***');
    //woo_ml_debug_log( '*** WOO MAILERLITE >> woo_ml_checkout_label() >> $hidden: ' . $hidden . ' ***');

    if ( 'yes' === $hidden ) {
        ?>
        <input name="woo_ml_subscribe" type="hidden" id="woo_ml_subscribe" value="1" checked="checked" />
        <?php
    } else { ?>
        <p id="woo-ml-subscribe">
            <input name="woo_ml_subscribe" type="checkbox" id="woo_ml_subscribe"
                   value="1" <?php if ( 'yes' === $preselect) echo 'checked="checked"'; ?> />
            <label for="woo_ml_subscribe"><?php echo stripslashes( $label) ; ?></label>
        </p>
    <?php }
}
$checkout_position = woo_ml_get_option('checkout_position', 'checkout_billing' );
$checkout_position_hook = 'woocommerce_' . $checkout_position;
//woo_ml_debug_log( '*** WOO MAILERLITE >> Firing checkout position hook: ' . $checkout_position_hook . ' ***');
add_action( $checkout_position_hook, 'woo_ml_checkout_label', 20 );

/**
 * Maybe prepare signup
 *
 * @param $order_id
 */
function woo_ml_checkout_maybe_prepare_signup( $order_id ) {

    if ( isset( $_POST['woo_ml_subscribe'] ) && '1' == $_POST['woo_ml_subscribe'] ) {
        update_post_meta( $order_id, '_woo_ml_subscribe', true );
    }
}
add_action( 'woocommerce_checkout_update_order_meta', 'woo_ml_checkout_maybe_prepare_signup' );

/**
 * Process checkout completed
 *
 * @param $order_id
 */
function woo_ml_process_checkout_completed( $order_id ) {

    woo_ml_debug_log( '*** WOO MAILERLITE - CHECKOUT COMPLETED >> START ***' );

    woo_ml_process_order_subscription( $order_id );

    woo_ml_debug_log( '*** WOO MAILERLITE - CHECKOUT COMPLETED >> END ***' );
}
add_action( 'woocommerce_checkout_order_processed', 'woo_ml_process_checkout_completed' );

/**
 * Process order completed (and finally paid)
 *
 * @param $order_id
 */
function woo_ml_process_order_completed( $order_id ) {

    woo_ml_debug_log( '*** WOO MAILERLITE - ORDER COMPLETED >> END ***' );

    if ( woo_ml_is_order_tracking_enabled() ) {
        woo_ml_process_order_tracking( $order_id );
    }

    woo_ml_debug_log( '*** WOO MAILERLITE - ORDER COMPLETED >> END ***' );
}
add_action( 'woocommerce_order_status_completed', 'woo_ml_process_order_completed' );