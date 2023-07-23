<?php
    register_rest_route(STM_LMS_API, '/login/account', array(
        'permission_callback' => '__return_true',
        'methods' => WP_REST_Server::CREATABLE,
        'args' => array(
            'phone' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'register' => array(
                'required' => true,
                'sanitize_callback' => 'rest_sanitize_boolean'
            ),
            'password' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'password_re' => array(
                'required' => false,
                'sanitize_callback' => 'sanitize_text_field'
            )
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $phone        = $request->get_param('phone');
            $register     = $request->get_param('register');
            $password     = $request->get_param('password');
            $password_re  = $request->get_param('password_re') ?: '';

            $otp          = new STM_THEME_CHILD_OTP();
            $valid_number = $otp->valid_phone( $phone );
            $data         = array(
                'phone'        => $valid_number,
                'register'     => $register,
                'password'     => $password,
                'password_re'  => $password_re,
            );
            $response     = $otp->create_account( $data );

            if ( isset( $response['user'] ) ) {
                $token = stm_lms_api_get_user_token( $response['user']->ID );

                unset( $response['user'] );

                $response['token'] = $token;
            }

            if ( 'success' === $response['status'] ) {
                $response['status'] = 'password';
            }

            return $response;
        },
    ));