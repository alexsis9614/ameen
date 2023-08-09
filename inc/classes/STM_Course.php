<?php
    namespace LMS\inc\classes;

    use STM_LMS_Curriculum;
    use STM_LMS_Options;
    use STM_LMS_Helpers;
    use STM_LMS_User;

    class STM_Course extends STM_Curriculum
    {
        public $courses_slug;

        public function __construct()
        {
            parent::__construct();

            $this->courses_slug = STM_LMS_Curriculum::$courses_slug;

            add_action( 'save_post_' . $this->courses_slug, array( $this, 'save' ) );

            add_filter( 'stm_wpcfto_fields', array( $this, 'fields' ) );
        }

        public function save( $course_id )
        {
            if ( get_post_type( $course_id ) !== $this->courses_slug ) return;

            $_field_name = 'curriculum_plans';

            if ( isset( $_POST[ $_field_name ] ) && ! empty( $_POST[ $_field_name ] ) ) {
                $sections_curriculum_plans = json_decode( wp_unslash( $_POST[ $_field_name ] ), true );

                if ( ! empty( $sections_curriculum_plans ) ) {
                    foreach ( $sections_curriculum_plans as $curriculum_plans ) {
                        if ( ! empty( $curriculum_plans ) ) {
                            foreach ( $curriculum_plans as $curriculum ) {
                                $post_id = $curriculum['id'];
                                if ( ! empty( $curriculum['plans'] ) ) {
                                    foreach ( $curriculum['plans'] as $plan => $value ) {
                                        $value = (bool)$value;
                                        self::update_curriculum_meta_key( $post_id, $course_id, $plan, $value );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        public function fields( $settings ): array
        {
            if ( ! empty( $settings ) && ! empty( $this->plans ) )
            {
                foreach ( $settings as $index => $sections )
                {
                    if ( $index === 'stm_courses_settings' )
                    {
                        foreach ( $sections as $section_name => $section )
                        {
                            $fields = array();

                            if ( 'section_accessibility' === $section_name )
                            {
                                $fields = $this->accessibility( $section );
                            }
                            else if ( 'section_files' === $section_name )
                            {
                                $fields = $this->files( $section );
                            }
                            else if ( 'section_expiration' === $section_name )
                            {
                                $fields = $this->expiration( $section );
                            }

                            if ( ! empty( $fields ) ) {
                                $settings[ $index ][ $section_name ]['fields'] = $fields;
                            }
                        }
                    }
                }
            }

            return $settings;
        }

        public function accessibility( $section )
        {
            $fields      = $section['fields'];
            $first_field = array_splice($fields, 0, 4);

            $decimals_num = STM_LMS_Options::get_option( 'decimals_num', 2 );
            $zeros        = str_repeat( '0', intval( $decimals_num ) - 1 );
            $step         = "0.{$zeros}1";
            $currency     = STM_LMS_Helpers::get_currency();

            $new_fields = $first_field;

            $count_plans = count( $this->plans );
            foreach ( $this->plans as $plan_index => $plan ) {
                $new_fields[ self::price_key( $plan['name'] ) ] = array(
                    'type'        => 'number',
                    'label'       => sprintf(
                    /* translators: %s: number */
                        esc_html__( 'Price %s (%s)', 'masterstudy-child' ),
                        $plan['name'], $currency
                    ),
                    'placeholder' => sprintf(
                        esc_html__( 'Leave empty if course is free', 'masterstudy-child' ),
                        $currency
                    ),
                    'sanitize'    => 'wpcfto_save_number',
                    'step'        => $step,
                    'columns'     => 50,
                    'dependency'  => array(
                        'key'   => 'not_single_sale',
                        'value' => 'empty'
                    )
                );

                if ( $plan_index === 0 ) {
                    $new_fields[ self::price_key( $plan['name'] ) ]['group'] = 'started';
                }

                if ( $count_plans === ($plan_index + 1) ) {
                    $new_fields[ self::price_key( $plan['name'] ) ]['group'] = 'ended';
                }
            }

            return array_merge( $new_fields, $fields );
        }

        public function files( $section ): array
        {
            $fields      = $section['fields'];
            $options     = array(
                '' => esc_html__('Select plan', 'masterstudy-child')
            );

            foreach ( $this->plans as $plan )
            {
                $options[ self::price_key( $plan['name'] ) ] = esc_attr( $plan['name'] );
            }

            $fields['course_files_pack']['fields']['course_files_plan'] = array(
                'type'    => 'select',
                'label'   => esc_html__( 'Select Plan', 'masterstudy-child' ),
                'options' => $options,
                'value'   => '',
                'pro'     => true,
                'pro_url' => 'https://stylemixthemes.com/wordpress-lms-plugin/?utm_source=wpadmin-ms&utm_medium=course-settings-backend&utm_campaign=certificate-pro',
                'classes' => array( 'short_field' ),
            );

            return $fields;
        }

        public function expiration( $section )
        {
            $fields      = $section['fields'];

//            unset( $fields['end_time'] );

            unset( $fields['end_time']['group'] );

            $plans_count = count( $this->plans );

            foreach ( $this->plans as $key => $plan ) {
                $_field_key = 'end_time_' . STM_Plans::key( $plan['name'] );

                $fields[ $_field_key ] =  array(
                    'type'       => 'number',
                    'label'      => sprintf(
                        esc_html__( 'Course %s expiration (days)', 'masterstudy-child' ),
                        sprintf(
                            esc_html__('%s plan'),
                            STM_Plans::key( $plan['name'] )
                        )
                    ),
                    'value'      => '',
                    'dependency' => array(
                        'key'   => 'expiration_course',
                        'value' => 'not_empty',
                    ),
                );

                if ( $plans_count === ($key + 1) ) {
                    $fields[ $_field_key ]['group'] = 'ended';
                }
            }

            return $fields;
        }

        public static function get_time_expiration( $course_id, $user_id = 0 ) {
            if ( self::get() ) {
                if ( ! $user_id ) {
                    $user_id = get_current_user_id();
                }

                $stm_lms_course_plan = self::get_user_meta_key( $user_id, $course_id );
                $_field_key          = 'end_time_' . self::key( $stm_lms_course_plan );
            }
            else {
                $_field_key = 'end_time';
            }

            return get_post_meta( $course_id, $_field_key, true );
        }

        public static function get_end_time( $course_id, $user_id = 0 ) {
            $end_time = self::get_time_expiration( $course_id, $user_id );
            if ( empty( $end_time ) ) {
                return 0;
            }

            return time() + intval( $end_time ) * 24 * 60 * 60;
        }
    }