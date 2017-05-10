<?php
/**
 * Shows the final purchase total at the bottom of the checkout page
 *
 * @since 1.5
 * @return void
 */
function woo_ml_checkout_label() {

    if ( ! woo_ml_is_active() )
        return;

    $checkout = woo_ml_get_option('checkout', 'no' );

    if ( 'yes' != $checkout )
        return;

    $group = woo_ml_get_option('group' );

    if ( empty( $group ) )
        return;

    $label = woo_ml_get_option('checkout_label' );
    $preselect = woo_ml_get_option('checkout_preselect', 'no' );
    $hidden = woo_ml_get_option('checkout_hide', 'no' );

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
$checkout_position_hook = sprintf( 'woocommerce_%s',woo_ml_get_option('checkout_position', 'checkout_billing' ) );
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
 * Maybe initiate signup after purchase completed
 *
 * @param $payment_id
 */
function woo_ml_maybe_initiate_signup( $order_id ) {

    //woo_ml_debug_log('woo_ml_maybe_initiate_signup');

    $subscribed = get_post_meta( $order_id, '_woo_ml_subscribe', true );

    if ( $subscribed ) {
        woo_ml_process_signup( $order_id );
    }

}
add_action( 'woocommerce_checkout_order_processed', 'woo_ml_maybe_initiate_signup' );