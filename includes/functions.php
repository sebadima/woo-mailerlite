<?php
/**
 * Check if checkout action is active
 *
 * @return mixed
 */
function woo_ml_is_active() {
    $api_status = woo_ml_get_option( 'api_status', false );

    return $api_status;
}

/**
 * Get settings api key status
 *
 * @return string
 */
function woo_ml_settings_get_api_key_status() {

    $api_status = woo_ml_get_option( 'api_status', false );

    return ( $api_status ) ? '<span style="color: green;">' . __('Valid', 'woo-mailerlite' ) . '</span>' : '<span style="color: red;">' . __('Invalid', 'woo-mailerlite' ) . '</span>';
}

/**
 * Get settings group options
 *
 * @return array
 */
function woo_ml_settings_get_group_options( $force_refresh = false ) {

    $options = array();

    $groups = get_transient( 'woo_ml_groups' );

    if ( $force_refresh || empty( $groups ) ) {
        $groups = mailerlite_wp_get_groups();

        if ( ! empty( $groups ) )
            set_transient( 'woo_ml_groups', $groups, 60 * 60 * 24 ); // 24 hours
    }

    // Groups found
    if ( is_array( $groups ) && sizeof( $groups ) > 0 ) {

        $options[''] = __('Please select...', 'woo-mailerlite' );

        foreach ( $groups as $group ) {

            if ( isset( $group['id'] ) &&  isset( $group['name'] ) ) {
                $options[$group['id']] = $group['name'];
            }
        }

    // No groups found
    } else {
        $options[''] = __('No groups found', 'woo-mailerlite' );
    }

    return $options;
}

/**
 * Validate given API key
 *
 * @param $api_key
 * @return bool
 */
function woo_ml_validate_api_key( $api_key ) {

    if ( empty( $api_key ) )
        return false;

    $validation = mailerlite_wp_api_key_validation( $api_key );

    return $validation;
}

/**
 * Process order subscription
 * 1.) Maybe add customer to group
 * 2.) Set/update basic subscriber data
 *
 * @param $order_id
 */
function woo_ml_process_order_subscription( $order_id ) {
    $order = wc_get_order( $order_id );
    $customer_data = woo_ml_get_customer_data_from_order( $order_id );

    $subscribe = get_post_meta( $order_id, '_woo_ml_subscribe', true );
    $group = woo_ml_get_option('group' );
    $double_option = woo_ml_get_option('double_optin', false );

    $data = [];
    $data['email'] = $customer_data['email'];
    $data['type'] = ( 'yes' === $double_option ) ? 'unconfirmed' : 'subscribed';
    $data['checked_sub_to_mailist'] = $subscribe;
    $data['group_id'] = $group;
    $data['checkout_id'] = md5($customer_data['email']);
    $data['order_id'] = $order_id;

    $subscriber_fields = woo_ml_get_subscriber_fields_from_customer_data( $customer_data );
    if ( sizeof( $subscriber_fields ) > 0 )
        $data['fields'] = $subscriber_fields;

    $subscriber_result = mailerlite_wp_add_subscriber_and_save_order($data);

    if (isset($subscriber_result->added_to_group))
        woo_ml_complete_order_customer_subscribed( $order_id );
        
    if ( isset($subscriber_result->updated_fields) )
        woo_ml_complete_order_subscriber_updated( $order_id );
     
}

/**
 * Process order tracking
 * 1.) Get current data from MailerLite
 * 2.) Prepare order data
 * 3.) Merge both data
 * 4.) Update subscriber data with updated values
 *
 * @param $order_id
 */
function woo_ml_process_order_tracking( $order_id ) {

    $order_tracked = get_post_meta( $order_id, '_woo_ml_order_tracking', true );

    if ( $order_tracked ) // Prevent tracking orders multiple times
        return;

    $customer_data = woo_ml_get_customer_data_from_order( $order_id );

    $ml_subscriber_obj = mailerlite_wp_get_subscriber_by_email( $customer_data['email'] );

    // Customer exists on MailerLite
    if ( $ml_subscriber_obj ) {

        /*
         * Step 1: Get order tracking data from order
         */
        $tracking_data = woo_ml_get_order_tracking_data( $order_id );

        /*
         * Step 2: Merge tracking data with the one from MailerLite
         */
        $tracking_data = woo_ml_get_merged_order_tracking_data( $tracking_data, $ml_subscriber_obj );

        /*
         * Step 3: Update subscriber data via API
         */
        $subscriber_data = array(
            'fields' => array(
                'woo_orders_count' => $tracking_data['orders_count'],
                'woo_total_spent' => $tracking_data['total_spent'],
                'woo_last_order' => $tracking_data['last_order']
            )
        );

        woo_ml_debug_log( '>> $subscriber_data <<' );
        woo_ml_debug_log( $subscriber_data );
        woo_ml_debug_log( '-----------------------' );

        $subscriber_updated = mailerlite_wp_update_subscriber( $customer_data['email'], $subscriber_data );

        woo_ml_debug_log( '>> $subscriber_updated <<' );
        woo_ml_debug_log( $subscriber_updated );
        woo_ml_debug_log( '-----------------------' );

        if ( $subscriber_updated )
            woo_ml_complete_order_data_submitted( $order_id );
    }

    // Mark order data as tracked
    woo_ml_complete_order_tracking( $order_id );
}

