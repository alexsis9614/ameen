<?php
    namespace LMS\child\classes;

    use STM_LMS_User_Manager_Interface;
    use STM_LMS_User_Manager_Course_User;
    use STM_LMS_Helpers;
    use STM_LMS_Lesson;
    use STM_LMS_User;

    class STM_Student_Progress extends STM_Course
    {
        public $_ajax_dashboard = 'stm_lms_dashboard';

        public function __construct()
        {

            parent::__construct();

            if ( class_exists('STM_LMS_User_Manager_Course_User') ) {

                remove_all_actions('wp_ajax_' . $this->_ajax_dashboard . '_reset_student_progress', 10);
                add_action('wp_ajax_' . $this->_ajax_dashboard . '_reset_student_progress', array( $this, 'reset' ) );

                remove_all_actions('wp_ajax_' . $this->_ajax_dashboard . '_set_student_item_progress', 10);
                add_action( 'wp_ajax_' . $this->_ajax_dashboard . '_set_student_item_progress', array( $this, 'set' ) );

                remove_all_actions('wp_ajax_' . $this->_ajax_dashboard . '_get_student_progress', 10);
                add_action( 'wp_ajax_' . $this->_ajax_dashboard . '_get_student_progress', array( $this, 'get' ) );

            }

        }

        public function get()
        {
            check_ajax_referer( $this->_ajax_dashboard . '_get_student_progress', 'nonce' );

            if ( ! STM_LMS_User_Manager_Interface::isInstructor() ) {
                die;
            }

            $request_body = file_get_contents( 'php://input' );

            $data = json_decode( $request_body, true );

            if ( empty( $data['user_id'] ) || empty( $data['course_id'] ) ) {
                die;
            }

            $course_id  = intval( $data['course_id'] );
            $student_id = intval( $data['user_id'] );

            wp_set_current_user( $student_id );

            $data = self::_get( $course_id, $student_id );

            wp_send_json( $data );
        }

        public static function _get( $course_id, $student_id ) {
            $curriculum = get_post_meta( $course_id, self::$_meta_key, true );

            $curriculum    = explode( ',', $curriculum );
            $curriculum    = self::curriculum_filter( $course_id, $curriculum );
            $sections_data = STM_LMS_Lesson::create_sections( $curriculum );

            $sections = array();
            foreach ( $sections_data as $sections_datum ) {
                $sections[] = $sections_datum;
            }

            foreach ( $sections as &$section_info ) {

                $curriculum = ( ! empty( $section_info['items'] ) ) ? $section_info['items'] : array();

                foreach ( $curriculum as $curriculum_item ) {

                    $item_data = STM_LMS_User_Manager_Course_User::course_item_data( intval( $curriculum_item ), $student_id, $course_id );

                    $section_info['section_items'][] = $item_data;

                    if ( ! isset( $user_id ) ) {
                        $user_id = 0;
                    }
                }
            }

            $user_stats = STM_LMS_Helpers::simplify_db_array(
                stm_lms_get_user_course(
                    $student_id,
                    $course_id,
                    array(
                        'current_lesson_id',
                        'progress_percent',
                    )
                )
            );
            if ( empty( $user_stats['current_lesson_id'] ) ) {
                $user_stats['current_lesson_id'] = self::get_first_lesson( $course_id );
            }

            $lesson_type = get_post_meta( $user_stats['current_lesson_id'], 'type', true );
            if ( empty( $lesson_type ) ) {
                $lesson_type = 'text';
            }

            $user_stats['lesson_type'] = $lesson_type;

            $data = array_merge( $user_stats, array( 'sections' => $sections ) );

            $data['user']         = STM_LMS_User::get_current_user( $student_id );
            $data['course_title'] = get_the_title( $course_id );

            return $data;
        }

        public function set()
        {
            check_ajax_referer( $this->_ajax_dashboard . '_set_student_item_progress', 'nonce' );

            if ( ! STM_LMS_User_Manager_Interface::isInstructor() ) {
                die;
            }

            $request_body = file_get_contents( 'php://input' );

            $data = json_decode( $request_body, true );

            if ( empty( $data['user_id'] ) || empty( $data['course_id'] ) || empty( $data['item_id'] ) ) {
                die;
            }

            $course_id  = intval( $data['course_id'] );
            $student_id = intval( $data['user_id'] );
            $item_id    = intval( $data['item_id'] );
            $completed  = boolval( $data['completed'] );

            /*For various item types*/
            /*Check item in curriculum*/
            $curriculum = get_post_meta( $course_id, self::$_meta_key, true );

            if ( empty( $curriculum ) ) {
                die;
            }

            $curriculum = explode( ',', $curriculum );
            // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
            if ( ! in_array( $item_id, $curriculum ) ) {
                die;
            }

            $item_type = get_post_type( $item_id );

            if ( self::$curriculums[ 'lesson' ] === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_lesson( $student_id, $course_id, $item_id );
            }
            else if ( self::$curriculums[ 'assignment' ] === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_assignment( $student_id, $course_id, $item_id, $completed );
            }
            else if ( self::$curriculums[ 'quiz' ] === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_quiz( $student_id, $course_id, $item_id, $completed );
            }

            self::update_course_progress( $student_id, $course_id );

            wp_send_json( self::_get( $course_id, $student_id ) );
        }

        public function reset()
        {
            check_ajax_referer( $this->_ajax_dashboard . '_reset_student_progress', 'nonce' );

            if ( ! STM_LMS_User_Manager_Interface::isInstructor() ) {
                die;
            }

            $request_body = file_get_contents( 'php://input' );

            $data = json_decode( $request_body, true );

            if ( empty( $data['user_id'] ) || empty( $data['course_id'] ) ) {
                die;
            }

            $course_id  = intval( $data['course_id'] );
            $student_id = intval( $data['user_id'] );

            $curriculum = get_post_meta( $course_id, self::$_meta_key, true );

            if ( empty( $curriculum ) ) {
                die;
            }

            $curriculum = explode( ',', $curriculum );

            foreach ( $curriculum as $item_id ) {

                $item_type = get_post_type( $item_id );

                if ( self::$curriculums[ 'lesson' ] === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_lesson( $student_id, $course_id, $item_id );
                }
                else if ( self::$curriculums[ 'assignment' ] === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_assignment( $student_id, $course_id, $item_id );
                }
                else if ( self::$curriculums[ 'quiz' ] === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_quiz( $student_id, $course_id, $item_id );
                }
            }

            stm_lms_reset_user_answers( $course_id, $student_id );

            self::update_course_progress( $student_id, $course_id );

            wp_send_json( self::_get( $course_id, $student_id ) );
        }
    }