<?php
    register_rest_route(STM_LMS_API, '/reset-password', array(
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
            'password_re' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $phone        = $request->get_param('phone');
            $password     = $request->get_param('password');
            $password_re  = $request->get_param('password_re');

            $otp          = new STM_THEME_CHILD_OTP();
            $valid_number = $otp->valid_phone( $phone );
            $data         = array(
                'phone'       => $valid_number,
                'password'    => $password,
                'password_re' => $password_re
            );

            return $otp->reset_password( $data );
        },
    ));