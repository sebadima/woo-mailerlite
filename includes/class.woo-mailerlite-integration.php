<?php
/**
 * Integration Demo Integration.
 *
 * @package  Woo_Mailerlite_Integration
 * @category Integration
 */

if ( ! class_exists( 'Woo_Mailerlite_Integration' ) ) :

    class Woo_Mailerlite_Integration extends WC_Integration {

        private $api_key = '';
        private $api_status = false;

        /**
         * Init and hook in the integration.
         */
        public function __construct() {
            global $woocommerce;

            $this->id                 = 'mailerlite';
            $this->method_title       = __( 'Mailerlite', 'woo-mailerlite' );
            $this->method_description = __( 'Mailerlite integration for WooCommerce', 'woo-mailerlite' );

            // Load the settings.
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables.
            $this->api_key          = $this->get_option( 'api_key' );
            $this->api_status       = $this->get_option( 'api_status', false );

            // Actions.
            add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );

            // Filters.
            add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );

        }


        /**
         * Initialize integration settings form fields.
         *
         * @return void
         */
        public function init_form_fields() {

            $this->form_fields = array(
                'api_key' => array(
                    'title'             => __( 'Mailerlite API Key', 'woo-mailerlite' ),
                    'type'              => 'text',
                    'description'       => sprintf( wp_kses( __( 'You can find your Developer API key <a href="%s" target="_blank">here</a>.', 'woo-mailerlite' ), array(  'a' => array( 'href' => array(), 'target' => array() ) ) ), esc_url( 'https://app.mailerlite.com/integrations/api/' ) ),
                    'desc_tip'          => false,
                    'default'           => '',
                ),
                'group' => array(
                    'title' 		=> __( 'Group', 'woo-mailerlite' ),
                    'type' 			=> 'select',
                    //'class'         => 'wc-enhanced-select',
                    'description' => __( 'The default group which will be taken for new subscribers', 'woo-mailerlite' ),
                    'default' 		=> '',
                    'options'		=> woo_ml_settings_get_group_options(),
                ),
                'checkout' => array(
                    'title'             => __( 'Checkout', 'woo-mailerlite' ),
                    'type'              => 'checkbox',
                    'label'             => __( 'Enable list subscription via checkout page', 'woo-mailerlite' ),
                    'default'           => 'yes'
                ),
                'checkout_position' => array(
                    'title' 		=> __( 'Position', 'woo-mailerlite' ),
                    'type' 			=> 'select',
                    'default' 		=> 'checkout_billing',
                    'options'		=> array(
                        'checkout_billing' => __( 'After billing details', 'woo-mailerlite' ),
                        'checkout_shipping' => __( 'After shipping details', 'woo-mailerlite' ),
                        'checkout_after_customer_details' => __( 'After customer details', 'woo-mailerlite' ),
                        'review_order_before_submit' => __( 'Before submit button', 'woo-mailerlite' )
                    ),
                ),
                'checkout_preselect' => array(
                    'title'             => __( 'Pre-select checkbox', 'woo-mailerlite' ),
                    'type'              => 'checkbox',
                    'label'             => __( 'Check in order to pre-select the signup checkbox by default', 'woo-mailerlite' ),
                    'default'           => 'yes'
                ),
                'checkout_hide' => array(
                    'title'             => __( 'Hide checkbox', 'woo-mailerlite' ),
                    'type'              => 'checkbox',
                    'label'             => __( 'Check in order to hide the checkbox. All customers will be subscribed automatically', 'woo-mailerlite' ),
                    'default'           => 'yes'
                ),
                'checkout_label' => array(
                    'title'             => __( 'Checkbox label', 'woo-mailerlite' ),
                    'type'              => 'text',
                    'description'       => __( 'The text which will be shown besides the checkbox', 'woo-mailerlite' ),
                    'desc_tip'          => false,
                    'default'           => __( 'Yes, I want to receive your newsletter.', 'woo-mailerlite' )
                ),
                'double_optin' => array(
                    'title'             => __( 'Double Opt-In', 'woo-mailerlite' ),
                    'type'              => 'checkbox',
                    'label'             => __( 'Check in order to force email confirmation before being added to your list', 'woo-mailerlite' ),
                    'default'           => 'yes'
                ),
            );
        }


        /**
         * Santize our settings
         * @see process_admin_options()
         */
        public function sanitize_settings( $settings ) {

            if ( isset( $settings ) && isset( $settings['api_key'] ) ) {

                $reset_groups = false;
                $refresh_groups = false;

                $api_status = $this->api_status;
                $api_key = $this->api_key;

                if ( empty( $settings['api_key'] ) ) {
                    $api_status = false;
                    $reset_groups = true;

                } elseif ( ! empty( $settings['api_key'] ) && $settings['api_key'] != $api_key ) {
                    $validation = woo_ml_validate_api_key( esc_html( $settings['api_key'] ) );
                    $api_status = ( $validation );

                    $reset_groups = true;
                    $refresh_groups = true;
                }

                // Store API validation
                $settings['api_status'] = $api_status;

                // Maybe reset groups
                if ( $reset_groups ) {
                    delete_transient( 'woo_ml_groups' );
                    woo_ml_debug_log( 'resetting groups' );
                }

                // Maybe refresh groups
                if ( $refresh_groups && $api_status ) {
                    woo_ml_debug_log( 'refreshing groups' );
                    $groups = woo_ml_settings_get_group_options( true );
                }

            }

            return $settings;
        }
    }

endif;