/**
 * Get order tracking data merged with the one from MailerLite's subscriber object
 *
 * @param $tracking_data
 * @param $ml_subscriber_obj
 * @return array
 */
function woo_ml_get_merged_order_tracking_data( $tracking_data, $ml_subscriber_obj ) {

    /*
     * Step 1: Collect current tracking data from MailerLite subscriber object
     */
    $ml_tracking_data = array(
        'orders_count' => 0,
        'total_spent' => 0,
        'last_order' => ''
    );

    if ( isset( $ml_subscriber_obj->fields ) && is_array( $ml_subscriber_obj->fields ) ) {

        foreach ( $ml_subscriber_obj->fields as $ml_subscriber_field ) {

            if ( ! isset( $ml_subscriber_field->key ) || ! isset( $ml_subscriber_field->value ) || ! isset( $ml_subscriber_field->type ) )
                continue;

            // Get orders
            if ( 'woo_orders_count' === $ml_subscriber_field->key && ! empty( $ml_subscriber_field->value ) && is_numeric( $ml_subscriber_field->value ) )
                $ml_tracking_data['orders_count'] = intval( $ml_subscriber_field->value );

            // Get revenues
            if ( 'woo_total_spent' === $ml_subscriber_field->key && ! empty( $ml_subscriber_field->value ) && is_numeric( $ml_subscriber_field->value ) )
                $ml_tracking_data['total_spent'] = intval( $ml_subscriber_field->value );

            // Get last order date
            if ( 'woo_last_order' === $ml_subscriber_field->key && ! empty( $ml_subscriber_field->value ) )
                $ml_tracking_data['last_order'] = $ml_subscriber_field->value;
        }
    }

    woo_ml_debug_log( '>> $ml_tracking_data <<' );
    woo_ml_debug_log( $ml_tracking_data );
    woo_ml_debug_log( '-----------------------' );

    /*
     * Step 2: Merge order tracking data with the one on MailerLite
     */
    $tracking_data = array(
        'orders_count' => $ml_tracking_data['orders_count'] + $tracking_data['orders_count'],
        'total_spent' => $ml_tracking_data['total_spent'] + $tracking_data['total_spent'],
        'last_order' => $tracking_data['last_order']
    );

    return $tracking_data;
}

/**
 * Get order tracking data from order(s)
 *
 * @param int/array $order_ids
 * @return array|bool
 */
function woo_ml_get_order_tracking_data( $order_ids ) {

    if ( ! is_array( $order_ids ) && ! is_numeric( $order_ids ) )
        return false;

    if ( is_numeric( $order_ids ) )
        $order_ids = array( $order_ids );

    $tracking_data = array(
        'orders_count' => 0,
        'total_spent' => 0,
        'last_order' => ''
    );

    if ( sizeof( $order_ids ) > 0 ) {

        foreach ( $order_ids as $order_id ) {

            $order = wc_get_order( $order_id );

            $tracking_data['orders_count']++;

            $order_total = ( method_exists( $order, 'get_date_created' ) ) ? $order->get_total() : $order->total;
            woo_ml_debug_log( '$order_total >> ' . $order_total );
            $order_total = intval( $order_total );

            $tracking_data['total_spent'] += $order_total;

            $order_date = ( method_exists( $order, 'get_date_created' ) ) ? $order->get_date_created() : $order->date_created;
            woo_ml_debug_log( '$order_date >> ' . $order_date );
            $order_date = date( 'Y-m-d', strtotime( $order_date ) );

            $tracking_data['last_order'] = $order_date;
        }
    }

    return $tracking_data;
}

/**
 * Get customer data from order
 *
 * @param $order_id
 * @return array|bool
 */
