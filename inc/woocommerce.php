<?php
    add_action('profile_update', function ( $user_id ) {
        $first_name = get_user_meta($user_id, 'first_name', true);
        $last_name  = get_user_meta($user_id, 'last_name', true);

        update_user_meta($user_id, 'shipping_first_name', $first_name);
        update_user_meta($user_id, 'billing_first_name', $first_name);

        update_user_meta($user_id, 'shipping_last_name', $last_name);
        update_user_meta($user_id, 'billing_last_name', $last_name);
    });

    add_filter('default_checkout_billing_first_name', function ($value, $input) {
        return get_user_meta(get_current_user_id(), 'first_name', true);
    }, 10, 2);

    add_filter('default_checkout_billing_last_name', function ($value, $input) {
        return get_user_meta(get_current_user_id(), 'last_name', true);
    }, 10, 2);

    add_filter('default_checkout_billing_address_1', function () {
        return get_user_meta(get_current_user_id(), '7o1aflco80r', true);
    });