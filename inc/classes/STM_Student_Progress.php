<?php
    namespace LMS\inc\classes;

    use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;
    use STM_LMS_User_Manager_Interface;
    use STM_LMS_User_Manager_Course_User;
    use STM_LMS_Helpers;
    use STM_LMS_User;
    use MasterStudy\Lms\Repositories\CurriculumRepository;

    class STM_Student_Progress
    {
        public $_ajax_dashboard = 'stm_lms_dashboard';

        public function __construct()
        {

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

        public static function _get( $course_id, $student_id )
        {
            $curriculum = ( new CurriculumRepository() )->get_curriculum( $course_id );

            foreach ( $curriculum['materials'] as &$material ) {
                $material = array_merge( $material, STM_LMS_User_Manager_Course_User::course_material_data( $material, $student_id, $course_id ) );
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
                $user_stats['current_lesson_id'] = STM_Curriculum::get_first_lesson( $course_id );
            }

            $lesson_type = get_post_meta( $user_stats['current_lesson_id'], 'type', true );
            if ( empty( $lesson_type ) ) {
                $lesson_type = 'text';
            }

            $user_stats['lesson_type'] = $lesson_type;

            $curriculum = array_merge( $user_stats, $curriculum );

            $curriculum['user']         = STM_LMS_User::get_current_user( $student_id );
            $curriculum['course_title'] = get_the_title( $course_id );

            return $curriculum;
        }

        public function set()
        {
            check_ajax_referer( 'stm_lms_dashboard_set_student_item_progress', 'nonce' );

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
            $course_materials = ( new CurriculumMaterialRepository() )->get_course_materials( $course_id );
            $course_materials = STM_Curriculum::curriculum_filter( $course_id, $course_materials );

            if ( empty( $course_materials ) ) {
                die;
            }

            if ( ! in_array( $item_id, $course_materials, true ) ) {
                die;
            }

            switch ( get_post_type( $item_id ) ) {
                case 'stm-lessons':
                    STM_LMS_User_Manager_Course_User::complete_lesson( $student_id, $course_id, $item_id );
                    break;
                case 'stm-assignments':
                    STM_LMS_User_Manager_Course_User::complete_assignment( $student_id, $course_id, $item_id, $completed );
                    break;
                case 'stm-quizzes':
                    STM_LMS_User_Manager_Course_User::complete_quiz( $student_id, $course_id, $item_id, $completed );
                    break;
            }

            STM_Curriculum::update_course_progress( $student_id, $course_id );

            wp_send_json( self::_get( $course_id, $student_id ) );
        }

        public function reset()
        {
            check_ajax_referer( 'stm_lms_dashboard_reset_student_progress', 'nonce' );

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

            $curriculum = ( new CurriculumRepository() )->get_curriculum( $course_id );

            if ( empty( $curriculum['materials'] ) ) {
                die;
            }

            foreach ( $curriculum['materials'] as $material ) {
                switch ( $material['post_type'] ) {
                    case 'stm-lessons':
                        STM_LMS_User_Manager_Course_User::reset_lesson( $student_id, $course_id, $material['post_id'] );
                        break;
                    case 'stm-assignments':
                        STM_LMS_User_Manager_Course_User::reset_assignment( $student_id, $course_id, $material['post_id'] );
                        break;
                    case 'stm-quizzes':
                        STM_LMS_User_Manager_Course_User::reset_quiz( $student_id, $course_id, $material['post_id'] );
                        break;
                }
            }

            stm_lms_reset_user_answers( $course_id, $student_id );

            STM_Curriculum::update_course_progress( $student_id, $course_id );

            wp_send_json( self::_get( $course_id, $student_id ) );
        }
    }