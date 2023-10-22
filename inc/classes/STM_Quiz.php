<?php
    namespace LMS\inc\classes;

    use STM_LMS_Helpers;
    use STM_LMS_User;
    use WP_Query;
    use STM_LMS_Quiz;

    class STM_Quiz extends STM_Curriculum
    {
        public function __construct()
        {
            parent::__construct();

            remove_action('wp_ajax_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers');
            remove_action('wp_ajax_nopriv_stm_lms_user_answers', 'STM_LMS_Quiz::user_answers');
            add_action( 'wp_ajax_stm_lms_user_answers', array( $this, 'user_answers' ) );
            add_action( 'wp_ajax_nopriv_stm_lms_user_answers', array( $this, 'user_answers' ) );

            remove_action('wp_ajax_stm_lms_add_h5p_result', 'STM_LMS_Quiz::h5p_results');
            remove_action('wp_ajax_nopriv_stm_lms_add_h5p_result', 'STM_LMS_Quiz::h5p_results');
            add_action( 'wp_ajax_stm_lms_add_h5p_result', array( $this, 'h5p_results' ) );
            add_action( 'wp_ajax_nopriv_stm_lms_add_h5p_result', array( $this, 'h5p_results' ) );
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

        public static function user_answers() {
            check_ajax_referer( 'user_answers', 'nonce' );

            $source   = ( ! empty( $_POST['source'] ) ) ? intval( $_POST['source'] ) : '';
            $sequency = ! empty( $_POST['questions_sequency'] ) ? $_POST['questions_sequency'] : array();
            $sequency = wp_json_encode( $sequency );
            $user     = STM_LMS_User::get_current_user();
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
            $quiz_id         = intval( $_POST['quiz_id'] );
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
            $passing_grade                 = ( ! empty( $quiz_info['passing_grade'] ) ) ? intval( $quiz_info['passing_grade'] ) : 0;

            $user_quizzes   = stm_lms_get_user_quizzes( $user_id, $quiz_id, array( 'user_quiz_id', 'progress' ) );
            $attempt_number = count( $user_quizzes ) + 1;
            $prev_answers   = ( 1 !== $attempt_number ) ? stm_lms_get_user_answers( $user_id, $quiz_id, $attempt_number - 1, true, array( 'question_id' ) ) : array();

            foreach ( $_POST as $question_id => $value ) {
                if ( is_numeric( $question_id ) ) {
                    $question_id = intval( $question_id );
                    $type        = get_post_meta( $question_id, 'type', true );

                    if ( 'fill_the_gap' === $type || 'multi_choice' === $type ) {
                        $answer = STM_LMS_Quiz::encode_answers( $value );
                    } else {
                        if ( is_array( $value ) ) {
                            $answer = STM_LMS_Quiz::sanitize_answers( $value );
                        } else {
                            $answer = sanitize_text_field( $value );
                        }
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
            $progress  = round( $progress );
            $status    = ( $progress < $passing_grade ) ? 'failed' : 'passed';
            $user_quiz = compact( 'user_id', 'course_id', 'quiz_id', 'progress', 'status', 'sequency' );

            stm_lms_add_user_quiz( $user_quiz );

            /*REMOVE TIMER*/
            stm_lms_get_delete_user_quiz_time( $user_id, $quiz_id );

            if ( 'passed' === $status ) {
                self::update_course_progress( $user_id, $course_id );
                $user_login   = $user['login'];
                $course_title = get_the_title( $course_id );
                $quiz_name    = get_the_title( $quiz_id );
                $message      = sprintf(
                /* translators: %1$s Course Title, %2$s User Login */
                    esc_html__( '%1$s completed the %2$s on the course %3$s with a Passing grade of %4$s%% and a result of %5$s%%', 'masterstudy-lms-learning-management-system' ),
                    $user_login,
                    $quiz_name,
                    $course_title,
                    $passing_grade,
                    $progress
                );

                STM_LMS_Helpers::send_email( $user['email'], 'Quiz Completed', $message, 'stm_lms_course_quiz_completed_for_user', compact( 'user_login', 'course_title', 'quiz_name', 'passing_grade', 'progress' ) );
            }

            $user_quiz['passed']   = $progress >= $passing_grade;
            $user_quiz['progress'] = round( $user_quiz['progress'] );
            $user_quiz['url']      = '<a class="btn btn-default btn-close-quiz-modal-results" href="' . apply_filters( 'stm_lms_item_url_quiz_ended', self::item_url( $course_id, $quiz_id ) ) . '">' . esc_html__( 'Close', 'masterstudy-lms-learning-management-system' ) . '</a>';
            $user_quiz['url']      = apply_filters( 'user_answers__course_url', $user_quiz['url'], $source );

            do_action( 'stm_lms_quiz_' . $status, $user_id, $quiz_id, $user_quiz['progress'], $course_id );

            wp_send_json( $user_quiz );
        }
    }