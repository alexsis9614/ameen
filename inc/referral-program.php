<?php
    new STM_THEME_CHILD_Referral_Program();

    class STM_THEME_CHILD_Referral_Program
    {
        public function __construct()
        {
            add_filter('wpcfto_options_page_setup', [$this, 'options'], 20, 1);
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
                                'stm_referral_enable' => array(
                                    'type'    => 'checkbox',
                                    'label'   => esc_html__( 'Enable referral program', 'masterstudy-child' ),
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