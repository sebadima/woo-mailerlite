<?php
if ( ! function_exists( 'flowdee_ml_api_key_validation') ) :
/**
 * Check Mailerlite API connection
 *
 * @param $api_key
 * @return bool
 */
function flowdee_ml_api_key_validation( $api_key ) {

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
        //echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        return false;
    }

    return false;
}
endif;

if ( ! function_exists( 'flowdee_ml_get_groups') ) :
/**
 * Get groups from API
 *
 * @param $api_key
 * @return array|bool
 */
function flowdee_ml_get_groups() {

    if ( ! flowdee_ml_api_key_exists() )
        return false;

    $groups = array();

    try {

        $mailerliteClient = new \MailerLiteApi\MailerLite( FLOWDEE_MAILERLITE_API_KEY );

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
        //echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        return false;
    }

    return $groups;
}
endif;

if ( ! function_exists( 'flowdee_ml_add_subscriber') ) :
/**
 * Add subscriber to group via API
 *
 * @param $group_id
 * @param array $subscriber
 * @return bool
 */
function flowdee_ml_add_subscriber( $group_id, $subscriber = array() ) {

    if ( ! flowdee_ml_api_key_exists() )
        return false;

    if ( empty( $group_id ) || ! is_numeric( $group_id ) )
        return false;

    $default_subscriber = array(
        'email' => '',
        'name' => '',
        'fields' => array(
            'name' => '',
            'last_name' => '',
            'company' => ''
        ),
        'type' => 'unconfirmed' // subscribed, active, unconfirmed
    );

    $subscriber = wp_parse_args( $subscriber, $default_subscriber );

    //woo_ml_debug( $subscriber );

    if ( empty( $subscriber['email'] ) )
        return false;

    try {

        $mailerliteClient = new \MailerLiteApi\MailerLite( FLOWDEE_MAILERLITE_API_KEY );

        $groupsApi = $mailerliteClient->groups();

        $addedSubscriber = $groupsApi->addSubscriber( $group_id, $subscriber ); // returns added subscriber

        if ( isset( $addedSubscriber->id ) ) {
            //woo_ml_debug( $addedSubscriber );
            return $addedSubscriber->id;
        } else {
            // $addedSubscriber->error->message
            return false;
        }

    } catch (Exception $e) {
        //echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        return false;
    }
}
endif;

if ( ! function_exists( 'flowdee_ml_api_key_exists') ) :
/**
 * Check wether API key exists or not
 *
 * @return bool
 */
function flowdee_ml_api_key_exists() {

    if ( defined( 'FLOWDEE_MAILERLITE_API_KEY' ) && ! empty( FLOWDEE_MAILERLITE_API_KEY ) )
        return true;

    return false;
}
endif;
