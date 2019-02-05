<?php
/**
 * Functions inside this file are being used in different Mailerlite related WordPress plugins
 */

if ( ! function_exists( 'mailerlite_wp_api_key_validation') ) :
    /**
     * Check Mailerlite API connection
     *
     * @param $api_key
     * @return bool
     */
    function mailerlite_wp_api_key_validation( $api_key ) {

        if ( empty( $api_key ) )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( $api_key );

            $groups = $mailerliteClient->groups();
            $response = $groups->get();
            $results = $response->toArray();

            //woo_ml_debug( $response, 'mailerlite_wp_api_key_validation >> $response' );
            //woo_ml_debug( $results, 'mailerlite_wp_api_key_validation >> $results' );

            if ( is_array( $results ) && ! isset( $results[0]->error->message ) )
                return true;

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }

        return false;
    }
endif;

if ( ! function_exists( 'mailerlite_wp_api_key_exists') ) :
    /**
     * Check wether API key exists or not
     *
     * @return bool
     */
    function mailerlite_wp_api_key_exists() {

        if ( defined( 'MAILERLITE_WP_API_KEY' ) && ! empty( MAILERLITE_WP_API_KEY ) )
            return true;

        return false;
    }
endif;

if ( ! function_exists( 'mailerlite_wp_get_groups') ) :
    /**
     * Get groups from API
     *
     * @param $api_key
     * @return array|bool
     */
    function mailerlite_wp_get_groups() {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $groups = array();

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $groupsApi = $mailerliteClient->groups();
            $results = $groupsApi->get();
            $results = $results->toArray();

            if ( is_array( $results ) && ! isset( $results[0]->error->message ) ) {

                if ( sizeof( $results ) > 0 ) {
                    foreach ( $results as $result ) {
                        $groups[] = (array) $result;
                    }
                }
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }

        return $groups;
    }
endif;