function woo_ml_get_customer_data_from_order( $order_id ) {

    if ( empty( $order_id ) )
        return false;

    $order = wc_get_order( $order_id );

    if ( method_exists( $order, 'get_billing_email' ) ) {
        $data = array(
            'email' => $order->get_billing_email(),
            'name' => "{$order->get_billing_first_name()} {$order->get_billing_last_name()}",
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'company' => $order->get_billing_company(),
            'city' => $order->get_billing_city(),
            'postcode' => $order->get_billing_postcode(),
            'state' => $order->get_billing_state(),
            'country' => $order->get_billing_country(),
            'phone' => $order->get_billing_phone()
        );
    } else {
        // NOTE: Only for compatibility with WooCommerce < 3.0
        $data = array(
            'email' => $order->billing_email,
            'name' => "{$order->billing_first_name} {$order->billing_last_name}",
            'first_name' => $order->billing_first_name,
            'last_name' => $order->billing_last_name,
            'company' => $order->billing_company,
            'city' => $order->billing_city,
            'postcode' => $order->billing_postcode,
            'state' => $order->billing_state,
            'country' => $order->billing_country,
            'phone' => $order->billing_phone
        );
    }

    return $data;
}

/**
 * Get customer email address from order
 *
 * @param $order_id
 * @return bool|mixed|string
 */
function woo_ml_get_customer_email_from_order( $order_id ) {

    if ( empty( $order_id ) )
        return false;

    $order = wc_get_order( $order_id );

    return ( method_exists( $order, 'get_billing_email' ) ) ? $order->get_billing_email() : $order->billing_email;
}

/**
 * Get subscriber fields from customer data
 *
 * @param $customer_data
 * @return array
 */
function woo_ml_get_subscriber_fields_from_customer_data( $customer_data ) {

    $subscriber_fields = array();

    if ( ! empty( $customer_data['first_name'] ) )
        $subscriber_fields['name'] = $customer_data['first_name'];

    if ( ! empty( $customer_data['last_name'] ) )
        $subscriber_fields['last_name'] = $customer_data['last_name'];

    if ( ! empty( $customer_data['company'] ) )
        $subscriber_fields['company'] = $customer_data['company'];

    if ( ! empty( $customer_data['city'] ) )
        $subscriber_fields['city'] = $customer_data['city'];

    if ( ! empty( $customer_data['postcode'] ) )
        $subscriber_fields['zip'] = $customer_data['postcode'];

    if ( ! empty( $customer_data['state'] ) )
        $subscriber_fields['state'] = $customer_data['state'];

    if ( ! empty( $customer_data['country'] ) )
        $subscriber_fields['country'] = $customer_data['country'];

    if ( ! empty( $customer_data['phone'] ) )
        $subscriber_fields['phone'] = $customer_data['phone'];

    return $subscriber_fields;
}

/**
 * Get untracked orders
 *
 * @param array $args
 * @return array
 */
function woo_ml_get_untracked_orders( $args = array() ) {

    $defaults = array(
        'numberposts' => -1,
        'post_type'   => 'shop_order',
        'post_status' => 'wc-completed',
        'order'       => 'ASC', // old to new in order to get latest address data first
        'meta_key'     => '_woo_ml_order_tracked',
        'meta_compare' => 'NOT EXISTS'
    );

    $args = wp_parse_args( $args, $defaults );

    $order_posts = get_posts( $args );

    return $order_posts;
}

define( 'WOO_ML_SYNC_UNTRACKED_ORDERS_CYCLE', 5 );

/**
 * Bulk synchronize untracked orders
 *
 * @return bool
 */
