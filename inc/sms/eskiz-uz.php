<?php
    class STM_LMS_Eskiz_Uz
    {
        /**
         * @var string $baseUrl
         */
        public $baseUrl = "https://notify.eskiz.uz/api/";
        public $email, $password, $from, $endpoint, $callback_url, $contact_id;
        public $token;
        public $token_type;
        public $last_request;

        /**
         * STM_LMS_Eskiz_Uz constructor.
         * @param $email
         * @param $password
         */
        public function __construct($email, $password)
        {
            $this->endpoint     = 'verification';
            $this->email        = $email;
            $this->password     = $password;
            $this->from         = 4546;
            $this->callback_url = esc_url( STM_LMS_User::login_page_url() . $this->path_trim( $this->endpoint ) );

            add_action( 'init', array( $this, 'rewrite_endpoint' ) );
        }

        public function path_trim( $path )
        {
            return '/' . ltrim( $path, '/' );
        }

        public function rewrite_endpoint()
        {
            $settings = get_option( 'stm_lms_settings', array() );

            if ( empty( $settings['user_url'] ) ) {
                $endpoint = $this->path_trim( $this->endpoint );
            }
            else {
                $endpoint = $this->path_trim( $settings['user_url'] . '/' . $this->endpoint );
            }

            add_rewrite_endpoint( $endpoint, EP_ALL );

            flush_rewrite_rules();
        }

        public function get_route($route)
        {
            return $this->baseUrl . $route . '/';
        }

        public function validation()
        {
            return ! is_wp_error( $this->last_request ) && isset( $this->last_request['body'] ) && !empty( $this->last_request['body'] );
        }

        public function send_error()
        {
            $response = array(
                'message' => esc_html__('An error occurred, please try again later', 'masterstudy-child'),
                'status'  => 'error',
            );

            if ( is_wp_error( $this->last_request ) ) {
                $response['message'] = $this->last_request->get_error_message();
            }
            else if ( wp_remote_retrieve_response_code( $this->last_request ) !== 200 ) {
                $error_message = '';
                $response      = json_decode( wp_remote_retrieve_body( $this->last_request ), true );

                if ( isset( $response['error'] ) ) {
                    $error_message = $response['error']['message'];
                }

                $response['message'] = $error_message;
            }

            wp_send_json( $response );
        }

        public function get_headers()
        {
            return array(
                'Authorization' => 'Bearer ' . $this->token
            );
        }

        public function set_verification_code( $phone )
        {
            $verification_code = rand(10000, 99999);
            $option            = sanitize_key('stm_lms_sign_in_verification_' . $phone);

            set_transient($option, $verification_code, HOUR_IN_SECONDS);

            return $verification_code;
        }

        public function verification_code( $phone, $verification_code )
        {
            $option   = sanitize_key('stm_lms_sign_in_verification_' . $phone);
            $code     = get_transient( $option );

            $response = ( absint( $code ) === absint( $verification_code ) );

            if ( $response ) {
                delete_transient( $option );
            }

            return $response;
        }

        public function login()
        {
            $request = wp_remote_post(
                $this->get_route('auth/login'),
                array(
                    'body' => array(
                        'email' => $this->email,
                        'password' => $this->password
                    )
                )
            );
            $response = false;

            $this->last_request = $request;

            if ( $this->validation() ) {
                $body = json_decode( $this->last_request['body'], true );
                if ( 'token_generated' === $body['message'] ) {
                    $this->token = $body['data']['token'];
                    $this->token_type = $body['token_type'];

                    $response = true;
                }
            }
            else if ( wp_remote_retrieve_response_code( $this->last_request ) !== 200 ) {
                $this->send_error();
            }
            else {
                $this->send_error();
            }

            return $response;
        }

        public function set_contact( $phone )
        {
            $response = $this->login();

            $request  = wp_remote_post(
                $this->get_route('contact'),
                array(
                    'headers' => $this->get_headers(),
                    'body' => array(
                        'group' => 'group_id',
                        'mobile_phone' => $phone
                    )
                )
            );

            $this->last_request = $request;

            if ( $this->validation() ) {
                $body = json_decode( $this->last_request['body'], true );
                if ( 'success' === $body['status'] ) {
                    $this->contact_id = $body['data']['contact_id'];

                    $response = true;
                }
                else {
                    $response = false;
                }
            }
            else if ( wp_remote_retrieve_response_code( $this->last_request ) !== 200 ) {
                $this->send_error();
            }
            else {
                $this->send_error();
            }

            return $response;
        }

        public function send( $phone )
        {
            $verification_code = $this->set_verification_code( $phone );

            $response = $this->login();

            if ( ! $response ) {
                return $response;
            }

            $request  = wp_remote_post(
                $this->get_route('message/sms/send'),
                array(
                    'headers' => $this->get_headers(),
                    'body' => array(
                        'mobile_phone' => $phone,
                        'message'      => sprintf(
                            esc_html__("Verification code: %d \nDont tell it to anyone.", 'masterstudy-child'),
                            $verification_code
                        ),
                        'from'         => $this->from,
                        'callback_url' => $this->callback_url,
                    )
                )
            );

            $this->last_request = $request;

            if ( $this->validation() ) {
                $body = json_decode( $this->last_request['body'], true );
                if ( in_array( $body['status'], array('success', 'waiting') ) ) {
                    $response = true;
                }
                else {
                    $response = false;
                }
            }
            else if ( wp_remote_retrieve_response_code( $this->last_request ) !== 200 ) {
                $this->send_error();
            }
            else {
                $this->send_error();
            }

            return $response;
        }
    }