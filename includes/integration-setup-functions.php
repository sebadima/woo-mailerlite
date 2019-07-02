<?php
/**
 * Check if order tracking setup was finished
 */
function woo_ml_integration_setup_completed() {

    $integration_setup = get_option( 'woo_ml_integration_setup', false );

    return ( '1' == $integration_setup ) ? true : false;
}

/**
 * Mark order tracking setup as completed
 */
function woo_ml_complete_integration_setup() {
    add_option( 'woo_ml_integration_setup', true );
}

/**
 * Revoke order tracking setup completion
 */
function woo_ml_revoke_integration_setup() {
    delete_option( 'woo_ml_integration_setup' );
}

/**
 * Setup MailerLite integration
 *
 * 1.) Create custom fields
 * 2.) Create segments
 */
function woo_ml_setup_integration() {

    /*
     * Step 1: Setup custom fields
     */
    $setup_custom_fields = woo_ml_setup_integration_custom_fields();

    /*
     * Step 2: Setup segments
     */
    $setup_segments = woo_ml_setup_integration_segments();

    /*
     * Finally mark integration setup as completed
     */
    if ( $setup_custom_fields && $setup_segments ) {
        woo_ml_complete_integration_setup();
    }
}

/**
 * Setup Integration Custom Fields
 *
 * - Get existing custom fields via API
 * - Check if our custom fields were already created
 * - Create missing custom fields
 *
 * @return bool
 */
function woo_ml_setup_integration_custom_fields($fields = null) {
    $ml_fields = mailerlite_wp_get_custom_fields();
    if (! $fields)
        $fields = woo_ml_get_integration_custom_fields();

    if (is_array($ml_fields)) {
        foreach ($ml_fields as $ml_field) {
            if (isset($ml_field->key ) && isset( $fields[$ml_field->key]))
                unset($fields[$ml_field->key]);
        }
    }
    if ( sizeof($fields) > 0 ) {
        foreach ($fields as $field_data) {
            mailerlite_wp_create_custom_field($field_data);
        }
    }

    return true;
}

/**
 * Setup Integration Segments
 *
 * - Get existing custom fields via API
 * - TODO: As soon as API allows fetching segments, we'll check for existing ones here
 * - Create our segments
 *
 * @return bool
 */
function woo_ml_setup_integration_segments() {
    $group_id = woo_ml_get_option( 'group' );

    if ( empty( $group_id ) )
        return false;

    $ml_fields = mailerlite_wp_get_custom_fields(); 

    if ( ! $ml_fields )
        return false;

    /*
     * Prepare fields
     */
    $account_fields_by_key = [];

    foreach ( $ml_fields as $ml_field ) {
        $ml_field = (array) $ml_field;
        $account_fields_by_key[$ml_field['key']] = $ml_field;
    }

    /*
     * Prepare segments data
     */
    $segments = woo_ml_get_integration_segments();

    foreach ( $segments as $segment_key => $segment_data ) {
        if (!empty($segment_data['filter'])) {
            $data = [
                "title" => $segment_data["title"],
                "filter" => $segment_data["filter"]
            ];

            foreach ($data['filter']['rules'] as $rule_set_key => $rule_set) {
                foreach ($rule_set as $rule_key => $rule) {
                    if ($rule['args'][0] === 'group_id') {
                        $value = $group_id;
                    } else {
                        if ( ! isset( $account_fields_by_key[$rule['args'][0]] ) )
                            continue;

                        $field_array = $account_fields_by_key[$rule['args'][0]];
                        $value = $field_array['id'];
                    }
                    $data['filter']['rules'][$rule_set_key][$rule_key]['args'][0] = $value;
                }
            }

            foreach ($data['filter']['conditionSets'] as $rule_set_key => $rule_set) {
                foreach ($rule_set as $rule_key => $rule) {
                    if ($rule['args'][0] === 'group_id') {
                        $data['filter']['conditionSets'][$rule_set_key][$rule_key]['args'][0] = $group_id;
                    } else {

                        if ( ! isset( $account_fields_by_key[$rule['args'][0]['value']] ) )
                            continue;

                        $field_array = $account_fields_by_key[$rule['args'][0]['value']];

                        $data['filter']['conditionSets'][$rule_set_key][$rule_key]['args'][0]['value'] = $field_array["id"];
                        $data['filter']['conditionSets'][$rule_set_key][$rule_key]['args'][0]['display'] = $field_array["title"];
                    }
                }
            }

            $data['filter']['conditionSets'] = json_encode($data['filter']['conditionSets']);
            $segment = mailerlite_wp_create_segment( $data );
        }
    }

    return true;
}

/**
 * Get integration custom fields
 *
 * @return array
 */
function woo_ml_get_integration_custom_fields() {

    return array(
        'woo_orders_count' => array( 'title' => 'Woo Orders Count', 'type' => 'NUMBER' ),
        'woo_total_spent' => array( 'title' => 'Woo Total Spent', 'type' => 'NUMBER' ),
        'woo_last_order' => array( 'title' => 'Woo Last Order', 'type' => 'DATE' ),
        'woo_last_order_id' => array('title' => 'Woo Last Order ID', 'type' => 'NUMBER')
    );
}