function woo_ml_sync_untracked_orders() {

    $data = array();

    // Get orders
    $order_posts = woo_ml_get_untracked_orders( array( 'numberposts' => WOO_ML_SYNC_UNTRACKED_ORDERS_CYCLE ) );

    //echo 'Order posts found: ' . sizeof( $order_posts ) . '<br>';

    if ( is_array( $order_posts ) && sizeof( $order_posts ) > 0 ) {

        foreach ($order_posts as $order_post) {

            if (!isset($order_post->ID))
                continue;

            $order_id = $order_post->ID;
            //echo 'tracking order #' . $order_id . ' data<br>';

            $order_email = woo_ml_get_customer_email_from_order($order_id);

            if (isset($data[$order_email]) && is_array($data[$order_email])) {
                $data[$order_email][] = $order_id;
            } else {
                $data[$order_email] = array($order_id);
            }
        }
    }

    //woo_ml_debug( $data, '$data' );

    if ( sizeof( $data ) == 0 )
        return true;

    foreach ( $data as $customer_email => $order_ids ) {

        $ml_subscriber_obj = mailerlite_wp_get_subscriber_by_email( $customer_email );

        // Customer exists in MailerLite
        if ( $ml_subscriber_obj ) {

            if (sizeof($order_ids) > 1) {
                $last_order_id = array_values(array_slice($order_ids, -1))[0];
            } else {
                $last_order_id = $order_ids[0];
            }

            $customer_data = woo_ml_get_customer_data_from_order($last_order_id);

            // Collecting data
            $subscriber_data = array();

            if (!empty($customer_data['name']))
                $subscriber_data['name'] = $customer_data['name'];

            // Basic customer data fields
            $subscriber_fields = woo_ml_get_subscriber_fields_from_customer_data($customer_data);

            // Order tracking data
            $tracking_data = woo_ml_get_order_tracking_data($order_ids);
            $tracking_data = woo_ml_get_merged_order_tracking_data($tracking_data, $ml_subscriber_obj);

            $subscriber_fields['woo_orders_count'] = $tracking_data['orders_count'];
            $subscriber_fields['woo_total_spent'] = $tracking_data['total_spent'];
            $subscriber_fields['woo_last_order'] = $tracking_data['last_order'];

            $subscriber_data['fields'] = $subscriber_fields;

            //woo_ml_debug( $subscriber_data, $customer_email . ' >> $subscriber_data' );

            $subscriber_updated = mailerlite_wp_update_subscriber($customer_email, $subscriber_data);

            //echo 'Subscriber ' . $customer_email . ' updated with ' . sizeof( $order_ids ) . ' orders.<br>';

            if ( $subscriber_updated ) {

                foreach ( $order_ids as $order_id ) {
                    // Mark order customer data as being updated
                    woo_ml_complete_order_subscriber_updated( $order_id );

                    // Mark order data as being submitted
                    woo_ml_complete_order_data_submitted( $order_id );
                }
            }
        }

        // Mark order tracking as completed
        foreach ( $order_ids as $order_id ) {
            woo_ml_complete_order_tracking( $order_id );
        }
    }

    return true;
}

/**
 * Check whether a customer wants to be subscribed to our mailing list or not
 *
 * @param $order_id
 * @return bool
 */
function woo_ml_order_customer_subscribe( $order_id ) {

    $subscribe = get_post_meta( $order_id, '_woo_ml_subscribe', true );

    return ( '1' == $subscribe ) ? true : false;
}

/**
 * Mark order as "wants to be subscribed to mailing our list"
 *
 * @param $order_id
 */
function woo_ml_set_order_customer_subscribe( $order_id ) {
    add_post_meta( $order_id, '_woo_ml_subscribe', true );
}

/**
 * Check whether a customer was subscribed via API or not
 *
 * @param $order_id
 * @return bool
 */
function woo_ml_order_customer_subscribed( $order_id ) {

    $subscribed = get_post_meta( $order_id, '_woo_ml_subscribed', true );

    return ( '1' == $subscribed ) ? true : false;
}

/**
 * Mark order as "customer subscribed via API"
 *
 * @param $order_id
 */
function woo_ml_complete_order_customer_subscribed( $order_id ) {
    add_post_meta( $order_id, '_woo_ml_subscribed', true );
}

/**
 * Check whether a subscriber was updated from order or not
 *
 * @param $order_id
 * @return bool
 */
function woo_ml_order_subscriber_updated( $order_id ) {

    $subscriber_updated_from_order = get_post_meta( $order_id, '_woo_ml_subscriber_updated', true );

    return ( '1' == $subscriber_updated_from_order ) ? true : false;
}

/**
 * Mark order as "subscriber was updated"
 *
 * @param $order_id
 */
function woo_ml_complete_order_subscriber_updated( $order_id ) {
    add_post_meta( $order_id, '_woo_ml_subscriber_updated', true );
}

/**
 * Check whether order data was submitted via API or not
 *
 * @param $order_id
 * @return bool
 */
function woo_ml_order_data_submitted( $order_id ) {

    $data_submitted = get_post_meta( $order_id, '_woo_ml_order_data_submitted', true );

    return ( '1' == $data_submitted ) ? true : false;
}

/**
 * Mark order as "order data submitted"
 *
 * @param $order_id
 */
function woo_ml_complete_order_data_submitted( $order_id ) {
    add_post_meta( $order_id, '_woo_ml_order_data_submitted', true );
}

/**
 * Check whether order was already tracked or not
 *
 * @param $order_id
 * @return bool
 */