if ( ! function_exists( 'mailerlite_wp_get_subscriber_by_email') ) :
    /**
     * Get subscriber from API by email
     *
     * @param $email
     * @return mixed
     */
    function mailerlite_wp_get_subscriber_by_email( $email ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        if ( empty( $email ) )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $subscribersApi = $mailerliteClient->subscribers();
            $subscriber = $subscribersApi->find( $email ); // returns object of subscriber by its email

            if ( isset( $subscriber->id ) ) {
                //woo_ml_debug( $subscriber );
                return $subscriber;
            } else {
                // $subscriber->error->message
                return false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists( 'mailerlite_wp_add_subscriber') ) :
    /**
     * Add subscriber to group via API
     *
     * @param $group_id
     * @param array $subscriber
     * @return bool
     */
    function mailerlite_wp_add_subscriber( $group_id, $subscriber = array() ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        if ( empty( $group_id ) || ! is_numeric( $group_id ) )
            return false;

        $default_subscriber = array(
            'email' => '',
            /*'name' => '',
            'fields' => array(
                'name' => '',
                'last_name' => '',
                'company' => ''
            ),*/
            'type' => 'unconfirmed' // subscribed, active, unconfirmed
        );

        $subscriber = wp_parse_args( $subscriber, $default_subscriber );

        //woo_ml_debug( $subscriber );

        if ( empty( $subscriber['email'] ) )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $groupsApi = $mailerliteClient->groups();

            $addedSubscriber = $groupsApi->addSubscriber( $group_id, $subscriber ); // returns added subscriber

            if ( isset( $addedSubscriber->id ) ) {
                //woo_ml_debug( $addedSubscriber );
                return $addedSubscriber;
            } else {
                // $addedSubscriber->error->message
                return false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists( 'mailerlite_wp_update_subscriber') ) :
    /**
     * Update subscriber via API
     *
     * @param $subscriber_email
     * @param array $subscriber_data
     * @return mixed
     */
    function mailerlite_wp_update_subscriber( $subscriber_email, $subscriber_data = array() ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        if ( empty( $subscriber_email ) )
            return false;

        //woo_ml_debug( $subscriber );

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $subscribersApi = $mailerliteClient->subscribers();

            $subscriber_updated =$subscribersApi->update( $subscriber_email, $subscriber_data ); // returns updated subscriber

            if ( isset( $subscriber_updated->id ) ) {
                //woo_ml_debug( $addedSubscriber );
                return $subscriber_updated;
            } else {
                // $addedSubscriber->error->message
                return false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists( 'mailerlite_wp_set_double_optin') ) :
    /**
     * Set Mailerlite double opt in status
     *
     * @param bool $status
     * @return bool
     */
    function mailerlite_wp_set_double_optin( $status ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $settingsApi = $mailerliteClient->settings();
            $result = $settingsApi->setDoubleOptin( $status );
            
            //woo_ml_debug_log( $result );

            if ( isset( $result->enabled ) ) {
                $double_optin = $result->enabled == true ? 'yes' : 'no';
                update_option('double_optin', $double_optin );
                return true;
            } else {
                // $result->error->message
                return false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists('mailerlite_wp_get_double_optin') ) :
    function mailerlite_wp_get_double_optin()
    {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $settingsApi = $mailerliteClient->settings();
            $result = $settingsApi->getDoubleOptin();

            if ( isset( $result->enabled ) ) {
                $double_optin = $result->enabled == true ? 'yes' : 'no';
                update_option('double_optin', $double_optin );
                return $double_optin ;
            } else {
                return 'no';
            }

        } catch (Exception $e) {
            dd($e);
            //return 'no';
        }
    }
    
endif;

if ( ! function_exists( 'mailerlite_wp_create_custom_field') ) :
    /**
     * Create custom field in MailerLite
     *
     * @param array $field_data
     * @return bool
     */
    function mailerlite_wp_create_custom_field( $field_data ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        if ( ! isset( $field_data['title'] ) || ! isset( $field_data['type'] ) )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $fieldsApi = $mailerliteClient->fields();
            $field_added = $fieldsApi->create( $field_data );
            //woo_ml_debug_log( $field_added );
            //woo_ml_debug( $field_added, '$field_added' );

            if ( isset( $field_added->id ) ) {
                return $field_added;
            } else {
                return false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists( 'mailerlite_wp_get_custom_fields') ) :
    /**
     * Get custom fields from MailerLite
     *
     * @param array $args
     * @return mixed
     */
    function mailerlite_wp_get_custom_fields( $args = array() ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $fieldsApi = $mailerliteClient->fields();

            $fields = $fieldsApi->get();
            //woo_ml_debug_log( $fields );

            return $fields;

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

if ( ! function_exists( 'mailerlite_wp_create_segment') ) :
    /**
     * Create segment in MailerLite
     *
     * @param array $segment_data
     * @return bool
     */
    function mailerlite_wp_create_segment( $segment_data ) {

        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );

        $data = json_encode( $segment_data );

        try {

            // TODO: As soon as the segments route is officially added to the API docs, we're using our library instead

            $response = wp_remote_post( 'https://api.mailerlite.com/api/v2/segments', array(
                'headers' => array(
                    'Cache-Control' => 'no-cache',
                    'Content-Type' => 'application/json' ,
                    'x-mailerlite-apikey' => $api_key
                ),
                'body' => $data
            ));

            //woo_ml_debug( $response );

            if ( is_wp_error( $response ) ) {
                return false;
            } else {
                $segment_added = json_decode( wp_remote_retrieve_body( $response ), false );

                return ( isset( $segment_added->id ) ) ? $segment_added : false;
            }

        } catch (Exception $e) {
            //echo 'Exception caught: ',  $e->getMessage(), "\n";
            return false;
        }
    }
endif;

/**
 * Sends to api shop data needed to make back and forth connection with woo commerce
 * Api returns account id and subdomain used to for universal script
 */
if (! function_exists('mailerlite_wp_set_consumer_data') ) :
    function mailerlite_wp_set_consumer_data($consumerKey, $consumerSecret, $apiKey) {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            $store = site_url();
            $currency = get_option('woocommerce_currency');
            if (strpos($store, 'https://') !== false ) {
                $result = $wooCommerceApi->setConsumerData( $consumerKey, $consumerSecret, $store, $apiKey, $currency);

                if ( isset( $result->account_id ) && (isset($result->account_subdomain))) {
                    update_option('account_id', $result->account_id);
                    update_option('account_subdomain', $result->account_subdomain);
                    update_option('new_plugin_enabled', true);
                } else if (isset($result->errors)) {
                    return ['errors' => $result->errors];
                }
                return true;
            } else {
                return ['errors' => 'Your shop url does not have the right security protocol'];
            }
        } catch (Exception $e) {
            return false;
        }
    }
endif;

/**
 * Sends completed order data to api to be evaluated and saved and/if trigger automations
 */
if ( ! function_exists( 'mailerlite_wp_send_order') ) :
    function mailerlite_wp_send_order($order_data)
    {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );

        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            
            $store = site_url();
            $wooCommerceApi->saveOrder($order_data, $store);
            
        } catch (Exception $e) {
            return false;
        }
    }
endif; 

/**
 * Get triggered on deactivate plugin event. Sends store name to api
 * to toggle its active status
 */
if ( ! function_exists( 'mailerlite_wp_toggle_shop_connection') ) :
    function mailerlite_wp_toggle_shop_connection($active_state)
    {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );

        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            
            $store = site_url();
            $result =$wooCommerceApi->toggleShopConnection($store, $active_state);
        } catch (Exception $e) {
            
            return false;
        }
    }
endif; 