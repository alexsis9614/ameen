<?php
    register_rest_route(STM_LMS_API, '/login/otp', array(
        'permission_callback' => '__return_true',
        'methods' => WP_REST_Server::CREATABLE,
        'args' => array(
            'phone' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'password' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'limit' => array(
                'required' => false,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ),
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $phone        = $request->get_param( 'phone' );
            $limit        = $request->get_param( 'limit' );
            $password     = $request->get_param( 'password' );

            if ( empty( $password ) ) {
                return array(
                    'message' => esc_html__('Enter a password', 'masterstudy-child'),
                    'success' => 'error'
                );
            }

            $otp          = new STM_THEME_CHILD_OTP();
            $valid_number = $otp->valid_phone( $phone );
            $data         = array(
                'phone'    => $valid_number,
                'password' => $password,
                'limit'    => $limit,
            );
            $response     = $otp->sign_in( $data );

            if ( isset( $response['user'] ) ) {
                $token = stm_lms_api_get_user_token( $response['user']->ID );

                unset( $response['user'] );

                $response['token'] = $token;
            }
            else {
                if ( 'success' === $response['status'] ) {
                    $response['status'] = 'verify';
                }
            }

            return $response;
        },
    ));