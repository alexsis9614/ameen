<?php
    new STM_THEME_CHILD_Curriculum;

    class STM_THEME_CHILD_Curriculum extends STM_LMS_WPCFTO_AJAX
    {
        public $plans;

        public function __construct()
        {
            parent::__construct();

            $this->plans = STM_LMS_Options::get_option('course_plans', array());

            add_filter('wpcfto_options_page_setup', [$this, 'options'], 20, 1);

            add_filter('stm_wpcfto_fields', [$this, 'fields'], 20, 1);

            remove_all_filters('wpcfto_field_curriculum', 10);
            add_filter('wpcfto_field_curriculum', [$this, 'curriculum']);

            remove_all_actions('wp_ajax_stm_lms_get_curriculum_v2', 10);
            add_action('wp_ajax_stm_lms_get_curriculum_v2', [$this, 'get_curriculum']);

            remove_all_actions('wp_ajax_stm_curriculum_create_item', 10);
            add_action( 'wp_ajax_stm_curriculum_create_item', [$this, 'stm_create_curriculum'] );

            add_action('save_post_stm-courses', [$this, 'save_course']);

            remove_action('stm_lms_before_item_template_start', 'STM_LMS_Course::check_course_item');
            add_action( 'stm_lms_before_item_template_start', [$this, 'check_course_item'], 10, 2 );

            if ( class_exists('STM_LMS_User_Manager_Course_User') ) {
                remove_all_actions('wp_ajax_stm_lms_dashboard_reset_student_progress', 10);
                add_action('wp_ajax_stm_lms_dashboard_reset_student_progress', [$this, 'reset_student_progress']);

                remove_all_actions('wp_ajax_stm_lms_dashboard_set_student_item_progress', 10);
                add_action( 'wp_ajax_stm_lms_dashboard_set_student_item_progress', [$this, 'set_student_progress'] );
            }

            if ( class_exists( 'STM_LMS_Quiz' ) ) {
                remove_action('wp_ajax_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers');
                remove_action('wp_ajax_nopriv_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers');
                add_action( 'wp_ajax_stm_lms_user_answers', [$this, 'user_answers'] );
                add_action( 'wp_ajax_nopriv_stm_lms_user_answers', [$this, 'user_answers'] );

                remove_action('wp_ajax_stm_lms_add_h5p_result', 'STM_LMS_Quiz::h5p_results');
                remove_action('wp_ajax_nopriv_stm_lms_add_h5p_result', 'STM_LMS_Quiz::h5p_results');
                add_action( 'wp_ajax_stm_lms_add_h5p_result', [$this, 'h5p_results'] );
                add_action( 'wp_ajax_nopriv_stm_lms_add_h5p_result', [$this, 'h5p_results'] );
            }

            if ( class_exists( 'STM_LMS_Lesson' ) ) {
                remove_action('wp_ajax_stm_lms_complete_lesson', 'STM_LMS_Lesson::complete_lesson');
                remove_action('wp_ajax_nopriv_stm_lms_complete_lesson', 'STM_LMS_Lesson::complete_lesson');
                add_action('wp_ajax_stm_lms_complete_lesson', [$this, 'complete_lesson']);
                add_action('wp_ajax_nopriv_stm_lms_complete_lesson', [$this, 'complete_lesson']);
            }

            if ( class_exists( 'STM_LMS_Assignments_Columns' ) ) {
                remove_action('save_post', ['STM_LMS_Assignments_Columns', 'assignment_saved']);
                add_action( 'save_post', array( $this, 'assignment_saved' ), 99999 );
            }

            if ( class_exists( 'STM_LMS_User_Assignment' ) ) {
                remove_all_actions('wp_ajax_stm_lms_edit_user_answer', 10);
                add_action( 'wp_ajax_stm_lms_edit_user_answer', array( $this, 'stm_lms_edit_user_answer' ) );
            }
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
                /* We cant have status on draft/pending assignment */
                if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending' ), true ) ) {
                    update_post_meta( $post_id, 'status', '' );
                }

                /* We cant have empty status on any post status except pending */
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

        public function complete_lesson()
        {
            check_ajax_referer('stm_lms_complete_lesson', 'nonce');

            $user = STM_LMS_User::get_current_user();
            if (empty($user['id']) or empty($_GET['course']) or empty($_GET['lesson'])) die;

            $user_id = $user['id'];
            $course_id = intval($_GET['course']);
            $lesson_id = intval($_GET['lesson']);

            /*Check if already passed*/
            if (STM_LMS_Lesson::is_lesson_completed($user_id, $course_id, $lesson_id)) {
                wp_send_json(compact('user_id', 'course_id', 'lesson_id'));
                die;
            };

            /*Check if lesson in course*/
            $curriculum = get_post_meta($course_id, 'curriculum', true);

            if (empty($curriculum)) die;
            $curriculum = STM_LMS_Helpers::only_array_numbers(explode(',', $curriculum));

            if (!in_array($lesson_id, $curriculum)) die;

            $end_time = time();
            $start_time = get_user_meta($user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}", true);
            stm_lms_add_user_lesson(compact('user_id', 'course_id', 'lesson_id', 'start_time', 'end_time'));
            self::update_course_progress($user_id, $course_id);

            do_action('stm_lms_lesson_passed', $user_id, $lesson_id);

            delete_user_meta($user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}");

            wp_send_json(compact('user_id', 'course_id', 'lesson_id'));
        }

        public function h5p_results()
        {
            check_ajax_referer( 'stm_lms_add_h5p_result', 'nonce' );

            $res = array(
                'completed' => false,
            );

            $course_id = intval( $_POST['sources']['post_id'] );
            $quiz_id   = intval( $_POST['sources']['item_id'] );
            $user_id   = get_current_user_id();

            $last_quiz = STM_LMS_Helpers::simplify_db_array(
                stm_lms_get_user_last_quiz(
                    $user_id,
                    $quiz_id,
                    array(
                        'progress',
                        'status',
                    )
                )
            );

            if ( ! empty( $last_quiz ) && ! empty( $last_quiz['status'] ) && 'passed' === $last_quiz['status'] ) {
                wp_send_json( $res );
            }

            $status = ( ! empty( $_POST['success'] ) ) ? sanitize_text_field( $_POST['success'] ) : 'failed';

            $status = ( ! empty( $status ) && 'true' === $status ) ? 'passed' : 'failed';

            stm_lms_get_delete_user_quiz_time( $user_id, $quiz_id );

            $progress = ( isset( $_POST['score']['scaled'] ) ) ? intval( $_POST['score']['scaled'] * 100 ) : 0;

            /*We have no success, but we have progress now!*/
            if ( ! isset( $_POST['success'] ) ) {
                if ( 100 === $progress ) {
                    $status = 'passed';
                }
            }

            $sequency = '';

            $res['completed'] = ( 'passed' === $status );
            $res['progress']  = $progress;
            $res['status']    = $status;

            $user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status', 'sequency' );
            stm_lms_add_user_quiz( $user_quiz );

            if ( 'passed' === $status ) {
                self::update_course_progress( $user_id, $course_id );
            }

            wp_send_json( $res );
        }

        public function user_answers()
        {
            check_ajax_referer( 'user_answers', 'nonce' );

            $source   = ( ! empty( $_POST['source'] ) ) ? intval( $_POST['source'] ) : '';
            $sequency = ! empty( $_POST['questions_sequency'] ) ? $_POST['questions_sequency'] : array();
            $sequency = json_encode( $sequency );
            $user     = apply_filters( 'user_answers__user_id', STM_LMS_User::get_current_user(), $source );
            /*Checking Current User*/
            if ( ! $user['id'] ) {
                die;
            }
            $user_id   = $user['id'];
            $course_id = ( ! empty( $_POST['course_id'] ) ) ? intval( $_POST['course_id'] ) : '';
            $course_id = apply_filters( 'user_answers__course_id', $course_id, $source );

            if ( empty( $course_id ) || empty( $_POST['quiz_id'] ) ) {
                die;
            }
            $quiz_id = intval( $_POST['quiz_id'] );
            $progress        = 0;
            $quiz_info       = STM_LMS_Helpers::parse_meta_field( $quiz_id );
            $total_questions = count( explode( ',', $quiz_info['questions'] ) );

            $questions = explode( ',', $quiz_info['questions'] );

            foreach ( $questions as $question ) {
                $type = get_post_meta( $question, 'type', true );

                if ( 'question_bank' !== $type ) {
                    continue;
                }

                $answers = get_post_meta( $question, 'answers', true );

                if ( ! empty( $answers[0] ) && ! empty( $answers[0]['categories'] ) && ! empty( $answers[0]['number'] ) ) {
                    $number     = $answers[0]['number'];
                    $categories = wp_list_pluck( $answers[0]['categories'], 'slug' );

                    $questions = get_post_meta( $quiz_id, 'questions', true );
                    $questions = ( ! empty( $questions ) ) ? explode( ',', $questions ) : array();

                    $args = array(
                        'post_type'      => 'stm-questions',
                        'posts_per_page' => $number,
                        'post__not_in'   => $questions,
                        'tax_query'      => array(
                            array(
                                'taxonomy' => 'stm_lms_question_taxonomy',
                                'field'    => 'slug',
                                'terms'    => $categories,
                            ),
                        ),
                    );

                    $q = new WP_Query( $args );

                    if ( $q->have_posts() ) {

                        $total_in_bank = $q->found_posts - 1;
                        if ( $total_in_bank > $number ) {
                            $total_in_bank = $number - 1;
                        }
                        $total_questions += $total_in_bank;
                        wp_reset_postdata();
                    }
                }
            }
            $single_question_score_percent = 100 / $total_questions;
            $cutting_rate                  = ( ! empty( $quiz_info['re_take_cut'] ) ) ? ( 100 - $quiz_info['re_take_cut'] ) / 100 : 1;
            $passing_grade                 = ( ! empty( $quiz_info['passing_grade'] ) ) ? $quiz_info['passing_grade'] : 0;

            $user_quizzes   = stm_lms_get_user_quizzes( $user_id, $quiz_id, array( 'user_quiz_id', 'progress' ) );
            $attempt_number = count( $user_quizzes ) + 1;
            $prev_answers   = ( 1 !== $attempt_number ) ? stm_lms_get_user_answers( $user_id, $quiz_id, $attempt_number - 1, true, array( 'question_id' ) ) : array();

            foreach ( $_POST as $question_id => $value ) {
                if ( is_numeric( $question_id ) ) {
                    $question_id = intval( $question_id );

                    if ( is_array( $value ) ) {
                        $answer = STM_LMS_Quiz::sanitize_answers( $value );
                    } else {
                        $answer = sanitize_text_field( $value );
                    }

                    $user_answer = ( is_array( $answer ) ) ? implode( ',', $answer ) : $answer;

                    $correct_answer = STM_LMS_Quiz::check_answer( $question_id, $answer );

                    if ( $correct_answer ) {
                        if ( 1 === $attempt_number || STM_LMS_Helpers::in_array_r( $question_id, $prev_answers ) ) {
                            $single_question_score = $single_question_score_percent;
                        } else {
                            $single_question_score = $single_question_score_percent * $cutting_rate;
                        }

                        $progress += $single_question_score;
                    }

                    $add_answer = compact( 'user_id', 'course_id', 'quiz_id', 'question_id', 'attempt_number', 'user_answer', 'correct_answer' );
                    stm_lms_add_user_answer( $add_answer );
                }
            }

            /*Add user quiz*/
            $progress  = round( $progress, 5 );
            $status    = ( $progress < $passing_grade ) ? 'failed' : 'passed';
            $user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status', 'sequency' );

            stm_lms_add_user_quiz( $user_quiz );

            /*REMOVE TIMER*/
            stm_lms_get_delete_user_quiz_time( $user_id, $quiz_id );

            if ( 'passed' === $status ) {
                self::update_course_progress( $user_id, $course_id );
                $user_login         = $user['login'];
                $course_title  = get_the_title( $course_id );
                $quiz_name     = get_the_title( $quiz_id );
                $passing_grade = round( $user_quiz['progress'], 1 );
                $message       = sprintf(
                /* translators: %1$s Course Title, %2$s User Login */
                    esc_html__( '%1$s completed the %2$s on the course %3$s with a Passing grade of %4$s%%', 'masterstudy-lms-learning-management-system' ),
                    $user_login,
                    $quiz_name,
                    $course_title,
                    $passing_grade,
                );

                STM_LMS_Mails::send_email( 'Quiz Completed', $message, $user['email'], array(), 'stm_lms_course_quiz_completed_for_user', compact( 'user_login', 'course_title', 'quiz_name', 'passing_grade' ) );

            }
            $user_quiz['passed']   = $progress >= $passing_grade;
            $user_quiz['progress'] = round( $user_quiz['progress'], 1 );
            $user_quiz['url']      = '<a class="btn btn-default btn-close-quiz-modal-results" href="' . apply_filters( 'stm_lms_item_url_quiz_ended', STM_LMS_Course::item_url( $course_id, $quiz_id ) ) . '">' . esc_html__( 'Close', 'masterstudy-lms-learning-management-system' ) . '</a>';
            $user_quiz['url']      = apply_filters( 'user_answers__course_url', $user_quiz['url'], $source );

            do_action( 'stm_lms_quiz_' . $status, $user_id, $quiz_id, $user_quiz['progress'] );

            wp_send_json( $user_quiz );
        }

        public function set_student_progress()
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
            $curriculum = get_post_meta( $course_id, 'curriculum', true );

            if ( empty( $curriculum ) ) {
                die;
            }

            $curriculum = explode( ',', $curriculum );
            // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
            if ( ! in_array( $item_id, $curriculum ) ) {
                die;
            }

            $item_type = get_post_type( $item_id );

            if ( 'stm-lessons' === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_lesson( $student_id, $course_id, $item_id );
            } elseif ( 'stm-assignments' === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_assignment( $student_id, $course_id, $item_id, $completed );
            } elseif ( 'stm-quizzes' === $item_type ) {
                STM_LMS_User_Manager_Course_User::complete_quiz( $student_id, $course_id, $item_id, $completed );
            }

            self::update_course_progress( $student_id, $course_id );

            wp_send_json( STM_LMS_User_Manager_Course_User::_student_progress( $course_id, $student_id ) );
        }

        public function reset_student_progress()
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

            $curriculum = get_post_meta( $course_id, 'curriculum', true );

            if ( empty( $curriculum ) ) {
                die;
            }

            $curriculum = explode( ',', $curriculum );

            foreach ( $curriculum as $item_id ) {

                $item_type = get_post_type( $item_id );

                if ( 'stm-lessons' === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_lesson( $student_id, $course_id, $item_id );
                } elseif ( 'stm-assignments' === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_assignment( $student_id, $course_id, $item_id );
                } elseif ( 'stm-quizzes' === $item_type ) {
                    STM_LMS_User_Manager_Course_User::reset_quiz( $student_id, $course_id, $item_id );
                }
            }

            stm_lms_reset_user_answers( $course_id, $student_id );

            self::update_course_progress( $student_id, $course_id );

            wp_send_json( STM_LMS_User_Manager_Course_User::_student_progress( $course_id, $student_id ) );
        }

        public static function update_course_progress( $user_id, $course_id )
        {
            $last_progress_time = get_user_meta( $user_id, 'last_progress_time', true );

            if ( empty( $last_progress_time ) ) {
                $last_progress_time = array();
            }
            $last_progress_time[ $course_id ] = time();

            update_user_meta( $user_id, 'last_progress_time', $last_progress_time );

            $curriculum = explode( ',', get_post_meta( $course_id, 'curriculum', true ) );

            STM_LMS_Helpers::transform_to_wpml_curriculum( $curriculum );

            $curriculum_items = STM_LMS_Helpers::only_array_numbers( $curriculum );
            $curriculum_items = self::curriculum_filter( $course_id, $curriculum_items );

            $total_items = count( $curriculum_items );

            $passed_items = array();

            $passed_quizzes = array_unique( STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_course_quizzes( $user_id, null, array( 'quiz_id' ), 'passed' ), 'quiz_id' ) );
            $passed_lessons = STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_course_lessons( $user_id, $course_id, array( 'lesson_id' ) ) );

            foreach ( $passed_lessons as $lesson_id ) {
                if ( in_array( $lesson_id, $curriculum ) ) {
                    $passed_items[] = $lesson_id;
                }
            }
            foreach ( $passed_quizzes as $quiz_id ) {
                $quiz_data = STM_LMS_Helpers::simplify_meta_array( stm_lms_get_user_last_quiz( $user_id, $quiz_id, array( 'status' ) ), 'status' );
                if ( ! empty( $quiz_data[0] ) && 'failed' === $quiz_data[0] ) {
                    continue;
                }
                if ( in_array( $quiz_id, $curriculum ) ) {
                    $passed_items[] = $quiz_id;
                }
            }

            $passed_items = count( array_unique( $passed_items ) );

            $passed_items = apply_filters( 'stm_lms_course_passed_items', $passed_items, $curriculum, $user_id );

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
        }

        public function check_course_item( $course_id, $item_id )
        {
            $sections = STM_LMS_Course::get_course_curriculum( $course_id );
            $sections['curriculum'] = self::curriculum_filter( $course_id, $sections['curriculum'] );

            $is_scorm = ( '0' == $item_id );

            if ( empty( $sections['curriculum'] ) && ! $is_scorm ) {
                STM_LMS_User::js_redirect( get_permalink( $course_id ) );
            }

            if ( ! in_array( $item_id, (array)$sections['curriculum']) && ! $is_scorm ) {
                STM_LMS_User::js_redirect( STM_LMS_Lesson::get_lesson_url( $course_id, ( self::get_first_lesson( $course_id ) ) ) );
            }
        }

        public static function get_first_lesson($course_id)
        {
            $item_id    = 0;
            $curriculum = get_post_meta( $course_id, 'curriculum', true );
            if ( ! empty( $curriculum ) ) {
                $curriculum = STM_LMS_Helpers::only_array_numbers( explode( ',', $curriculum ) );
                $curriculum = self::curriculum_filter($course_id, $curriculum);
            }

            if ( ! empty( $curriculum ) ) {
                $item_id = $curriculum[0];
            }

            return $item_id;
        }

        public function course_plan_enable($course_id)
        {
            if ( ! empty( $this->plans ) ) {
                foreach ($this->plans as $plan) {
                    $price = self::plan_price($course_id, $plan['name']);
                    if ( ! empty( $price ) ) {
                        return true;
                    }
                }
            }

            return false;
        }

        public static function curriculum_filter( $course_id, $curriculum )
        {
            $curriculum_items = array();

            if ( ! empty( $curriculum ) ) {
                $sections = STM_LMS_Lesson::create_sections( $curriculum );

                if ( ! empty( $sections ) ) {
                    foreach ($sections as $section_info) {
                        $section_curriculum = ( ! empty( $section_info['items'] ) ) ? $section_info['items'] : array();

                        if ( ! empty( $section_curriculum ) ) {
                            foreach ( $section_curriculum as $curriculum_item ) {
                                $user     = STM_LMS_User::get_current_user();
                                $user_id  = $user['id'];

                                $stm_lms_course_plan = get_user_meta($user_id, 'stm_lms_course_plan_' . $course_id, true);
                                $course_plan         = get_post_meta($curriculum_item, 'course_plan_' . strtolower( $stm_lms_course_plan ) . '_' . $course_id, true);

                                if ( empty( $course_plan ) ) {
                                    continue;
                                }

                                $curriculum_items[] = $curriculum_item;
                            }
                        }
                    }
                }
            }

            return $curriculum_items ?: $curriculum;
        }

        public static function item_url($course_id, $current_lesson)
        {
            $curriculum = get_post_meta( $course_id, 'curriculum', true );

            if ( ! empty( $curriculum ) ) {
                $curriculum = explode( ',', $curriculum );
                $curriculum = self::curriculum_filter($course_id, $curriculum);

                if ( ! empty( $curriculum ) ) {
                    return STM_LMS_Course::item_url( $course_id, $curriculum[0] );
                }
            }

            return STM_LMS_Course::item_url( $course_id, $current_lesson );
        }

        public static function plan_price_key( $plan )
        {
            return 'price_' . strtolower( $plan );
        }

        public static function plan_price( $post_id, $plan )
        {
            return get_post_meta($post_id, self::plan_price_key( $plan ), true);
        }

        public function save_course($course_id)
        {
            if ( get_post_type($course_id) !== STM_LMS_Curriculum::$courses_slug ) return;

            if ( isset( $_POST['curriculum_plans'] ) && ! empty( $_POST['curriculum_plans'] ) ) {
                $sections_curriculum_plans = json_decode( wp_unslash($_POST['curriculum_plans']), true );

                if ( ! empty( $sections_curriculum_plans ) ) {
                    foreach ($sections_curriculum_plans as $curriculum_plans) {
                        if ( ! empty( $curriculum_plans ) ) {
                            foreach ($curriculum_plans as $curriculum) {
                                $curriculum_id = $curriculum['id'];
                                if ( ! empty( $curriculum['plans'] ) ) {
                                    foreach ($curriculum['plans'] as $index => $plan) {
                                        $plan = (bool)$plan;
                                        update_post_meta($curriculum_id, 'course_plan_' . $index . '_' . $course_id, $plan);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        public function fields( $settings )
        {
            if ( ! empty( $settings ) && ! empty( $this->plans ) ) {
                foreach ($settings as $index => $sections) {
                    if ( $index === 'stm_courses_settings' ) {
                        foreach ($sections as $section_name => $section) {
                            if ( $section_name === 'section_accessibility' ) {
                                $fields      = $section['fields'];
                                $first_field = array_splice($fields, 0, 4);

                                $decimals_num = STM_LMS_Options::get_option( 'decimals_num', 2 );
                                $zeros        = str_repeat( '0', intval( $decimals_num ) - 1 );
                                $step         = "0.{$zeros}1";
                                $currency     = STM_LMS_Helpers::get_currency();

                                $new_fields = $first_field;

                                $count_plans = count( $this->plans );
                                foreach ($this->plans as $plan_index => $plan) {
                                    $new_fields[ self::plan_price_key( $plan['name'] ) ] = array(
                                        'type'        => 'number',
                                        'label'       => sprintf(
                                        /* translators: %s: number */
                                            esc_html__( 'Price %s (%s)', 'masterstudy-child' ),
                                            $plan['name'], $currency
                                        ),
                                        'placeholder' => sprintf( esc_html__( 'Leave empty if course is free', 'masterstudy-lms-learning-management-system' ), $currency ),
                                        'sanitize'    => 'wpcfto_save_number',
                                        'step'        => $step,
                                        'columns'     => 50,
                                        'dependency'  => array(
                                            'key'   => 'not_single_sale',
                                            'value' => 'empty',
                                        ),
                                    );

                                    if ( $plan_index === 0 ) {
                                        $new_fields[ self::plan_price_key( $plan['name'] ) ]['group'] = 'started';
                                    }

                                    if ( $count_plans === ($plan_index + 1) ) {
                                        $new_fields[ self::plan_price_key( $plan['name'] ) ]['group'] = 'ended';
                                    }
                                }

                                $fields = array_merge( $new_fields, $fields );

                                $settings[ $index ][ $section_name ]['fields'] = $fields;
                            }
                        }
                    }
                }
            }

            return $settings;
        }

        public function options( $setups )
        {
            if ( ! empty( $setups ) ) {
                foreach ($setups as &$setup) {
                    if (array_key_exists('fields', $setup)) {
                        $setup['fields']['section_course']['fields']['course_plans'] = array(
                            'type'   => 'repeater',
                            'label'  => esc_html__( 'Course plans', 'masterstudy-child' ),
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
                                    'text_button' => esc_html__( 'Get plan', 'masterstudy-child' ),
                                ),
                                array(
                                    'name'        => esc_html__('Standard', 'masterstudy-child'),
                                    'description' => '',
                                    'text_button' => esc_html__( 'Get plan', 'masterstudy-child' ),
                                ),
                                array(
                                    'name'        => esc_html__('VIP', 'masterstudy-child'),
                                    'description' => '',
                                    'text_button' => esc_html__( 'Get plan', 'masterstudy-child' ),
                                ),
                            ),
                        );
                    }
                }
            }

            return $setups;
        }

        public function stm_create_curriculum()
        {
            check_ajax_referer( 'stm_curriculum_create_item', 'nonce' );

            /*Check if data passed*/
            if ( empty( sanitize_text_field( $_GET['post_type'] ) ) ) {
                wp_send_json(
                    array(
                        'error'   => true,
                        'message' => esc_html__( 'Post Type is required', 'masterstudy-lms-learning-management-system' ),
                    )
                );
            }

            /*Check if data passed*/
            if ( empty( $_GET['title'] ) ) {
                wp_send_json(
                    array(
                        'error'   => true,
                        'message' => esc_html__( 'Title is required', 'masterstudy-lms-learning-management-system' ),
                    )
                );
            }

            $category_ids = null; // Question categories
            $post_type    = sanitize_text_field( $_GET['post_type'] );
            $title        = sanitize_text_field( urldecode( $_GET['title'] ) );

            // comma separated category ids
            if ( ! empty( $_GET['category_ids'] ) ) {
                $category_ids = sanitize_text_field( $_GET['category_ids'] );
                $category_ids = array_map( 'intval', explode( ',', $category_ids ) );
            }

            /*Check if available post type*/
            if ( ! in_array( $post_type, apply_filters( 'stm_lms_available_post_types', self::$available_post_types ) ) ) {
                wp_send_json(
                    array(
                        'error'   => true,
                        'message' => esc_html__( 'Wrong post type', 'masterstudy-lms-learning-management-system' ),
                    )
                );
            }

            if ( ! apply_filters( 'stm_lms_allow_add_lesson', true ) && 'stm-lessons' === $post_type ) {
                return;
            }

            $result   = array();
            $is_front = (bool) ( ! empty( $_GET['is_front'] ) ) ? sanitize_text_field( $_GET['is_front'] ) : false;
            $item     = array(
                'post_type'   => $post_type,
                'post_title'  => html_entity_decode( $title ),
                'post_status' => 'publish',
            );

            $result['id'] = wp_insert_post( $item );

            /** add question category if was sent */
            if ( null !== $category_ids ) {
                wp_set_object_terms( $result['id'], $category_ids, 'stm_lms_question_taxonomy' );
            }

            do_action(
                'stm_lms_item_added',
                array(
                    'id'    => $result['id'],
                    'front' => $is_front,
                )
            );

            $result['categories'] = wp_get_post_terms( $result['id'], 'stm_lms_question_taxonomy' );
            $result['is_edit']    = false;
            $result['title']      = html_entity_decode( get_the_title( $result['id'] ) );
            $result['post_type']  = $post_type;
            $result['edit_link']  = html_entity_decode( get_edit_post_link( $result['id'] ) );
            $result['plans']      = array();

            if ( ! empty( $this->plans ) ) {
                foreach ($this->plans as $plan) {
                    $result['plans'][ strtolower( $plan['name'] ) ] = false;
                }
            }

            $result = apply_filters( 'stm_lms_wpcfto_create_question', $result, array( $post_type ) );

            do_action(
                'stm_lms_item_question_added',
                array(
                    'id'    => $result['id'],
                    'front' => $is_front,
                )
            );

            wp_send_json( $result );
        }

        public function curriculum()
        {
            return STM_THEME_CHILD_DIRECTORY . '/settings/curriculum/field.php';
        }

        public function get_curriculum()
        {
            check_ajax_referer( 'stm_lms_get_curriculum_v2', 'nonce' );
            $ids  = ( isset( $ids ) ? $ids : '' );
            $args = array(
                'post_type'      => array( 'stm-lessons', 'stm-quizzes', 'stm-assignments' ),
                'posts_per_page' => - 1,
            );

            $user  = wp_get_current_user();
            $roles = (array) $user->roles;

            $course_id = 0;

            if ( ! in_array( 'administrator', $roles, true ) ) {
                $args['author'] = get_current_user_id();
            }

            if ( ! empty( $_GET['course_id'] ) ) {
                $course_id          = intval( $_GET['course_id'] );
                $authors            = array();
                $authors[]          = intval( get_post_field( 'post_author', $course_id ) );
                $authors[]          = get_post_meta( $course_id, 'co_instructor', true );
                $args['author__in'] = $authors;
            }
            if ( ! empty( $_GET['ids'] ) ) {
                $ids              = wp_unslash( esc_html( $_GET['ids'] ) );
                $args['post__in'] = explode( ',', $ids );
                $args['orderby']  = 'post__in';
            } else {
                $args['posts_per_page'] = 30;
            }
            if ( ! empty( $_GET['exclude_ids'] ) ) {
                $args['post__not_in'] = explode( ',', sanitize_text_field( $_GET['exclude_ids'] ) );
            }
            if ( ! empty( $_GET['s'] ) ) {
                $args['s'] = sanitize_text_field( $_GET['s'] );
            }
            $args       = apply_filters( 'stm_lms_search_posts_args', $args );
            $q          = new WP_Query( $args );
            $r          = array();
            $curriculum = STM_LMS_Lesson::create_sections( explode( ',', $ids ) );
            if ( $q->have_posts() ) {
                while ( $q->have_posts() ) {
                    $q->the_post();
                    $post_id       = get_the_ID();
                    $response      = array(
                        'id'        => $post_id,
                        'title'     => get_the_title(),
                        'post_type' => get_post_type( $post_id ),
                        'plans'     => array(),
                        'edit_link' => html_entity_decode( get_edit_post_link( $post_id ) ),
                    );

                    if ( ! empty( $this->plans ) ) {
                        foreach ($this->plans as $plan) {
                            $plan = strtolower( $plan['name'] );
                            $response['plans'][ $plan ] = get_post_meta($post_id, 'course_plan_' . $plan . '_' . $course_id, true) ?: false;
                        }
                    }

                    $r[ $post_id ] = $response;
                }
                wp_reset_postdata();
            }
            if ( ! empty( $curriculum ) ) {
                foreach ( $curriculum as &$section ) {
                    $section['opened']              = true;
                    $section['touched']             = true;
                    $section['editingSectionTitle'] = false;
                    if ( apply_filters( 'stm_lms_allow_add_lesson', true ) ) {
                        $section['activeTab'] = 'stm-lessons';
                    } else {
                        $section['activeTab'] = 'stm-quizzes';
                    }
                    if ( empty( $section['title'] ) ) {
                        $section['opened']  = true;
                        $section['touched'] = false;
                    }
                    if ( empty( $section['items'] ) ) {
                        continue;
                    }
                    foreach ( $section['items'] as $key => &$item ) {
                        if ( empty( $r[ $item ] ) ) {
                            unset( $section['items'][ $key ] );
                            continue;
                        }
                        $item = $r[ $item ];
                    }
                    $section['items'] = array_values( $section['items'] );
                }
            }
            if ( ! empty( $_GET['only_items'] ) ) {
                wp_send_json( array_values( $r ) );
            };
            wp_send_json( array_values( $curriculum ) );
        }

        public static function curriculum_load_template( $tpl )
        {
            require STM_THEME_CHILD_DIRECTORY . "/settings/curriculum/tpls/{$tpl}.php";
        }
    }