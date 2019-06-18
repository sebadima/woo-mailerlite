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

            $wooCommerceApi = $mailerliteClient->woocommerce();
            $result = $wooCommerceApi->validateAccount($api_key);
            
            if ( isset( $result ) && ! isset( $result['errors'] ) ) {
                $settings = get_option('woocommerce_mailerlite_settings');
                $settings['double_optin'] = $result['body']->double_optin;
                $settings['api_key'] = $api_key;
                $settings['api_status'] = true;
                
                update_option('woocommerce_mailerlite_settings', $settings);
                update_option('double_optin', $result['body']->double_optin);
                update_option('ml_account_authenticated', true);

                $groupsArray = [];
                $groups = $result['body']->groups;
                if ( sizeof( $groups ) > 0 ) {
                    foreach ( $groups as $group ) {
                        $groupsArray[] = (array) $group;
                    }
                }
                set_transient( 'woo_ml_groups', $groupsArray, 60 * 60 * 24 );
            }

        } catch (Exception $e) {
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
            return $groups;
        } catch (Exception $e) {
            return false;
        }
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
            $subscriber = $subscribersApi->find( $email );
            if ( isset( $subscriber->id ) ) {
                return $subscriber;
            } else {
                return false;
            }
        } catch (Exception $e) {
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
        try {

            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $subscribersApi = $mailerliteClient->subscribers();

            $subscriber_updated =$subscribersApi->update( $subscriber_email, $subscriber_data ); // returns updated subscriber
            if ( isset( $subscriber_updated->id ) ) {
                return $subscriber_updated;
            } else {
                return false;
            }
        } catch (Exception $e) {
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

            if ( isset( $result->enabled ) ) {
                $double_optin = $result->enabled == true ? 'yes' : 'no';
                update_option('double_optin', $double_optin );
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
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
            if ( isset( $field_added->id ) ) {
                return $field_added;
            } else {
                return false;
            }

        } catch (Exception $e) {
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

            $fields = $fieldsApi->getAccountFields();
            return $fields;

        } catch (Exception $e) {
            
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

            if ( is_wp_error( $response ) ) {
                return false;
            } else {
                $segment_added = json_decode( wp_remote_retrieve_body( $response ), false );

                return ( isset( $segment_added->id ) ) ? $segment_added : false;
            }

        } catch (Exception $e) {
            return false;
        }
    }
endif;

/**
 * Sends to api shop data needed to make back and forth connection with woo commerce
 * Api returns account id and subdomain used to for universal script
 * 
 * @param string $consumerKey
 * @param string $consumerSecret
 * @param string $apiKey
 * 
 * @return array|bool
 */
if (! function_exists('mailerlite_wp_set_consumer_data') ) :
    function mailerlite_wp_set_consumer_data($consumerKey, $consumerSecret, $group, $resubscribe) {
        if ( ! mailerlite_wp_api_key_exists())
            return false;

        $api_key = woo_ml_get_option( 'api_key' );
        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( $api_key );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            $store = home_url();
            $currency = get_option('woocommerce_currency');
            if (empty($group)) {
                return ['errors' => 'Please select a group.'];
            }
            if (strpos($store, 'https://') !== false ) {
                $result = $wooCommerceApi->setConsumerData( $consumerKey, $consumerSecret, $store, $currency, $group, $resubscribe);
                if ( isset( $result->account_id ) && (isset($result->account_subdomain))) {
                    update_option('account_id', $result->account_id);
                    update_option('account_subdomain', $result->account_subdomain);
                    update_option('new_plugin_enabled', true);
                    update_option('ml_shop_not_active', false);
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
 * 
 * @param array $order_data
 * 
 * @return bool|void
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
            
            $store = home_url();
            $wooCommerceApi->saveOrder($order_data, $store);
        } catch (Exception $e) {
            return false;
        }
    }
endif; 

/**
 * Get triggered on deactivate plugin event. Sends store name to api
 * to toggle its active status
 * 
 * @param bool $active_state
 * 
 * @return bool|void
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
            
            $store = home_url();
            $result =$wooCommerceApi->toggleShopConnection($store, $active_state);
        } catch (Exception $e) {
            
            return false;
        }
    }
endif; 
/**
 * Sending cart data on cart update
 * 
 * @param bool $cart_data
 * 
 * @return bool|void
 */
if (! function_exists('mailerlite_wp_send_cart')) :
    function mailerlite_wp_send_cart($cart_data) {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );

        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            
            $shop_url = home_url();
            
            $wooCommerceApi->sendCartData($shop_url, $cart_data);
        } catch (Exception $e) {
            return false;
        }
    }
endif;

/**
 * Sending order data on creation of order and/or order status change to processing 
 * @param array $data
 * @param string $event
 * 
 * @return bool
 */
if(! function_exists('mailerlite_wp_add_subscriber_and_save_order')) :
    function mailerlite_wp_add_subscriber_and_save_order($data, $event)
    {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );
        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            
            $shop_url = site_url();
            $data['shop_url'] = home_url();
            $data['order_url'] = $shop_url."/wp-admin/post.php?post=".$data['order_id']."&action=edit";
            //order_created case also takes care of processing sub if they have ticked the box to
            //receive newsletters
            if ($event === 'order_created') {
                $result = $wooCommerceApi->sendSubscriberData($data);
            
                if (isset($result->added_to_group) && isset($result->updated_fields)) {
                    return $result;
                } else {
                    return false;
                }
            } else {
                $wooCommerceApi->sendOrderProcessingData($data);
                return true;
            }
            
        } catch (Exception $e) {
            return false;
        }
    }
endif;
/**
 * API call to get all shop settings from the MailerLite side
 * 
 * @return array|bool
 */
if (! function_exists('mailerlite_wp_get_shop_settings_from_db')) :
    function mailerlite_wp_get_shop_settings_from_db()
    {
        if ( ! mailerlite_wp_api_key_exists() )
            return false;

        $api_key = woo_ml_get_option( 'api_key' );
        try {
            $mailerliteClient = new \MailerLiteApi\MailerLite( MAILERLITE_WP_API_KEY );

            $wooCommerceApi = $mailerliteClient->woocommerce();
            $result = $wooCommerceApi->getShopSettings(home_url());
            if (isset($result['body'])) {
                return $result['body'];
            } else {
                return false;
            }

        } catch (Exception $e) {
            return false;
        }
    }
endif;