/**
 * Get integration segments
 */
function woo_ml_get_integration_segments() {

    $segments = [
        "first_time_customers" => [
            "title" => "First Time Customers",
            "filter" => [
                "rules" => [
                    [
                        [
                            "operator" => "in_all_groups",
                            "args" => [
                                "group_id", // Will be replaced with field id
                            ]
                        ],
                        [
                            "operator" => "numeric_field_equals",
                            "args" => [
                                "woo_orders_count", // Will be replaced with field id
                                "1"
                            ]
                        ]
                    ]
                ],

                "conditionSets" => [
                    [
                        [
                            "args" => [
                                "group_id"
                            ],
                            "conditionType" => "Groups",
                            "operator" => "in_group"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce orders count", // Will be replaced with field Title
                                    "value" => "woo_orders_count" // Will be replaced with field id
                                ],
                                "1"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_equals"
                        ]
                    ]
                ],
                "matching" => "all",
            ]
        ],
        "zero_purchases" => [
            "title" => "Customers with 0 Purchases",
            "filter" => [
                "rules" => [
                    [
                        [
                            "operator" => "in_all_groups",
                            "args" => [
                                "group_id", // Will be replaced with field id
                            ]
                        ],
                        [
                            "operator" => "numeric_field_equals",
                            "args" => [
                                "woo_orders_count", // Will be replaced with field id
                                "0"
                            ]
                        ]
                    ]
                ],

                "conditionSets" => [
                    [
                        [
                            "args" => [
                                "group_id"
                            ],
                            "conditionType" => "Groups",
                            "operator" => "in_group"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce orders count", // Will be replaced with field Title
                                    "value" => "woo_orders_count" // Will be replaced with field id
                                ],
                                "0"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_equals"
                        ]
                    ]
                ],
                "matching" => "all",
            ]
        ],
        "high_spending" => [
            "title" => "High Spending Customers (more than $500)",
            "filter" => [
                "rules" => [
                    [
                        [
                            "operator" => "in_all_groups",
                            "args" => [
                                "group_id", // Will be replaced with field id
                            ]
                        ],
                        [
                            "operator" => "numeric_field_greater",
                            "args" => [
                                "woo_total_spent", // Will be replaced with field id
                                "500"
                            ]
                        ]
                    ]
                ],

                "conditionSets" => [
                    [
                        [
                            "args" => [
                                "group_id"
                            ],
                            "conditionType" => "Groups",
                            "operator" => "in_group"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce total spent", // Will be replaced with field Title
                                    "value" => "woo_total_spent" // Will be replaced with field id
                                ],
                                "500"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_greater"
                        ]
                    ]
                ],
                "matching" => "all",
            ]
        ],
        "repeat" => [
            "title" => "Repeat Customers (5 times and more)",
            "filter" => [
                "rules" => [
                    [
                        [
                            "operator" => "in_all_groups",
                            "args" => [
                                "group_id", // Will be replaced with field id
                            ]
                        ],
                        [
                            "operator" => "numeric_field_greater",
                            "args" => [
                                "woo_orders_count", // Will be replaced with field id
                                "5"
                            ]
                        ]
                    ]
                ],

                "conditionSets" => [
                    [
                        [
                            "args" => [
                                "group_id"
                            ],
                            "conditionType" => "Groups",
                            "operator" => "in_group"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce orders count", // Will be replaced with field Title
                                    "value" => "woo_orders_count" // Will be replaced with field id
                                ],
                                "5"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_greater"
                        ]
                    ]
                ],
                "matching" => "all",
            ]
        ],
        "high_spending_repeat" => [
            "title" => "High Spending Repeat Customers",
            "filter" => [
                "rules" => [
                    [
                        [
                            "operator" => "in_all_groups",
                            "args" => [
                                "group_id", // Will be replaced with field id
                            ]
                        ],
                        [
                            "operator" => "numeric_field_greater",
                            "args" => [
                                "woo_total_spent", // Will be replaced with field id
                                "500"
                            ]
                        ],
                        [
                            "operator" => "numeric_field_greater",
                            "args" => [
                                "woo_orders_count", // Will be replaced with field id
                                "5"
                            ]
                        ]
                    ]
                ],

                "conditionSets" => [
                    [
                        [
                            "args" => [
                                "group_id"
                            ],
                            "conditionType" => "Groups",
                            "operator" => "in_group"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce total spent", // Will be replaced with field Title
                                    "value" => "woo_total_spent" // Will be replaced with field id
                                ],
                                "500"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_greater"
                        ],
                        [
                            "args" => [
                                [
                                    "display" => "WooCommerce orders count", // Will be replaced with field Title
                                    "value" => "woo_orders_count" // Will be replaced with field id
                                ],
                                "5"
                            ],
                            "conditionType" => "Fields",
                            "operator" => "numeric_field_greater"
                        ]
                    ]
                ],
                "matching" => "all",
            ]
        ]
    ];

    return $segments;
}

function woo_ml_old_integration()
{
    return ! get_option('new_plugin_enabled');
}

function woo_ml_shop_not_active()
{
    return get_option('ml_shop_not_active');
}
