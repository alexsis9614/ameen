<?php
    namespace LMS\inc\classes;

    use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;
    use STM_LMS_Options;
    use STM_LMS_User;
    use STM_LMS_Helpers;
    use STM_LMS_Lesson;
    use STM_LMS_Course;
    use STM_LMS_Bookit_Sync;
    use STM_LMS_Instructor;

    class STM_Curriculum extends STM_Plans
    {
        public static $curriculums = array(
            'lesson'     => 'stm-lessons',
            'assignment' => 'stm-assignments',
            'quiz'       => 'stm-quizzes',
        );

        public function __construct()
        {
            parent::__construct();

            if ( method_exists( 'STM_LMS_WPCFTO_AJAX', 'stm_curriculum_create_item' ) ) {
                add_filter( 'stm_lms_wpcfto_create_question', array( $this, 'create_question' ) );
            }

            if ( class_exists( 'STM_LMS_Lesson' ) ) {
                remove_action('wp_ajax_stm_lms_complete_lesson', 'STM_LMS_Lesson::complete_lesson');
                remove_action('wp_ajax_nopriv_stm_lms_complete_lesson', 'STM_LMS_Lesson::complete_lesson');
                add_action('wp_ajax_stm_lms_complete_lesson', array( $this, 'complete_lesson' ) );
                add_action('wp_ajax_nopriv_stm_lms_complete_lesson', array( $this, 'complete_lesson' ) );

                remove_action('wp_ajax_stm_lms_total_progress', 'STM_LMS_Lesson::total_progress');
                add_action('wp_ajax_stm_lms_total_progress', get_class() . '::total_progress');
            }

            if ( class_exists( 'STM_LMS_Assignments_Columns' ) ) {
                remove_action('save_post', array( 'STM_LMS_Assignments_Columns', 'assignment_saved' ) );
                add_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );
            }

            if ( class_exists( 'STM_LMS_User_Assignment' ) ) {
                remove_all_actions('wp_ajax_stm_lms_edit_user_answer', 10);
                add_action( 'wp_ajax_stm_lms_edit_user_answer', array( $this, 'stm_lms_edit_user_answer' ) );
            }

            if ( class_exists( 'STM_LMS_Page_Router' ) ) {
                add_filter('stm_lms_custom_routes_config', array( $this, 'pages_config' ) );
            }

            add_filter( 'masterstudy_lms_lesson_custom_fields', array( $this, 'curriculum_custom_fields' ), 20, 1 );

            add_filter( 'masterstudy_lms_quiz_custom_fields', array( $this, 'curriculum_custom_fields' ), 20, 1 );
        }

        public function curriculum_custom_fields( $custom_fields )
        {
            $count_plans = count( $this->plans );

            if ( $count_plans > 0 ) {
                $count_fields  = count( $custom_fields );
                $_plans_fields = array();

                foreach ( $this->plans as $plan_index => $plan ) {
                    $_plans_fields[ $count_fields ] = array(
                        'type'     => 'checkbox',
                        'name'     => self::curriculum_meta_key( $plan['name'] ),
                        'label'    => sprintf(
                            esc_html__( 'Plan - %s', 'masterstudy-child' ),
                            $plan['name']
                        ),
                        'default'  => false,
                        'required' => false,
                    );

                    if ( $count_plans === ( $plan_index + 1 ) ) {
                        $_plans_fields[ $count_fields ][ 'custom_html' ] = '<hr />';
                    }

                    $count_fields++;
                }

                if ( ! empty( $_plans_fields ) ) {
                    $custom_fields = array_merge( $custom_fields, $_plans_fields );
                }
            }

            return array_unique( $custom_fields, SORT_REGULAR );
        }

        public static function get_curriculum( $post_id )
        {
            return ( new CurriculumMaterialRepository() )->get_course_materials( apply_filters( 'wpml_object_id', $post_id, self::$courses_slug ) );
        }

        public function pages_config( $page_routes )
        {
            if ( is_array( $page_routes ) && ! empty( $page_routes ) ) {
                $page_routes['user_url']['sub_pages']['booking_url'] = array(
                    'template'  => 'stm-lms-user-booking',
                    'protected' => true,
                    'url'       => 'booking',
                );
            }

            return $page_routes;
        }

        public function stm_lms_edit_user_answer()
        {
            check_ajax_referer( 'stm_lms_edit_user_answer', 'nonce' );

            $status        = ( 'approve' === $_POST['status'] ) ? 'passed' : 'not_passed';
            $assignment_id = intval( $_POST['assignment_id'] );
            $comment       = wp_kses_post( $_POST['content'] );

            if ( get_post_status( $assignment_id ) !== 'pending' ) {
                die;
            }

            $student_id = get_post_meta( $assignment_id, 'student_id', true );
            $course_id  = get_post_meta( $assignment_id, 'course_id', true );

            wp_update_post(
                array(
                    'ID'          => $assignment_id,
                    'post_status' => 'publish',
                )
            );

            update_post_meta( $assignment_id, 'editor_comment', $comment );
            update_post_meta( $assignment_id, 'status', $status );
            update_post_meta( $assignment_id, 'who_view', 0 );

            if ( 'passed' === $status ) {
                self::update_course_progress( $student_id, $course_id );
            }

            $student = STM_LMS_User::get_current_user( $student_id );

            $message = esc_html__( 'Your assignment has been checked', 'masterstudy-lms-learning-management-system-pro' );
            STM_LMS_Helpers::send_email(
                $student['email'],
                esc_html__( 'Assignment status change.', 'masterstudy-lms-learning-management-system-pro' ),
                $message,
                'stm_lms_assignment_checked',
                compact( 'message' )
            );

            do_action( 'stm_lms_assignment_' . $status, $student_id, $assignment_id );

            wp_send_json( 'OK' );
        }

        public function assignment_saved( $post_id )
        {
            /* Remove parse_query filter */
            remove_filter( 'parse_query', array( $this, 'filter_assignments' ) );

            if ( get_post_type( $post_id ) === 'stm-user-assignment' ) {
                /* We can't have status on draft/pending assignment */
                if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending' ), true ) ) {
                    update_post_meta( $post_id, 'status', '' );
                }

                /* We can't have empty status on any post status except pending */
                if ( is_admin() &&
                    ( isset( $_POST['status'] ) && '' === $_POST['status'] ) && // phpcs:ignore WordPress.Security.NonceVerification
                    ( isset( $_POST['post_status'] ) && 'draft' !== $_POST['post_status'] ) // phpcs:ignore WordPress.Security.NonceVerification
                ) {
                    remove_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );

                    wp_update_post(
                        array(
                            'ID'          => $post_id,
                            'post_status' => 'pending',
                        )
                    );

                    add_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );
                }

                /* Update Course Progress */
                $student_id = get_post_meta( $post_id, 'student_id', true );
                $course_id  = get_post_meta( $post_id, 'course_id', true );
                self::update_course_progress( $student_id, $course_id );
            }
        }

        public static function update_course_progress( $user_id, $course_id )
        {
            $last_progress_time = get_user_meta( $user_id, 'last_progress_time', true );

            if ( empty( $last_progress_time ) ) {
                $last_progress_time = array();
            }
            $last_progress_time[ $course_id ] = time();

            update_user_meta( $user_id, 'last_progress_time', $last_progress_time );

            $course_materials = self::get_curriculum( $course_id );
            $course_materials = self::curriculum_filter( $course_id, $course_materials );

            STM_LMS_Helpers::transform_to_wpml_curriculum( $course_materials );

            $total_items    = count( $course_materials );
            $passed_items   = array();
            $passed_quizzes = array_unique( STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_course_quizzes( $user_id, null, array( 'quiz_id' ) ), 'quiz_id' ) );
            $passed_lessons = STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_course_lessons( $user_id, $course_id, array( 'lesson_id' ) ) );

            foreach ( $passed_lessons as $lesson_id ) {
                // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                if ( in_array( $lesson_id, $course_materials ) ) {
                    $passed_items[] = $lesson_id;
                }
            }
            foreach ( $passed_quizzes as $quiz_id ) {
                $quiz_data = STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_last_quiz( $user_id, $quiz_id, array( 'status' ) ), 'status' );
                if ( ! empty( $quiz_data[0] ) && 'failed' === $quiz_data[0] ) {
                    continue;
                }
                // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                if ( in_array( $quiz_id, $course_materials ) ) {
                    $passed_items[] = $quiz_id;
                }
            }

            $passed_items = count( array_unique( $passed_items ) );

            $passed_items = apply_filters( 'stm_lms_course_passed_items', $passed_items, $course_materials, $user_id );

            $progress = 0;
            if ( $passed_items > 0 && $total_items > 0 ) {
                $progress = ( 100 * $passed_items ) / $total_items;
            }

            $user_course = stm_lms_get_user_course( $user_id, $course_id, array( 'user_course_id' ) );

            /*
            TODO
            We even add course to user from drip content
            Need some check to this
            Add course if not exist
            if ( empty( $user_course ) ) {
                // STM_LMS_Course::add_user_course($course_id, $user_id, 0, $progress);
                // $user_course = stm_lms_get_user_course($user_id, $course_id, array('user_course_id'));
            }
            */

            $user_course    = STM_LMS_Helpers::simplify_db_array( $user_course );
            $user_course_id = empty( $user_course['user_course_id'] ) ? 0 : $user_course['user_course_id'];

            do_action( 'stm_lms_progress_updated', $course_id, $user_id, $progress );

            stm_lms_update_user_course_progress( $user_course_id, $progress );

            if ( 100 === $progress ) {
                stm_lms_update_user_course_endtime( $user_course['user_course_id'], time() );
            }
        }

        public static function get_last_lesson( $post_id )
        {
            $materials = ( new CurriculumMaterialRepository() )->get_sorted_course_material_ids( $post_id );
            $materials = self::curriculum_filter( $post_id, $materials );

            return ! empty( $materials ) ? end( $materials ) : 0;
        }

        public static function get_first_lesson( $course_id )
        {
            $materials = self::curriculum_filter( $course_id, self::get_curriculum( $course_id ) );

            return ! empty( $materials ) ? reset( $materials ) : 0;
        }

        public static function curriculum_filter( $course_id, $materials )
        {
            $plans    = new STM_Plans;

            if ( ! empty( $materials ) && $plans->enable( $course_id ) ) {
                $_user_id = get_current_user_id();

                if ( $author_id = get_post_field( 'post_author', $course_id ) ) {
                    $author_id = absint( $author_id );
                }

                if ( ( ! STM_LMS_Instructor::is_instructor( $_user_id ) || $_user_id !== $author_id ) ) {
                    $user_plan = self::get_user_meta_key( $_user_id, $course_id );

                    if ( ! empty( $user_plan ) ) {
                        foreach ( $materials as $material_index => $material_id ) {

                            if ( self::plan_exists( $material_id, $user_plan ) ) {
                                unset( $materials[ $material_index ] );
                            }

                        }
                    }
                    else {
                        $materials = array();
                    }
                }
            }

            return array_values( $materials );
        }

        public static function plan_exists( $material_id, $user_plan ): bool
        {
            $enable         = false;
            $plans          = new STM_Plans;
            $_material_plan = self::get_curriculum_meta_key( $material_id, $user_plan );

            if ( empty( $_material_plan ) ) {
                foreach ( $plans->plans as $plan ) {
                    $_material_plan = self::get_curriculum_meta_key( $material_id, $plan['name'] );

                    if ( ! empty( $_material_plan ) && ! $enable ) {
                        $enable = true;
                    }
                }
            }

            return $enable;
        }

        public static function item_url( $course_id, $item_id )
        {
            if ( empty( $item_id ) ) {
                $item_id = self::get_first_lesson( $course_id );
            }
            return esc_url( get_the_permalink( $course_id ) . stm_lms_get_wpml_binded_id( $item_id ) );
        }

        public function create_question( $result )
        {
            $result['plans'] = array();

            if ( ! empty( $this->plans ) ) {
                foreach ( $this->plans as $plan ) {
                    $result['plans'][ self::key( $plan['name'] ) ] = false;
                }
            }

            return $result;
        }

        public function complete_lesson()
        {
            check_ajax_referer( 'stm_lms_complete_lesson', 'nonce' );

            $user = STM_LMS_User::get_current_user();
            if ( empty( $user['id'] ) || empty( $_GET['course'] ) || empty( $_GET['lesson'] ) ) {
                die;
            }

            $user_id   = $user['id'];
            $course_id = intval( $_GET['course'] );
            $lesson_id = intval( $_GET['lesson'] );

            /* Check if already passed */
            if ( STM_LMS_Lesson::is_lesson_completed( $user_id, $course_id, $lesson_id ) ) {
                wp_send_json( compact( 'user_id', 'course_id', 'lesson_id' ) );
                die;
            }

            /* Check if lesson in course */
            $course_materials = self::get_curriculum( $course_id );
            $course_materials = self::curriculum_filter( $course_id, $course_materials );

            if ( empty( $course_materials ) || ! in_array( $lesson_id, $course_materials, true ) ) {
                die;
            }

            $end_time   = time();
            $start_time = get_user_meta($user_id, 'stm_lms_course_started_' . $course_id . '_' . $lesson_id, true);

            stm_lms_add_user_lesson(
                compact(
                    'user_id',
                    'course_id',
                    'lesson_id',
                    'start_time',
                    'end_time'
                )
            );

            self::update_course_progress( $user_id, $course_id );

            do_action( 'stm_lms_lesson_passed', $user_id, $lesson_id, $course_id );

            delete_user_meta( $user_id, 'stm_lms_course_started_' . $course_id . '_' . $lesson_id );

            wp_send_json( compact( 'user_id', 'course_id', 'lesson_id' ) );
        }

        public static function total_progress()
        {
            check_ajax_referer( 'stm_lms_total_progress', 'nonce' );

            wp_send_json(
                self::get_total_progress( get_current_user_id(), intval( $_GET['course_id'] ?? 0 ) )
            );
        }

        public static function get_total_progress( $user_id, $course_id )
        {
            if ( empty( $user_id ) ) return null;

            $data = array(
                'course'           => STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course_id ) ),
                'curriculum'       => array(),
                'course_completed' => false,
            );

            if ( ( ! empty( $data['course']['progress_percent'] ) ) && $data['course']['progress_percent'] > 100 ) {
                $data['course']['progress_percent'] = 100;
            }

            /* Curriculum */
            $course_materials = ( new CurriculumMaterialRepository() )->get_course_materials( $course_id );
            $course_materials = self::curriculum_filter( $course_id, $course_materials );
            $curriculum_data  = array();

            foreach ( $course_materials as $item_id ) {
                $lesson              = STM_LMS_Lesson::get_lesson_info( $course_id, $item_id );
                $lesson['completed'] = STM_LMS_Lesson::is_lesson_completed( $user_id, $course_id, $item_id );
                if ( 'lesson' === $lesson['type'] ) {
                    $lesson_type = get_post_meta( $item_id, 'type', true );
                    if ( empty( $lesson_type ) ) {
                        $lesson_type = 'text';
                    }
                    $lesson['lesson_type'] = $lesson_type;
                }
                $curriculum_data[] = $lesson;
            }

            foreach ( $curriculum_data as $item_data ) {
                $type = ( 'lesson' === $item_data['type'] && 'text' !== $item_data['lesson_type'] )
                    ? 'multimedia'
                    : $item_data['type'];
                if ( empty( $data['curriculum'][ $type ] ) ) {
                    $data['curriculum'][ $type ] = array(
                        'total'     => 0,
                        'completed' => 0,
                    );
                }

                $data['curriculum'][ $type ]['total']++;

                if ( $item_data['completed'] ) {
                    $data['curriculum'][ $type ]['completed']++;
                }
            }

            $data['title'] = get_the_title( $course_id );
            $data['url']   = get_permalink( $course_id );

            $booking_url = '';
            $stm_lms_course_plan = self::get_user_meta_key( $user_id, $course_id );
            if ( $stm_lms_course_plan === 'vip' ) {
                $booking_url = STM_LMS_Bookit_Sync::menu_url();
            }

            $data['booking_url'] = $booking_url;

            if ( empty( $data['course'] ) ) {
                $data['course'] = array(
                    'progress_percent' => 0,
                );

                return $data;
            }

            /*Completed label*/
            $threshold                = STM_LMS_Options::get_option( 'certificate_threshold', 70 );
            $data['course_completed'] = intval( $threshold ) <= intval( $data['course']['progress_percent'] );
            $data['certificate_url']  = STM_LMS_Course::certificates_page_url( $course_id );

            return $data;
        }
    }