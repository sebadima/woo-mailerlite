<?php
/**
 * Handle admin notices
 */
function woo_ml_admin_notices() {

    $notices = array();

    // Actions
    $admin_notice = ( isset( $_GET['woo_ml_admin_notice'] ) ) ? $_GET['woo_ml_admin_notice'] : null;

    // Debug
    /*
    $notices[] = array(
        'type' => 'warning',
        'dismiss' => false,
        'force' => false,
        'message' => __('Plugin settings has been successfully reset.', 'woo-mailerlite')
    );
    */

    // Integration setup (in case setup was not yet completed via settings handling)
    if ( woo_ml_is_active() && ! woo_ml_integration_setup_completed() ) {
        $notices[] = array(
            'type' => 'warning',
            'dismiss' => false,
            'force' => true,
            'message' => sprintf( wp_kses( __( 'In order to complete our integration setup, please <a href="%s">click here</a>.', 'woo-mailerlite' ), array(  'a' => array( 'href' => array() ) ) ), esc_url( woo_ml_get_complete_integration_setup_url() ) )
        );
    }

    // Integration setup completed
    if ( 'integration_setup_completed' === $admin_notice ) {
        $notices[] = array(
            'type' => 'success',
            'dismiss' => true,
            'force' => false,
            'message' => __( 'Integration setup has been successfully completed.', 'woo-mailerlite' )
        );
    }

    // Hook
    $notices = apply_filters( 'woo_ml_admin_notices', $notices );

    $is_plugin_area = true; // Maybe add a check here later

    // Output messages
    if ( sizeof( $notices ) > 0 ) {
        foreach ( $notices as $notice_id => $notice ) {

            // Maybe showing the notice on plugin related admin pages only
            if ( isset( $notice['force'] ) && false === $notice['force'] && ! $is_plugin_area )
                continue;

            $classes = 'woo-ml-notice notice';

            if ( ! empty( $notice['type'] ) )
                $classes .= ' notice-' . $notice['type'];

            if ( isset( $notice['dismiss'] ) && true === $notice['dismiss'] )
                $classes .= ' is-dismissible';

            ?>
            <div id="woo-ml-notice-<?php echo ( ! empty( $notice['id'] ) ) ? $notice['id'] : $notice_id; ?>" class="<?php echo $classes; ?>">
                <p><strong>WooCommerce MailerLite:</strong> <?php echo $notice['message']; ?></p>
            </div>
            <?php
        }
    }
}
add_action( 'admin_notices', 'woo_ml_admin_notices' );

/**
 * Handle admin actions
 */
function woo_ml_admin_actions() {

    if ( ! isset( $_GET['woo_ml_action'] ) )
        return;

    // Handle admin actions here
    if ( 'setup_integration' === $_GET['woo_ml_action'] ) {
        // Setup integration
        woo_ml_setup_integration();
        // Afterwards redirect to settings and show success notice
        wp_redirect( add_query_arg( 'woo_ml_admin_notice', 'integration_setup_completed', woo_ml_get_settings_page_url() ) );
        exit;
    }
}
add_action( 'admin_init', 'woo_ml_admin_actions' );

