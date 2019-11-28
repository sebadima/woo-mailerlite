<?php
/**
 * Plugin Name:     WooCommerce - MailerLite
 * Plugin URI:      https://wordpress.org/plugins/woo-mailerlite/
 * Description:     Official MailerLite integration for WooCommerce. Track sales and campaign ROI, import products details, automate emails based on purchases and seamlessly add your customers to your email marketing lists via WooCommerce's checkout process.
 * Version:         1.4.4
 * Author:          MailerLite
 * Author URI:      https://mailerlite.com
 * Text Domain:     woo-mailerlite
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
                self::$instance->hooks();
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
            define( 'WOO_MAILERLITE_VER', '1.1.1' );

            // Plugin path
            define( 'WOO_MAILERLITE_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'WOO_MAILERLITE_URL', plugin_dir_url( __FILE__ ) );

            // Plugin prefix
            define( 'WOO_MAILERLITE_PREFIX', 'woo_ml_' );

            // API Key
            if ( ! defined( 'MAILERLITE_WP_API_KEY' ) ) {
                $option_value = get_option('woo_ml_key');
                $api_key = $option_value ? $option_value : '';
                define( 'MAILERLITE_WP_API_KEY', $api_key );
            }

            // Other
            define( 'WOO_MAILERLITE_MIN_PHP_VERSION', '5.6' );
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

            if ( ! $this->check_server_requirements() )
                return;

            // Dependencies
            require_once 'vendor/autoload.php';
            require_once 'includes/shared/mailerlite-wp-functions.php';

            // Core functions and hooks
            require_once 'includes/functions.php';
            require_once 'includes/integration-setup-functions.php';
            require_once 'includes/hooks.php';
            require_once 'includes/scripts.php';

            // Admin functions and hooks
            if ( is_admin() ) {
                require_once 'includes/admin/functions.php';
                require_once 'includes/admin/hooks.php';
                require_once 'includes/admin/ajax.php';
                require_once 'includes/admin/meta-boxes.php';
            }

            // Include our integration class.
            include_once 'includes/class.woo-mailerlite-integration.php';

            // Register the integration.
            add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
        }

        /**
         * Fire some hooks
         */
        private function hooks() {
            add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );
        }

        /**
         * Add plugin action links
         *
         * @param $links
         * @param $file
         * @return mixed
         */
        public function plugin_action_links( $links, $file ) {

            if ( $file !== 'woo-mailerlite/woo-mailerlite.php' )
                return $links;

            if ( ! $this->check_server_requirements() ) {
                $info = '<span style="color: red; font-weight: bold;">' . sprintf( esc_html__( 'PHP Version %1$s or newer required', 'woo-mailerlaite' ), WOO_MAILERLITE_MIN_PHP_VERSION ) . '</span>';
                array_unshift( $links, $info );
                return $links;
            }

            if ( class_exists( 'WC_Integration' ) ) {
                $settings_link = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=integration' ) . '">' . esc_html__( 'Settings', 'woo-mailerlite' ) . '</a>';
                array_unshift( $links, $settings_link );
            }

            return $links;
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

        /**
         * Check web server requirements
         *
         * @return bool
         */
        private function check_server_requirements() {

            if ( version_compare( phpversion(), WOO_MAILERLITE_MIN_PHP_VERSION, '<' ) )
                return false;

            return true;
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

function deactivate()
{
    require_once 'includes/functions.php';
    woo_ml_toggle_shop_connection(0);
}
register_deactivation_hook( __FILE__, 'deactivate' );

function activate()
{
    require_once 'includes/functions.php';
    woo_ml_toggle_shop_connection(1);
}
register_activation_hook( __FILE__, 'activate' );

function reload_checkout()
{
    require_once 'includes/functions.php';
    woo_ml_reload_checkout();
}
add_action('init', 'reload_checkout');

function woo_ml_deactivate_woo_ml_plugin($deactivate = false)
{
    if ($deactivate) {
        deactivate();
        
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins(plugin_basename( __FILE__ ), true);
    }
}