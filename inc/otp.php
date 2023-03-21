<?php
    new STM_THEME_CHILD_OTP();

    class STM_THEME_CHILD_OTP extends STM_LMS_Eskiz_Uz
    {
        public $otp_enable   = false;
        public $actions      = array();
        public $nonce_action = 'stm_lms_sign_in';
        public $nonce        = '';
        public $testing      = false;

        public function __construct()
        {
            $this->testing = true;

            $email    = STM_LMS_Options::get_option('stm_api_eskiz_email', false);
            $password = STM_LMS_Options::get_option('stm_api_eskiz_password', false);

            if ( $email && $password ) {
                parent::__construct( $email, $password );
            }

            $this->otp_enable = STM_LMS_Options::get_option('stm_otp_enable', false);
            $this->nonce      = wp_create_nonce( $this->nonce_action );
            $this->actions    = array(
                'sign_in'      => $this->nonce_action,
                'verification' => $this->nonce_action . '_verification'
            );

            add_filter('wpcfto_options_page_setup', [$this, 'options'], 20, 1);

            if ( $this->otp_enable ) {
                foreach ( $this->actions as $method => $action ) {
                    $hook = 'wp_ajax_nopriv_' . $action;
                    $callback = [$this, $method];

                    if ( ! has_action( $hook, $callback ) ) {
                        add_action('wp_ajax_nopriv_' . $action, $callback);
                    }
                }
            }
        }

        public function valid_phone( $phone )
        {
            $valid_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);

            if ( $valid_number ) {
                return preg_replace("/[^0-9]/", '', $valid_number);
            }

            return $valid_number;
        }

        public function sign_in()
        {
            check_ajax_referer( $this->nonce_action );

            $response     = array(
                'message' => esc_html__('An error occurred, please try again later', 'masterstudy-child'),
                'status'  => 'error',
            );
            $request_body = file_get_contents( 'php://input' );
            $data         = json_decode( $request_body, true );

            if ( isset( $data['phone'] ) && ! empty( $data['phone'] ) ) {
                $valid_number = $this->valid_phone( $data['phone'] );

                if ( $valid_number ) {
                    $message = esc_html__('Enter confirmation code', 'masterstudy-child');

                    if ( $this->testing ) {
                        $send = $this->send_testing( $valid_number );
                        $message .= ' - ' . $send;
                    }
                    else {
                        $send = $this->send( $valid_number );
                    }

                    if ( $send ) {
                        $response     = array(
                            'message' => $message,
                            'status'  => 'success',
                        );
                    }
                }
                else {
                    $response['message'] = esc_html__('Validation phone number error', 'masterstudy-child');
                }
            }

            wp_send_json( $response );
        }

        /**
         * @throws Exception
         */
        public function verification()
        {
            check_ajax_referer( $this->nonce_action );

            $response     = array(
                'message' => esc_html__('An error occurred, please try again later', 'masterstudy-child'),
                'status'  => 'error',
            );
            $request_body = file_get_contents( 'php://input' );
            $data         = json_decode( $request_body, true );

            if ( isset( $data['code'] ) && ! empty( $data['code'] ) && isset( $data['phone'] ) && ! empty( $data['phone'] ) ) {
                $valid_number = $this->valid_phone( $data['phone'] );

                if ( $valid_number ) {
                    $response = $this->verification_code($valid_number, $data['code']);
                }

                if ( $response == false ) {
                    $response     = array(
                        'message' => esc_html__('The check code is not correct', 'masterstudy-child'),
                        'status'  => 'error',
                    );
                }
                else {
                    $user_email = $valid_number . '@gmail.com';
                    $user = wp_create_user( sanitize_user( $valid_number ), wp_generate_password(), $user_email );

                    $user_page = home_url();

                    if ( is_wp_error( $user ) ) {
                        $code_login  = 'existing_user_login';
                        $code_email  = 'existing_user_email';

                        $message   = esc_html__('Successfully logged in. Redirecting...', 'masterstudy-child');

                        if ( $user->get_error_code() === $code_login ) {
                            $user = get_user_by('login', sanitize_user( $valid_number ));
                        }
                        else if ( $user->get_error_code() === $code_email ) {
                            $user = get_user_by('email', $user_email);
                        }
                        else {
                            $response     = array(
                                'message' => $user->get_error_message(),
                                'status'  => 'error',
                            );
                        }

                        if ( ! is_wp_error( $user ) && $user && $user->exists() ) {
                            if ( STM_LMS_Instructor::is_instructor( $user->ID ) ) {
                                $user_page = STM_LMS_User::user_page_url($user->ID, true);
                            }
                            else {
                                $user_page = STM_LMS_User::enrolled_courses_url();
                            }
                        }
                    }
                    else {
                        $user      = new WP_User( $user );
                        $message   = esc_html__('Successfully register in. Redirecting...', 'masterstudy-child');
                        $user_page = STM_LMS_User::settings_url();
                    }

                    if ( $user && $user->exists() ) {
                        wp_clear_auth_cookie();
                        wp_set_current_user( $user->ID );
                        wp_set_auth_cookie($user->ID, true, is_ssl());

                        $response = array(
                            'user_page' => $user_page,
                            'message'   => $message,
                            'status'    => 'success',
                        );
                    }
                }
            }

            wp_send_json( $response );
        }

        public function options( $setups )
        {
            if ( ! empty( $setups ) ) {
                foreach ( $setups as &$setup ) {
                    if ( 'stm_lms_settings' !== $setup['option_name'] ) {
                        continue;
                    }

                    if ( array_key_exists('fields', $setup) ) {
                        $setup['fields']['stm_otp_auth'] = array(
                            'name'   => esc_html__( 'OTP', 'masterstudy-child' ),
                            'label'  => esc_html__( 'OTP Settings', 'masterstudy-child' ),
                            'icon'   => 'fas fa-sliders-h',
                            'fields' => array(
                                'stm_otp_enable' => array(
                                    'type'    => 'checkbox',
                                    'label'   => esc_html__( 'Enable OTP auth', 'masterstudy-child' ),
                                    'pro'     => true,
                                    'pro_url' => 'https://stylemixthemes.com/wordpress-lms-plugin/?utm_source=wpadmin-ms&utm_medium=ms-settings&utm_campaign=general-settings-get-pro',
                                ),
                                'stm_otp_email_phone' => array(
                                    'type' => 'radio',
                                    'label' => esc_html__('Authentication method', 'masterstudy-child'),
                                    'options' => array(
                                        'email_or_phone' => esc_html__('Email or Phone', 'masterstudy-child'),
                                        'phone' => esc_html__('Phone', 'masterstudy-child'),
                                    ),
                                    'dependency' => array(
                                        'key'   => 'stm_otp_enable',
                                        'value' => 'not_empty'
                                    ),
                                    'value' => 'phone',
                                    'pro'     => true,
                                    'pro_url' => 'https://stylemixthemes.com/wordpress-lms-plugin/?utm_source=wpadmin-ms&utm_medium=ms-settings&utm_campaign=general-settings-get-pro',
                                ),
                                'stm_api_eskiz_email' => array(
                                    'group'       => 'started',
                                    'columns'     => '33',
                                    'group_title' => esc_html__( 'Eskiz.uz API credentials', 'masterstudy-child' ),
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Email', 'masterstudy-child' ),
                                ),
                                'stm_api_eskiz_password' => array(
                                    'group'       => 'ended',
                                    'columns'     => '33',
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Password', 'masterstudy-child' ),
                                ),
                            )
                        );
                    }
                }
            }

            return $setups;
        }
    }