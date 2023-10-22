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
                    }
                }
            }

            return $setups;
        }
    }