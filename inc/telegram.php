<?php
    add_filter('stm_wpcfto_boxes', function ( $boxes ) {
        if ( is_array( $boxes ) && ! empty( $boxes ) ) {
            $boxes['stm_telegram_users_settings'] = array(
                'post_type' => array( 'telegram_subscribers' ),
                'label'     => esc_html__( 'Users Settings', 'masterstudy-child' ),
            );
        }

        return $boxes;
    });

    add_filter('stm_wpcfto_fields', function ( $fields ) {
        if ( is_array( $fields ) && ! empty( $fields ) ) {
            $fields['stm_telegram_users_settings'] = array(
                'section_telegram_settings'      => array(
                    'name'   => esc_html__( 'Settings', 'masterstudy-child' ),
                    'label'  => esc_html__( 'General Settings', 'masterstudy-child' ),
                    'icon'   => 'fa fa-cog',
                    'fields' => array(
                        'send_notification'         => array(
                            'type'  => 'radio',
                            'label' => esc_html__( 'Accept to send notifications', 'masterstudy-child' ),
                            'options' => array(
                                ''        => esc_html__( 'Pending', 'masterstudy-child' ),
                                'approve' => esc_html__( 'Approve', 'masterstudy-child' ),
                                'cancel'  => esc_html__( 'Cancel', 'masterstudy-child' ),
                            )
                        ),
                    ),
                ),
            );
        }

        return $fields;
    });

    add_action('stm_lms_woocommerce_send_message_approved', 'lms_theme_order_approved', 20, 3);

    if ( ! function_exists( 'lms_theme_order_approved' ) ) {
        function lms_theme_order_approved($course_data, $user_id, $order_id)
        {
            if ( ! empty( $order_id ) ) {
                $order = new WC_Order( $order_id );

                if ( ! empty( $order ) ) {
                    $user      = new WP_User( $order->get_user_id() );
                    $course_id = $course_data['item_id'];
                    $course    = get_post( $course_id );

                    if ( ! is_wp_error( $user ) && $user->exists() && ! is_wp_error( $course ) ) {
                        $message = sprintf(
                            esc_html__('Created new order #%d', 'masterstudy-child') . "\n" .
                            esc_html__('Course name: %s', 'masterstudy-child') . "\n" .
                            esc_html__('Course price: %s', 'masterstudy-child'),
                            $order_id,
                            $course->post_title,
                            wc_price( ( $course_data['price'] ?? $order->get_total() ) )
                        );

                        if ( ! empty( $order->get_coupon_codes() ) ) {
                            $message .= "\n" . sprintf(
                                esc_html__('Coupon codes: %s', 'masterstudy-child'),
                                implode(', ', $order->get_coupon_codes())
                            );
                        }

                        $args = array(
                            'post_type' => 'telegram_subscribers',
                            'post_per_page' => -1,
                            'nopaging' => true,
                            'meta_query' => array(
                                array(
                                    'key' => 'send_notification',
                                    'value' => 'approve'
                                )
                            )
                        );

                        $query = new WP_Query( $args );

                        if ( $query->have_posts() ) {
                            while ( $query->have_posts() ) : $query->the_post();
                                telegram_sendmessage(
                                    get_post_field( 'post_title', get_the_ID() ),
                                    $message,
                                    false
                                );
                            endwhile;
                        }
                    }
                }
            }
        }
    }

    add_shortcode('lms_send_telegram_statistic_month', function () {
        $query = new WC_Order_Query( array(
            'limit' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'return' => 'ids',
        ) );
        try {
            $orders = $query->get_orders();
        } catch (Exception $e) {
            telegram_log('', '', $e->getMessage());
        }

        return '123';
    });