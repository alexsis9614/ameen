<?php
    register_rest_route(STM_LMS_API, '/login/otp/verify', array(
        'permission_callback' => '__return_true',
        'methods' => WP_REST_Server::CREATABLE,
        'args' => array(
            'phone' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
            'code' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $phone        = $request->get_param('phone');
            $code         = $request->get_param('code');

            $otp          = new STM_THEME_CHILD_OTP();
            $data         = array(
                'phone' => $phone,
                'code'  => $code
            );
            $response     = $otp->verification( $data );

            if ( 'success' === $response['status'] ) {
                $response['status'] = 'password';
            }

            return $response;
        },
    ));