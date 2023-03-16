<?php
    new STM_THEME_CHILD_OTP();

    class STM_THEME_CHILD_OTP extends STM_LMS_Play_Mobile
    {
        public $otp_enable   = false;
        public $actions      = array();
        public $nonce_action = 'stm_lms_sign_in';
        public $nonce        = '';

        public function __construct()
        {
            parent::__construct('', '');

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
                $response     = array(
                    'message' => esc_html__('Enter confirmation code', 'masterstudy-child'),
                    'status'  => 'success',
                );
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

            if ( isset( $data['code'] ) && ! empty( $data['code'] ) ) {
                $play_mobile = new STM_LMS_Play_Mobile('username', 'password');
                $play_mobile->create([
                    'baseUrl' => "http://91.204.239.44/broker-api/"
                ]);

                $response = $play_mobile->send([
                    [
                        'recipient' => "998936913932",
                        'message-id' => "1",
                        'originator' => "3700",
                        'text' => "test",
                    ],
                    [
                        'recipient' => "998936913932",
                        'message-id' => "1",
                        'originator' => "3700",
                        'text' => "test",
                    ],
                ]);

                if ( $response == false ) {
                    $response     = array(
                        'message' => esc_html__('There was an error sending this message, try again later', 'masterstudy-child'),
                        'status'  => 'error',
                    );
                }
                else {
                    $response     = array(
                        'message' => esc_html__('Send successfully', 'masterstudy-child'),
                        'status'  => 'success',
                    );
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
                            )
                        );
                    }
                }
            }

            return $setups;
        }
    }