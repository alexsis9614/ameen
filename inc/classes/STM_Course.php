<?php
    namespace LMS\inc\classes;

    use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;
    use STM_LMS_Helpers;
    use STM_LMS_Lesson;
    use STM_LMS_User;
    use STM_LMS_Templates;

    class STM_Course extends STM_Curriculum
    {
        public static $meta_key_bundle = 'stm_course_bundle-';

        public function __construct()
        {
            parent::__construct();

            add_action( 'save_post_' . self::$courses_slug, array( $this, 'save' ) );

            add_filter( 'masterstudy_lms_course_custom_fields', array( $this, 'course_field' ) );

            remove_action('stm_lms_before_item_template_start', 'STM_LMS_Course::check_course_item');
            add_action( 'stm_lms_before_item_template_start', array( $this, 'check_course_item' ), 10, 2 );

            add_filter( 'stm_lms_buy_button_auth', array( $this, 'buy_button_attributes' ), 20, 2 );

            add_filter( 'stm_lms_components/buy-button/paid-courses/buy-course', array( $this, 'change_vars' ), 20, 2 );
        }

        public function buy_button_attributes( $attributes, $course_id )
        {
            global $wp_query;

            $plans   = new STM_Plans;
            $courses = self::get_course_bundle( $course_id );

            if ( $plans->enable( $course_id ) && ( empty( $courses ) || ! $wp_query->get( 'bundle_widget', false ) ) ) {
                $attributes = array(
                    'data-target=".stm-lms-modal-plans"',
                    'data-lms-modal="plans"',
                    'data-lms-params="'. esc_attr( wp_json_encode(['course_id' => $course_id]) ) .'"',
                );

                wp_enqueue_script('buy-plans');
                wp_enqueue_style('buy-plans');
            }

            if ( ! empty( $courses ) && $wp_query->get( 'bundle_widget', false ) ) {
                return array();
            }

            return $attributes;
        }

        public function change_vars( $template, $stm_lms_vars )
        {
            $lms_page_path = get_query_var( 'lms_page_path' );

            if ( empty( $lms_page_path ) ) {
                return $template;
            }

            $course = get_page_by_path( $lms_page_path, OBJECT, 'stm-courses' );

            if ( ! isset( $stm_lms_vars[ 'attributes' ] ) ) {
                return $template;
            }

            $attributes = $stm_lms_vars[ 'attributes' ];

            if (
                ! empty( $course ) &&
                in_array( 'data-purchased-course="' . intval( $course->ID ) . '"', $stm_lms_vars[ 'attributes' ] )
            ) {
                $stm_lms_vars['attributes'] = $this->buy_button_attributes( $attributes, $course->ID );

                extract( $stm_lms_vars );

                $template = STM_LMS_Templates::load_lms_template('components/buy-button/paid-courses/buy-course', $stm_lms_vars );
            }

            return $template;
        }

        public function check_course_item( $course_id, $item_id )
        {
            $materials = ( new CurriculumMaterialRepository() )->get_course_materials( apply_filters( 'wpml_object_id', $course_id, self::$courses_slug ) );

            $materials = self::curriculum_filter( $course_id, $materials );

			wp_add_inline_script( 'masterstudy-course-player-lesson', "
				(function($) {
					$(document).ready( function() {
						if ( $( window ).width() > 1024 ) {
						    $('[data-id=\"masterstudy-curriculum-switcher\"]').addClass('masterstudy-switch-button_active');
						    $('.masterstudy-course-player-curriculum').addClass('masterstudy-course-player-curriculum_open');
						    $('body').addClass('masterstudy-course-player-body-hidden');
						}
						
						if (!$('.masterstudy-course-player-discussions').hasClass('masterstudy-course-player-discussions_open')) {
							$('.masterstudy-course-player-content').addClass('masterstudy-course-player-content_open-sidebar');
						}
						
						if (window.matchMedia('(max-width: 1366px)').matches) {
							$('.masterstudy-course-player-discussions').removeClass('masterstudy-course-player-discussions_open');
							$('.masterstudy-course-player-header__discussions').removeClass('masterstudy-course-player-header__discussions_open');
							if (url.searchParams.has('discussions_open')) {
								url.searchParams[\"delete\"]('discussions_open');
								history.pushState({}, '', url.toString());
							}
						}
					});
				}) ( jQuery );
			");

            $is_scorm  = ( '0' == $item_id ); // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison

            if ( empty( $materials ) && ! $is_scorm ) {
                STM_LMS_User::js_redirect( get_permalink( $course_id ) );
            }

            if ( ! in_array( intval( $item_id ), $materials, true ) && ! $is_scorm ) {
                STM_LMS_User::js_redirect( STM_LMS_Lesson::get_lesson_url( $course_id, ( self::get_first_lesson( $course_id ) ) ) );
            }
        }

        public function course_field( $fields )
        {
            $count_plans = count( $this->plans );

            if ( $count_plans > 0 ) {

                $count_fields      = count( $fields );
                $pricing_fields    = array();
                $expiration_fields = array();

                foreach ( $this->plans as $plan_index => $plan ) {

                    $pricing_fields[ $count_fields ] = $this->pricing( $plan );

                    $expiration_fields[ $count_fields ] = $this->expiration( $plan );

                    if ( $count_plans === ( $plan_index + 1 ) ) {
                        $pricing_fields[ $count_fields ][ 'custom_html' ]    = '<hr /> <div><b>' . __( 'Access - time limit for plans', 'masterstudy-child' ) . '</b></div>';
                        $expiration_fields[ $count_fields ][ 'custom_html' ] = '<hr />';
                    }

                    $count_fields++;

                }

                $fields = array_merge( $fields, $pricing_fields, $expiration_fields );

                $bundle_course_field = $this->course_bundle();

                if ( ! empty( $bundle_course_field ) ) {
                    $fields = array_merge( $fields, $bundle_course_field );
                }
            }

            return $fields;
        }

        public function course_bundle(): array
        {
            $args    = array(
                'post_type'      => self::$courses_slug,
                'posts_per_page' => -1,
                'fields'         => 'ids'
            );
            $courses = new \WP_Query( $args );

            if ( empty( $courses->posts ) ) {
                return array();
            }

            $courses = $courses->posts;

            array_walk( $courses, function ( &$course ) {
                $course = $this->field_checkbox( $this->meta_key_bundle . $course, html_entity_decode( get_the_title( $course ) ) );
            });

            return $courses;
        }

        public static function get_course_bundle( $course_id )
        {
            global $wpdb;

            $query = "
                SELECT pm.meta_key FROM {$wpdb->postmeta} as pm
                LEFT JOIN {$wpdb->posts} as p ON pm.post_id = p.ID AND p.post_type = %s
                WHERE pm.meta_key LIKE %s AND p.ID = %d
            ";

            $prepare = $wpdb->prepare( $query, self::$courses_slug, '%' . self::$meta_key_bundle . '%', $course_id );
            $results = $wpdb->get_results( $prepare, ARRAY_A );

            array_walk( $results, function ( &$result ) {
                $result = str_replace( self::$meta_key_bundle, '', $result['meta_key'] );
            });

            return $results;
        }

        public function field_checkbox( $field_name, $label ): array
        {
            return array(
                'type'        => 'checkbox',
                'name'        => $field_name,
                'label'       => $label,
                'required'    => false
            );
        }

        public function save( $course_id )
        {
            if ( get_post_type( $course_id ) !== self::$courses_slug ) return;

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

        public function pricing( $plan ): array
        {
            $currency = STM_LMS_Helpers::get_currency();

            return array(
                'type'        => 'number',
                'name'        => self::price_key( $plan['name'] ),
                'label'       => sprintf(
                /* translators: %s: number */
                    esc_html__( 'Price %s ( %s )', 'masterstudy-child' ),
                    $plan['name'], trim( $currency )
                ),
                'required'    => false,
            );
        }

        public function files( $section ): array
        {
            $fields      = $section['fields'];
            $options     = array(
                '' => esc_html__('Select plan', 'masterstudy-child')
            );

            foreach ( $this->plans as $plan )
            {
                $options[ self::key( $plan['name'] ) ] = esc_attr( $plan['name'] );
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

        public function expiration( $plan ): array
        {
            $_field_key = 'end_time_' . STM_Plans::key( $plan['name'] );

            return array(
                'type'       => 'number',
                'name'       => $_field_key,
                'label'      => sprintf(
                    esc_html__( 'Course %s expiration (days)', 'masterstudy-child' ),
                    sprintf(
                        esc_html__('%s plan'),
                        STM_Plans::key( $plan['name'] )
                    )
                ),
                'required'   => false,
            );
        }

        public static function get_time_expiration( $course_id, $user_id = 0 ) {
            $expiration = get_post_meta( $course_id, 'expiration_course', true );

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

            return $expiration ? get_post_meta( $course_id, $_field_key, true ) : false;
        }

        public static function get_course_duration_time( $course_id ) {
            $expiration_days = self::get_time_expiration( $course_id );

            if ( empty( $expiration_days ) ) {
                return 0;
            }

            return intval( $expiration_days ) * DAY_IN_SECONDS;
        }

        public static function get_end_time( $course_id, $user_id = 0 ) {
            $end_time = self::get_time_expiration( $course_id, $user_id );
            if ( empty( $end_time ) ) {
                return 0;
            }

            return time() + intval( $end_time ) * 24 * 60 * 60;
        }
    }