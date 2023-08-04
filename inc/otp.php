<?php
    new STM_THEME_CHILD_OTP();

    class STM_THEME_CHILD_OTP extends STM_LMS_Eskiz_Uz
    {
        public $otp_enable           = false;
        public $actions              = array();
        public $prefix               = 'stm_lms_';
        public $nonce_action         = 'sign_in';
        public $nonce                = '';
        public $lost_password_nonce  = '';
        public $testing              = false;
        public $routes               = false;
        public $path_api             = __DIR__ . '/routes';

        public function __construct()
        {
            $this->testing = STM_LMS_Options::get_option('stm_otp_testing', false);

            $this->nonce_action = $this->prefix . $this->nonce_action;

            $email    = STM_LMS_Options::get_option('stm_api_eskiz_email', false);
            $password = STM_LMS_Options::get_option('stm_api_eskiz_password', false);

            if ( $email && $password ) {
                parent::__construct( $email, $password );
            }

            $this->otp_enable           = STM_LMS_Options::get_option('stm_otp_enable', false);
            $this->nonce                = wp_create_nonce( $this->nonce_action );
            $this->lost_password_nonce  = wp_create_nonce( $this->prefix . 'lost_password' );
            $this->actions              = array(
                'sign_in'         => $this->nonce_action,
                'lost_password'   => $this->prefix . 'custom_lost_password',
                'reset_password'  => $this->prefix . 'custom_reset_password',
                'verification'    => $this->nonce_action . '_verification',
                'create_account'  => $this->nonce_action . '_create_account'
            );
            $this->routes               = array(
                'login-otp',
                'login-verify',
                'create_account',
            );

            add_filter('wpcfto_options_page_setup', array( $this, 'options' ), 20, 1);

            if ( $this->otp_enable ) {
                foreach ( $this->actions as $method => $action ) {
                    $hook = 'wp_ajax_nopriv_' . $action;
                    $callback = [$this, $method];

                    if ( ! has_action( $hook, $callback ) && method_exists(__CLASS__, $method) ) {
                        add_action('wp_ajax_nopriv_' . $action, $callback);
                    }
                }
            }

            add_action('rest_api_init', array( $this, 'api_init' ) );
        }

        public function api_init()
        {
            foreach ( $this->routes as $route ) {
                if ( empty( $route ) ) continue;
                require_once sprintf("%s/%s.php", $this->path_api, $route);
            }
        }

        public function pass_invalid( $password, $password_re ): array
        {
            $invalid = false;
            $message = '';

            if ( empty( $password ) || empty( $password_re ) ) {
                $message = esc_html__( 'Fill in the required password fields', 'masterstudy-child' );
                $invalid = true;
            }

            if ( $password !== $password_re ) {
                $message = esc_html__( 'Passwords do not match', 'masterstudy-child' );
                $invalid = true;
            }

            /* if Password longer than 20 -for some tricky user try to enter long characters to block input.*/
            if ( ! $invalid && strlen( $password ) > 20 ) {
                $message = esc_html__( 'Password too long', 'masterstudy-child' );
                $invalid = true;
            }

            return array(
                'message' => $message,
                'invalid' => $invalid
            );
        }

        public function valid_phone( $phone )
        {
            $valid_number = filter_var($phone, FILTER_SANITIZE_NUMBER_INT);

            if ( $valid_number ) {
                return preg_replace("/[^0-9]/", '', $valid_number);
            }

            return $valid_number;
        }

        public function get_user_email( $text ): string
        {
            return $text . '@gmail.com';
        }

        public function sign_in( $data = array() )
        {
            $doing_ajax = wp_doing_ajax();

            if ( $doing_ajax ) {
                check_ajax_referer( $this->nonce_action );
            }

            $message      = esc_html__('An error occurred, please try again later', 'masterstudy-child');
            $status       = 'error';
            $response     = array(
                'message' => $message,
                'status'  => $status,
            );

            if ( $doing_ajax ) {
                $request_body = file_get_contents( 'php://input' );
                $data         = json_decode( $request_body, true );
            }

            if ( isset( $data['phone'] ) && ! empty( $data['phone'] ) ) {
                $valid_number = $this->valid_phone( $data['phone'] );

                if ( $valid_number ) {
                    $user = get_user_by('login', sanitize_user( $valid_number ));
                    $send = false;

                    if ( is_wp_error( $user ) || ! $user ) {
                        $user_email = $this->get_user_email( $valid_number );
                        $user       = get_user_by('email', $user_email);

                        if ( is_wp_error( $user ) || ! $user ) {
                            $message = esc_html__('Enter confirmation code', 'masterstudy-child');

                            if ( $this->testing ) {
                                $send = $this->send_testing( $valid_number );
                                $message .= ' - ' . $send;
                            }
                            else {
                                $send = $this->send( $valid_number );
                            }

                            $status = 'success';
                        }
                    }

                    if ( $user && method_exists($user, 'exists') && $user->exists() ) {
                        $send    = true;
                        $status  = 'password';
                        $message = esc_html__('Enter a password for your account', 'masterstudy-child');
                    }

                    if ( $send ) {
                        $response     = array(
                            'message' => $message,
                            'status'  => $status,
                        );
                    }
                }
                else {
                    $response['message'] = esc_html__('Validation phone number error', 'masterstudy-child');
                }
            }

            if ( $doing_ajax ) {
                wp_send_json( $response );
            }
            else {
                return $response;
            }
        }

        public function lost_password()
        {
            check_ajax_referer( $this->prefix . 'lost_password' );

            $message      = esc_html__('An error occurred, please try again later', 'masterstudy-child');
            $status       = 'error';
            $response     = array(
                'message' => $message,
                'status'  => $status,
            );
            $request_body = file_get_contents( 'php://input' );
            $data         = json_decode( $request_body, true );

            if ( isset( $data['phone'] ) && ! empty( $data['phone'] ) ) {
                $valid_number = $this->valid_phone( $data['phone'] );

                if ( $valid_number ) {
                    $user = get_user_by('login', sanitize_user( $valid_number ));

                    if ( is_wp_error( $user ) || ! $user ) {
                        $user_email = $this->get_user_email( $valid_number );
                        $user       = get_user_by('email', $user_email);

                        if ( is_wp_error( $user ) || ! $user ) {
                            $message = esc_html__('User is not exists', 'masterstudy-child');
                        }
                    }

                    if ( $user && is_a( $user, 'WP_User' ) && method_exists($user, 'exists') && $user->exists() ) {
                        $message = esc_html__('Enter confirmation code', 'masterstudy-child');

                        if ( $this->testing ) {
                            $send = $this->send_testing( $valid_number );
                            $message .= ' - ' . $send;
                        }
                        else {
                            $send = $this->send( $valid_number );
                        }

                        $status = $send ? 'success' : $status;
                    }

                    $response     = array(
                        'message' => $message,
                        'status'  => $status,
                    );
                }
                else {
                    $response['message'] = esc_html__('Validation phone number error', 'masterstudy-child');
                }
            }

            wp_send_json( $response );
        }

        public function reset_password()
        {
            check_ajax_referer( $this->prefix . 'lost_password' );

            $message      = esc_html__('An error occurred, please try again later', 'masterstudy-child');
            $status       = 'error';
            $response     = array(
                'message' => $message,
                'status'  => $status,
            );
            $request_body = file_get_contents( 'php://input' );
            $data         = json_decode( $request_body, true );

            if ( isset( $data['phone'] ) && ! empty( $data['phone'] ) ) {
                $valid_number = $this->valid_phone( $data['phone'] );

                if ( $valid_number ) {

                    $user = get_user_by('login', sanitize_user( $valid_number ));

                    if ( is_wp_error( $user ) || ! $user ) {
                        $user_email = $this->get_user_email( $valid_number );
                        $user       = get_user_by('email', $user_email);

                        if ( is_wp_error( $user ) || ! $user ) {
                            wp_send_json( $response );
                        }
                    }

                    $new_password    = ( isset( $data['password'] ) && ! empty( $data['password'] ) ) ? $data['password'] : '';
                    $new_password_re = ( isset( $data['password_re'] ) && ! empty( $data['password_re'] ) ) ? $data['password_re'] : '';
                    $pass_invalid    = $this->pass_invalid( $new_password, $new_password_re );

                    if ( $pass_invalid['invalid'] ) {
                        $response['message'] = $pass_invalid['message'];

                        wp_send_json( $response );
                    }

                    reset_password( $user, $new_password );

                    $response     = array(
                        'message'   => esc_html__('Password changed successfully', 'masterstudy-child'),
                        'status'    => 'success',
                        'user_page' => STM_LMS_User::user_page_url()
                    );
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
        public function verification( $data = array() )
        {
            $doing_ajax = wp_doing_ajax();

            if ( $doing_ajax ) {
                check_ajax_referer( $this->nonce_action );
            }

            $response     = array(
                'message' => esc_html__('An error occurred, please try again later', 'masterstudy-child'),
                'status'  => 'error',
            );

            if ( $doing_ajax ) {
                $request_body = file_get_contents( 'php://input' );
                $data         = json_decode( $request_body, true );
            }

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
                    $response = array(
                        'message'   => esc_html__('Successfully code validation. Think of a password for your account', 'masterstudy-child'),
                        'status'    => 'success',
                    );
                }
            }

            if ( $doing_ajax ) {
                wp_send_json( $response );
            }
            else {
                return $response;
            }
        }

        public function create_account( $data = array() )
        {
            $doing_ajax = wp_doing_ajax();

            if ( $doing_ajax ) {
                check_ajax_referer( $this->nonce_action );
            }

            $response     = array(
                'message' => esc_html__('An error occurred, please try again later', 'masterstudy-child'),
                'status'  => 'error',
            );

            if ( $doing_ajax ) {
                $request_body = file_get_contents( 'php://input' );
                $data         = json_decode( $request_body, true );
            }

            if (
                isset( $data['phone'] ) && ! empty( $data['phone'] ) &&
                isset( $data['register'] ) &&
                isset( $data['password'] ) && ! empty( $data['password'] ) &&
                isset( $data['password_re'] )
            ) {
                $valid_number     = $this->valid_phone( $data['phone'] );
                $user_password    = $data['password'];
                $user_password_re = $data['password_re'];
                $register         = $data['register'];

                if ($valid_number) {
                    $user_email   = $this->get_user_email( $valid_number );

                    if ( $register ) {
                        $pass_invalid    = $this->pass_invalid( $user_password, $user_password_re );

                        if ( $pass_invalid['invalid'] ) {
                            $response['message'] = $pass_invalid['message'];

                            wp_send_json( $response );
                        }

                        /* Now we have valid data */
                        $user = wp_create_user( sanitize_user( $valid_number ), $user_password, $user_email );
                    }
                    else {
                        $user = wp_signon( array(
                            'user_login'    => $valid_number,
                            'user_password' => $user_password,
                            'remember'      => false
                        ), is_ssl() );
                    }

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

                        if ( ! empty( $register ) ) {
                            $message   = esc_html__('Successfully register in. Redirecting...', 'masterstudy-child');
                            $user_page = STM_LMS_User::settings_url();

                            do_action( 'stm_lms_after_user_register', $user, $data );
                        }
                        else {
                            $message   = esc_html__('Successfully logged in. Redirecting...', 'masterstudy-child');

                            if ( STM_LMS_Instructor::is_instructor( $user->ID ) ) {
                                $user_page = STM_LMS_User::user_page_url($user->ID, true);
                            }
                            else {
                                $user_page = STM_LMS_User::enrolled_courses_url();
                            }
                        }
                    }

                    if ( $user && method_exists($user, 'exists') && $user->exists() ) {
                        wp_clear_auth_cookie();
                        wp_set_current_user( $user->ID );
                        wp_set_auth_cookie($user->ID, true, is_ssl());

                        update_user_meta($user->ID, 'billing_phone', $valid_number);
                        update_user_meta($user->ID, 'shipping_phone', $valid_number);

                        if ( isset( $_COOKIE['redirect_trial_lesson'] ) ) {
                            $last_visit = $_COOKIE['redirect_trial_lesson'];
                            unset( $_COOKIE['redirect_trial_lesson'] );
                        }

                        $user_page = ( ! empty( $last_visit ) ) ? $last_visit : $user_page;

                        $response = array(
                            'user_page' => $user_page,
                            'message'   => $message,
                            'status'    => 'success',
                        );

                        if ( ! $doing_ajax ) {
                            unset( $response['user_page'] );
                            $response['user'] = $user;
                        }
                    }
                }
            }

            if ( $doing_ajax ) {
                wp_send_json( $response );
            }
            else {
                return $response;
            }
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
                                'stm_otp_testing' => array(
                                    'type'    => 'checkbox',
                                    'label'   => esc_html__( 'OTP Testing', 'masterstudy-child' ),
                                    'pro'     => true,
                                    'pro_url' => 'https://stylemixthemes.com/wordpress-lms-plugin/?utm_source=wpadmin-ms&utm_medium=ms-settings&utm_campaign=general-settings-get-pro',
                                    'dependency' => array(
                                        'key'   => 'stm_otp_enable',
                                        'value' => 'not_empty'
                                    ),
                                ),
                                'stm_api_eskiz_email' => array(
                                    'group'       => 'started',
                                    'columns'     => '33',
                                    'group_title' => esc_html__( 'Eskiz.uz API credentials', 'masterstudy-child' ),
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Email', 'masterstudy-child' ),
                                    'dependency' => array(
                                        'key'   => 'stm_otp_enable',
                                        'value' => 'not_empty'
                                    ),
                                ),
                                'stm_api_eskiz_password' => array(
                                    'group'       => 'ended',
                                    'columns'     => '33',
                                    'type'        => 'text',
                                    'label'       => esc_html__( 'Password', 'masterstudy-child' ),
                                    'dependency' => array(
                                        'key'   => 'stm_otp_enable',
                                        'value' => 'not_empty'
                                    ),
                                ),
                            )
                        );
                    }
                }
            }

            return $setups;
        }
    }