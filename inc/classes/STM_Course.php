<?php
    namespace LMS\child\classes;

    use STM_LMS_Curriculum;
    use STM_LMS_Options;
    use STM_LMS_Helpers;
    use STM_LMS_Course;
    use STM_LMS_User;
    use STM_LMS_Cart;
    use STM_LMS_Order;
    use STM_LMS_Subscriptions;

    class STM_Course extends STM_Curriculum
    {
        public $courses_slug;

        public function __construct()
        {
            parent::__construct();

            $this->courses_slug = STM_LMS_Curriculum::$courses_slug;

//            add_action( 'save_post_' . $this->courses_slug, array( $this, 'save' ) );

            add_filter( 'stm_wpcfto_fields', array( $this, 'fields' ), 20, 1 );

            add_action( 'add_user_course', array( $this, 'add_user' ), 20, 2 );

        }

        public function add_user( $user_id, $course_id )
        {
            $end_time = self::get_end_time( $course_id );

            stm_lms_update_user_course_endtime( $course_id, $end_time );
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
                                $curriculum_id = $curriculum['id'];
                                if ( ! empty( $curriculum['plans'] ) ) {
                                    foreach ( $curriculum['plans'] as $index => $plan ) {
                                        $plan = (bool)$plan;
                                        update_post_meta( $curriculum_id, 'course_plan_' . $index . '_' . $course_id, $plan );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            error_log( print_r( $_POST, true ) );
        }

        public function fields( $settings )
        {
            if ( ! empty( $settings ) && ! empty( $this->plans ) )
            {
                foreach ( $settings as $index => $sections )
                {
                    if ( $index === 'stm_courses_settings' )
                    {
                        foreach ( $sections as $section_name => $section )
                        {
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
                $new_fields[ self::plan_price_key( $plan['name'] ) ] = array(
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
                    $new_fields[ self::plan_price_key( $plan['name'] ) ]['group'] = 'started';
                }

                if ( $count_plans === ($plan_index + 1) ) {
                    $new_fields[ self::plan_price_key( $plan['name'] ) ]['group'] = 'ended';
                }
            }

            return array_merge( $new_fields, $fields );
        }

        public function files( $section )
        {
            $fields      = $section['fields'];
            $options     = array(
                '' => esc_html__('Select plan', 'masterstudy-child')
            );

            foreach ( $this->plans as $plan )
            {
                $options[ self::plan_price_key( $plan['name'] ) ] = esc_attr( $plan['name'] );
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

            unset( $fields['end_time'] );

            $plans_count = count( $this->plans );

            foreach ( $this->plans as $key => $plan ) {
                $_field_key = 'end_time_' . strtolower( $plan['name'] );

                $fields[ $_field_key ] =  array(
                    'type'       => 'number',
                    'label'      => sprintf(
                        esc_html__( 'Course %s expiration (days)', 'masterstudy-child' ),
                        sprintf(
                            esc_html__('%s plan'),
                            strtolower( $plan['name'] )
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

        public static function get_time_expiration( $course_id ) {
            if ( self::get() ) {
                $stm_lms_course_plan = get_user_meta(get_current_user_id(), 'stm_lms_course_plan_' . $course_id, true);
                $_field_key          = 'end_time_' . strtolower( $stm_lms_course_plan );
            }
            else {
                $_field_key = 'end_time';
            }

            return get_post_meta( $course_id, $_field_key, true );
        }

        public static function get_end_time( $course_id ) {
            $end_time = self::get_time_expiration( $course_id );
            if ( empty( $end_time ) ) {
                return 0;
            }

            return time() + intval( $end_time ) * 24 * 60 * 60;
        }

//        public static function add_user_course( $course_id, $user_id, $current_lesson_id, $progress = 0, $is_translate = false, $enterprise = '', $bundle = '', $instructor_id = '', $for_points = '' ) {
//            if ( empty( $user_id ) ) {
//                $current_user = STM_LMS_User::get_current_user();
//                if ( empty( $current_user['id'] ) ) {
//                    die;
//                }
//                $user_id = $current_user['id'];
//            }
//
//            if ( empty( $user_id ) ) {
//                die;
//            }
//
//            $user_course = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course_id, array(), $enterprise ) );
//            $end_time    = self::get_end_time( $course_id );
//
//            if ( empty( $user_course ) ) {
//                $course                     = compact( 'user_id', 'course_id', 'current_lesson_id', 'end_time' );
//                $course['status']           = 'enrolled';
//                $course['progress_percent'] = $progress;
//                $course['start_time']       = time();
//
//                if ( function_exists( 'wpml_get_language_information' ) ) {
//                    $post_language_information = wpml_get_language_information( null, $course_id );
//                    $course['lng_code']        = $post_language_information['locale'];
//                } else {
//                    $course['lng_code'] = get_locale();
//                }
//
//                $course['enterprise_id'] = $enterprise;
//                $course['bundle_id']     = $bundle;
//                $course['instructor_id'] = $instructor_id;
//                $course['for_points']    = $for_points;
//
//                stm_lms_add_user_course( $course );
//
//                if ( ! $is_translate ) {
//                    self::add_wpmls_binded_courses( $course_id, $user_id, $current_lesson_id, $progress );
//                }
//
//                /*User was Added course*/
//                do_action( 'add_user_course', $user_id, $course_id );
//
//            } elseif ( ! empty( $user_course ) && ! empty( $end_time ) ) {
//                /*Update course time*/
//                stm_lms_update_user_course_endtime( $user_course['user_course_id'], $end_time );
//            }
//        }

//        public static function add_wpmls_binded_courses( $course_id, $user_id, $current_lesson_id, $progress ) {
//            if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
//                global $sitepress;
//                $trid         = $sitepress->get_element_trid( $course_id );
//                $translations = $sitepress->get_element_translations( $trid );
//                if ( ! empty( $translations ) ) {
//                    foreach ( $translations as $translation ) {
//                        self::add_user_course( $translation->element_id, $user_id, $current_lesson_id, $progress, true );
//                    }
//                }
//            }
//        }
//
//        public static function has_course_access( $course_id, $item_id = '', $add = true ) {
//            $user = STM_LMS_User::get_current_user();
//            if ( empty( $user['id'] ) ) {
//                return apply_filters( 'stm_lms_has_course_access', false, $course_id, $item_id );
//            }
//            $user_id = $user['id'];
//
//            /*If course Author*/
//            $author_id = get_post_field( 'post_author', $course_id );
//            if ( $author_id == $user_id ) {
//                self::add_user_course( $course_id, $user_id, STM_LMS_Course::item_url( $course_id, '' ), 0 );
//                return true;
//            }
//
//            if ( STM_LMS_Cart::woocommerce_checkout_enabled() ) {
//                wc_customer_bought_product( $user['email'], $user_id, $course_id );
//            }
//
//            $columns = array( 'user_course_id', 'enterprise_id', 'bundle_id' );
//            if ( stm_lms_points_column_available() ) {
//                array_push( $columns, 'for_points' );
//            }
//            $course = stm_lms_get_user_course( $user_id, $course_id, $columns );
//
//            /*Check for membership expiration*/
//
//            $in_enterprise       = ( isset( $course[0] ) ) ? STM_LMS_Order::is_purchased_by_enterprise( $course[0], $user_id ) : false;
//            $my_course           = ( $author_id == $user_id );
//            $is_free             = ( ! get_post_meta( $course_id, 'not_single_sale', true ) && empty( STM_LMS_Course::get_course_price( $course_id ) ) );
//            $is_bought           = STM_LMS_Order::has_purchased_courses( $user_id, $course_id );
//            $in_bundle           = ( isset( $course[0]['bundle_id'] ) ) ? empty( $course[0]['bundle_id'] ) : false;
//            $not_in_membership   = get_post_meta( $course_id, 'not_membership', true );
//            $membership_level    = ( STM_LMS_Subscriptions::subscription_enabled() ) ? STM_LMS_Subscriptions::membership_plan_available() : false;
//            $membership_status   = ( STM_LMS_Subscriptions::subscription_enabled() ) ? STM_LMS_Subscriptions::get_membership_status( $user_id ) : 'inactive';
//            $membership_expired  = ( STM_LMS_Subscriptions::subscription_enabled() && $membership_level && 'expired' == $membership_status && ! $not_in_membership && ! $is_bought && ! $is_free && ! $in_enterprise && $in_bundle && empty( $course[0]['for_points'] ) );
//            $membership_inactive = ( STM_LMS_Subscriptions::subscription_enabled() && $membership_level && 'active' !== $membership_status && 'expired' !== $membership_status && ! $not_in_membership && ! $is_bought && ! $is_free && ! $my_course && ! $in_enterprise && $in_bundle && empty( $course[0]['for_points'] ) );
//
//            if ( $membership_expired || $membership_inactive ) {
//                return apply_filters( 'stm_lms_has_course_access', 0, $course_id, $item_id );
//            }
//
//            if ( ! count( $course ) ) {
//                /*If course is free*/
//                $prerequisite_passed = true;
//                if ( class_exists( 'STM_LMS_Prerequisites' ) ) {
//                    $prerequisite_passed = STM_LMS_Prerequisites::is_prerequisite( true, $course_id );
//                }
//                if ( $is_free && $prerequisite_passed && $add ) {
//                    STM_LMS_Course::add_user_course( $course_id, $user_id, STM_LMS_Course::item_url( $course_id, '' ), 0 );
//                    STM_LMS_Course::add_student( $course_id );
//                    return true;
//                }
//            } else {
//                /*Check for expiration*/
//                $course_expired = STM_LMS_Course::is_course_time_expired( $user_id, $course_id );
//                if ( $course_expired ) {
//                    return apply_filters( 'stm_lms_has_course_access', 0, $course_id, $item_id );
//                }
//            }
//
//            return apply_filters( 'stm_lms_has_course_access', count( $course ), $course_id, $item_id );
//        }
    }