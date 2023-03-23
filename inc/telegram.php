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