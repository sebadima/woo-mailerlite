<?php
/**
 * Register meta boxes
 */
function woo_ml_register_meta_boxes() {

    add_meta_box(
        'woo-ml-order',
        '<span class="woo-ml-icon"></span>&nbsp;&nbsp;' . __( 'MailerLite', 'woo-mailerlite' ),
        'woo_ml_order_meta_box_output',
        'shop_order',
        'side',
        'low'
    );
}
add_action( 'add_meta_boxes', 'woo_ml_register_meta_boxes' );

/**
 * Outputs the content of the meta box
 */
function woo_ml_order_meta_box_output( $post ) {

    wp_nonce_field( basename( __FILE__ ), 'woo_ml_order_meta_box_nonce' );

    $order_id = $post->ID;

    $icon_yes = '<span class="dashicons dashicons-yes" style="color: #00A153;"></span>';
    $icon_no = '<span class="dashicons dashicons-no-alt" style="color: #a00;"></span>';
    ?>
    <?php $subscribe = get_post_meta( $order_id, '_woo_ml_subscribe', true ); ?>
    <p>
        <?php _e('Signed up for newsletter:', 'woo-mailerlite' ); ?> <?php echo ( $subscribe ) ? $icon_yes : $icon_no; ?>
    </p>
    <?php $subscribed = get_post_meta( $order_id, '_woo_ml_subscribed', true ); ?>
    <p>
        <?php _e('Subscribed via API:', 'woo-mailerlite' ); ?> <?php echo ( $subscribed ) ? $icon_yes : $icon_no; ?>
    </p>
    <?php $subscriber_updated = get_post_meta( $order_id, '_woo_ml_subscriber_updated', true ); ?>
    <p>
        <?php _e('Updated customer data via API:', 'woo-mailerlite' ); ?> <?php echo ( $subscriber_updated ) ? $icon_yes : $icon_no; ?>
    </p>
    <?php
    // Order Tracking related data
    if ( woo_ml_is_order_tracking_enabled() ) { ?>
        <?php $order_tracked = get_post_meta( $order_id, '_woo_ml_order_tracked', true ); ?>
        <p>
            <?php _e('Submitted order data via API:', 'woo-mailerlite' ); ?> <?php echo ( $order_tracked ) ? $icon_yes : $icon_no; ?>
        </p>
    <?php } ?>

    <?php
}

/**
 * Saves the custom meta input
 */
function woo_ml_order_meta_box_save( $post_id ) {

    // Checks save status
    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $is_valid_nonce = ( isset( $_POST[ 'woo_ml_order_meta_box_nonce' ] ) && wp_verify_nonce( $_POST[ 'woo_ml_order_meta_box_nonce' ], basename( __FILE__ ) ) ) ? 'true' : 'false';

    // Exits script depending on save status
    if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
        return;
    }

    // Checks for input and sanitizes/saves if needed
    /*
    if( isset( $_POST[ 'meta-text' ] ) ) {
        update_post_meta( $post_id, 'meta-text', sanitize_text_field( $_POST[ 'meta-text' ] ) );
    }
    */
}
add_action( 'save_post', 'woo_ml_order_meta_box_save' );