function woo_ml_order_tracking_completed( $order_id ) {

    $order_tracked = get_post_meta( $order_id, '_woo_ml_order_tracked', true );

    return ( '1' == $order_tracked ) ? true : false;
}

/**
 * Mark order as being tracked
 *
 * @param $order_id
 */
function woo_ml_complete_order_tracking( $order_id ) {
    add_post_meta( $order_id, '_woo_ml_order_tracked', true );
}

/**
 * Get settings option
 *
 * @param $key
 * @param null $default
 * @return null
 */
function woo_ml_get_option( $key, $default = null ) {

    $settings = get_option( 'woocommerce_mailerlite_settings' );

    return ( isset( $settings[$key] ) ) ? $settings[$key] : $default;
}

/**
 * Check whether we are on our admin pages or not
 *
 * @return bool
 */
function woo_ml_is_plugin_admin_area() {

    $screen = get_current_screen();

    return ( strpos( $screen->id, 'wc-settings') !== false ) ? true : false;
}

/**
 * Debug
 *
 * @param $args
 * @param bool $title
 */
function woo_ml_debug( $args, $title = false ) {

    if ( $title )
        echo '<h3>' . $title . '</h3>';

    echo '<pre>';
    print_r( $args );
    echo '</pre>';
}

/**
 * Debug to log file
 *
 * @param $message
 */
function woo_ml_debug_log( $message ) {

    if ( WP_DEBUG === true && defined( 'WOO_ML_DEBUG' ) && WOO_ML_DEBUG === true ) {
        if (is_array( $message ) || is_object( $message ) ) {
            error_log( print_r( $message, true ) );
        } else {
            error_log( $message );
        }
    }
}

//mailerlite universal script for tracking visits
function mailerlite_universal_woo_commerce()
{
    ?>
        <!-- MailerLite Universal -->
        <script>
        (function(m,a,i,l,e,r){ m['MailerLiteObject']=e;function f(){
        var c={ a:arguments,q:[]};var r=this.push(c);return "number"!=typeof r?r:f.bind(c.q);}
        f.q=f.q||[];m[e]=m[e]||f.bind(f.q);m[e].q=m[e].q||f.q;r=a.createElement(i);
        var _=a.getElementsByTagName(i)[0];r.async=1;r.src=l+'?v'+(~~(new Date().getTime()/1000000));
        _.parentNode.insertBefore(r,_);})(window, document, 'script', 'https://static.mailerlite.com/js/universal.js', 'ml');

        var ml_account = ml('accounts', '<?php echo get_option("account_id"); ?>', '<?php echo get_option("account_subdomain"); ?>');
        ml('ecommerce', 'visitor', 'woocommerce');  
        </script>
        <!-- End MailerLite Universal -->
    <?php 
}

if (get_option('account_id') && get_option('account_subdomain'))
{
    add_action('wp_head', 'mailerlite_universal_woo_commerce');
}
/**
 * Gets triggered on completed order event. Fetches order data
 * and passes it along to api
 */
function woo_ml_send_completed_order($order_id)
{
    $order = wc_get_order($order_id);
    $order_data['order'] = $order->get_data();
    $order_items = $order->get_items();

    $customer_email = $order->get_billing_email('view');
    $checkout_id = md5($customer_email);
    $order_data['order']['checkout_id'] = $checkout_id;

    foreach ($order_items as $key => $value) {
        $order_data['order']['line_items'][$key] = $value->get_data();
    }
    
    mailerlite_wp_send_order($order_data);
}

function woo_ml_get_double_optin()
{
    if (get_option('double_optin') === null) {
        return mailerlite_wp_get_double_optin();
    }
    return get_option('double_optin');
}

function woo_ml_send_cart($cookie_email = null)
{
    $cart = WC()->cart;
    $cart_items = $cart->get_cart();
    $customer = $cart->get_customer();
    $customer_email = $customer->get_email();
    if (! $customer_email) {
        $customer_email = isset($_COOKIE['mailerlite_checkout_email']) ? $_COOKIE['mailerlite_checkout_email'] : $cookie_email;
    }
    if (! empty($customer_email)) {
        $line_items = [];
        foreach($cart_items as $key => $value) {
            $line_items[] = $value;
        }

        $checkout_id = md5($customer_email);

        $shop_checkout_url = wc_get_checkout_url();
        $checkout_url = $shop_checkout_url.'?ml_checkout='.$checkout_id;
        
        $cart_data = [
            'id' => $checkout_id,
            'email' => $customer_email,
            'line_items' => $line_items,
            'abandoned_checkout_url' => $checkout_url
        ];
        mailerlite_wp_send_cart($cart_data);
    }
}