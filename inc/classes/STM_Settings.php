<?php
    namespace LMS\inc\classes;

    use STM_LMS_WPCFTO_AJAX;
    use WPCFTO_Settings;

    class STM_Settings extends STM_LMS_WPCFTO_AJAX
    {

        public static $_plans_key = 'course_plans';

        public $_settings_name = 'stm_lms_settings';

        public static $courses_slug = 'stm-courses';

        public function __construct()
        {
            parent::__construct();

            add_filter( 'wpcfto_options_page_setup', array( $this, 'options' ), 20, 1 );

            add_filter( 'redux/stm_option/field/typography/custom_fonts', array( $this, 'custom_fonts' ) );

            add_filter( 'stm_lms_current_user_data', array( $this, 'current_user_data' ) );
        }

        public function current_user_data( $user_data )
        {
            $current_user        = wp_get_current_user();
            $id                  = $user_data['id'] ?? $current_user->ID;
            $stm_lms_user_avatar = get_user_meta( $id, 'stm_lms_user_avatar', true );

            if ( empty( $stm_lms_user_avatar ) ) {
                $avatar_size         = 215;
                $avatar_url          = STM_THEME_CHILD_DIRECTORY_URI . '/assets/images/avatar.svg';
                $user_data['avatar'] = "
                    <img src='" . $avatar_url . "' class='avatar photo' width='{$avatar_size}' />
                ";
                $user_data['avatar_url'] = $avatar_url;
            }

            return $user_data;
        }

        public function custom_fonts( $fonts )
        {
            if ( is_array( $fonts ) ) {
                $fonts['Custom Fonts'] = array(
                    "Inter" => 'inter'
                );
            }

            return $fonts;
        }

        public function options( $setups )
        {
            if ( ! empty( $setups ) ) {
                $text_button = esc_html__( 'Get plan', 'masterstudy-child' );

                foreach ( $setups as &$setup ) {
                    if ( $this->_settings_name !== $setup['option_name'] ) {
                        continue;
                    }

                    if ( array_key_exists( 'fields', $setup ) ) {
                        $setup[ 'fields' ][ 'stm_course_plans' ] = array(
                            'name'   => esc_html__( 'Plans', 'masterstudy-child' ),
                            'label'  => esc_html__( 'Plans settings', 'masterstudy-child' ),
                            'icon'   => 'fas fa-sliders-h',
                            'fields' => array(
                                self::$_plans_key => array(
                                    'type'   => 'repeater',
                                    'label'  => esc_html__( 'List plans', 'masterstudy-child' ),
                                    'fields' => array(
                                        'name'    => array(
                                            'type'    => 'text',
                                            'label'   => esc_html__( 'Name', 'masterstudy-child' ),
                                            'columns' => '50',
                                        ),
                                        'description' => array(
                                            'type'    => 'editor',
                                            'label'   => esc_html__( 'Description', 'masterstudy-child' ),
                                            'columns' => '50',
                                        ),
                                        'text_button' => array(
                                            'type'    => 'text',
                                            'label'   => esc_html__( 'Text button', 'masterstudy-child' ),
                                            'columns' => '50',
                                        ),
                                    ),
                                    'value'  => array(
                                        array(
                                            'name'        => esc_html__('Basic', 'masterstudy-child'),
                                            'description' => '',
                                            'text_button' => $text_button,
                                        ),
                                        array(
                                            'name'        => esc_html__('Standard', 'masterstudy-child'),
                                            'description' => '',
                                            'text_button' => $text_button,
                                        ),
                                        array(
                                            'name'        => esc_html__('VIP', 'masterstudy-child'),
                                            'description' => '',
                                            'text_button' => $text_button,
                                        ),
                                    ),
                                )
                            )
                        );

                        $pages = WPCFTO_Settings::stm_get_post_type_array( 'stm-courses' );

                        $setup[ 'fields' ][ 'stm_course_bundle' ] = array(
                            'name'   => esc_html__( 'Archive bundle', 'masterstudy-child' ),
                            'label'  => esc_html__( 'Bundle settings', 'masterstudy-child' ),
                            'icon'   => 'fas fa-sliders-h',
                            'fields' => array(
                                'stm_bundle_free_course' => array(
                                    'type'    => 'select',
                                    'label'   => esc_html__( 'Bundle Free Course', 'masterstudy-child' ),
                                    'options' => $pages,
                                ),
                                'stm_bundle_course' => array(
                                    'type'    => 'select',
                                    'label'   => esc_html__( 'Bundle Course', 'masterstudy-child' ),
                                    'options' => $pages,
                                ),
                            )
                        );

                        if (
                            isset( $setup[ 'fields' ][ 'section_2' ] ) &&
                            isset( $setup[ 'fields' ][ 'section_2' ][ 'fields' ] ) &&
                            isset( $setup[ 'fields' ][ 'section_2' ][ 'fields' ][ 'course_card_style' ] )
                        ) {
                            $setup[ 'fields' ][ 'section_2' ][ 'fields' ][ 'course_card_style' ][ 'options' ]['style_4'] = __( 'Ameen Card', 'masterstudy-child' );
                        }

                        $setup[ 'fields' ][ 'section_2' ][ 'fields' ]['enable_card_rating'] = array(
                            'type'    => 'checkbox',
                            'label'   => esc_html__( 'Card Rating', 'masterstudy-child' )
                        );
                    }
                }
            }

            return $setups;
        }
    }