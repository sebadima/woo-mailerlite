<?php
/**
 * Plugin Name:     WooCommerce - Mailerlite
 * Plugin URI:      https://wordpress.org/plugins/woo-mailerlite/
 * Description:     Mailerlite integration for WooCommerce
 * Version:         1.0.2
 * Author:          flowdee
 * Author URI:      https://flowdee.de
 * Text Domain:     woo-mailerlite
 *
 * @author          flowdee
 * @copyright       Copyright (c) flowdee
 *
 * Copyright (c) 2017 - flowdee ( https://twitter.com/flowdee )
 */

// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'Woo_Mailerlite' ) ) {

    /**
     * Main Woo_Mailerlite class
     *
     * @since       1.0.0
     */
    class Woo_Mailerlite {

        /**
         * @var         Woo_Mailerlite $instance The one true Woo_Mailerlite
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true Woo_Mailerlite
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new Woo_Mailerlite();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {

            // Plugin name
            define( 'WOO_MAILERLITE_NAME', 'WooCommerce - Mailerlite' );

            // Plugin version
            define( 'WOO_MAILERLITE_VER', '1.0.2' );

            // Plugin path
            define( 'WOO_MAILERLITE_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'WOO_MAILERLITE_URL', plugin_dir_url( __FILE__ ) );

            // Plugin prefix
            define( 'WOO_MAILERLITE_PREFIX', 'woo_ml_' );

            // API Key
            if ( ! defined( 'MAILERLITE_WP_API_KEY' ) ) {
                $settings = get_option( 'woocommerce_mailerlite_settings' );
                $api_key = ( ! empty( $settings['api_key'] ) ) ? $settings['api_key'] : '';
                define( 'MAILERLITE_WP_API_KEY', $api_key );
            }

        }
        
        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {

            // Get out if WooCommerce is not active
            if ( ! class_exists( 'WC_Integration' ) )
                return;

            // Dependencies
            require_once 'vendor/autoload.php';
            require_once 'includes/shared/mailerlite-wp-functions.php';

            // Core functions and hooks
            require_once 'includes/functions.php';
            require_once 'includes/hooks.php';
            require_once 'includes/scripts.php';

            // Admin functions and hooks
            if ( is_admin() ) {
                require_once 'includes/admin/functions.php';
                require_once 'includes/admin/hooks.php';
                require_once 'includes/admin/ajax.php';
            }

            // Include our integration class.
            include_once 'includes/class.woo-mailerlite-integration.php';

            // Register the integration.
            add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
        }

        /**
         * Adding integration to WooCommerce.
         *
         * @param $integrations
         * @return array
         */
        public function add_integration( $integrations ) {

            $integrations[] = 'Woo_Mailerlite_Integration';

            return $integrations;
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {
            // Set filter for language directory
            $lang_dir = WOO_MAILERLITE_DIR . '/languages/';
            $lang_dir = apply_filters( 'woo_mailerlite_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'woo-mailerlite' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'woo-mailerlite', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/woo-mailerlite/' . $mofile;

            if( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/woo-mailerlite/ folder
                load_textdomain( 'woo-mailerlite', $mofile_global );
            } elseif( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/woo-mailerlite/languages/ folder
                load_textdomain( 'woo-mailerlite', $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'woo-mailerlite', false, $lang_dir );
            }
        }
    }
} // End if class_exists check

/**
 * The main function responsible for returning the one true Woo_Mailerlite
 * instance to functions everywhere
 * @since       1.0.0
 * @return      \Woo_Mailerlite The one true Woo_Mailerlite
 *
 */
function woo_ml_load() {
    return Woo_Mailerlite::instance();
}
add_action( 'plugins_loaded', 'woo_ml_load' );