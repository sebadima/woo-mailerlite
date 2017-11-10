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