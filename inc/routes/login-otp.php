<?php
    register_rest_route(STM_LMS_API, '/login/otp', array(
        'permission_callback' => '__return_true',
        'methods' => WP_REST_Server::CREATABLE,
        'args' => array(
            'phone' => array(
                'required' => true,
                'sanitize_callback' => 'sanitize_text_field'
            ),
        ),
        'callback' => function ( WP_REST_Request $request ) {
            $phone        = $request->get_param('phone');

            $otp          = new STM_THEME_CHILD_OTP();
            $valid_number = $otp->valid_phone( $phone );
            $data         = array(
                'phone' => $valid_number
            );
            $response     = $otp->sign_in( $data );

            if ( 'success' === $response['status'] ) {
                $response['status'] = 'verify';
            }

            return $response;
        },
    ));