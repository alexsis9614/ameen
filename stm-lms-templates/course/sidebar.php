<?php
    if ( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly

    use \MasterStudy\Lms\Repositories\CurriculumRepository;

    $post_id      = ( ! empty( $post_id ) ) ? $post_id : get_the_ID();
    $curriculum   = ( new CurriculumRepository() )->get_curriculum( $post_id, true );
    $course_price = STM_LMS_Course::get_course_price( $post_id );
    $not_salebale = get_post_meta( $post_id, 'not_single_sale', true );

    $is_prerequisite_passed = true;

    if ( class_exists( 'STM_LMS_Prerequisites' ) ) {
        $is_prerequisite_passed = STM_LMS_Prerequisites::is_prerequisite( true, $post_id );
    }
?>

<div class="stm-lms-course__sidebar">

    <div class="stm-lms-course__sidebar--info">
        <?php if ( ! empty( $curriculum ) ) : ?>
            <div class="stm-lms-course__lessons">
                <div class="stm-lms-course__curriculum--title">
                    <?php esc_html_e('Curriculum', 'masterstudy-child'); ?>
                </div>
                <div class="stm-lms-course__lessons--list">
                    <?php
                        foreach ( $curriculum as $section ) :
                            if ( isset( $section['title'] ) && ! empty( $section['title'] ) ) :
                    ?>
                                <div class="stm-lms-course__curriculum-section">
                                    <h3><?php echo wp_kses_post( $section['title'] ); ?></h3>
                                </div>
                    <?php
                        endif;

                        $course = STM_LMS_Helpers::simplify_db_array(
                            stm_lms_get_user_course(
                                get_current_user_id(),
                                $post_id,
                                array(
                                    'current_lesson_id',
                                    'progress_percent'
                                )
                            )
                        );

                        $current_lesson = ( ! empty( $course['current_lesson_id'] ) ) ? $course['current_lesson_id'] : '0';

                        foreach ( $section['materials'] as $index => $material ) :
                            $lesson_id    = stm_lms_get_wpml_binded_id( $material['post_id'] );
                            $icon         = 'stmlms-text';
                            $hint         = esc_html__( 'Text Lesson', 'masterstudy-lms-learning-management-system' );
                            $type         = '';
                            $classes      = 'stm-lms-course__lessons--item';
                            $is_previewed = STM_LMS_Lesson::is_previewed( $post_id, $lesson_id );

                            $has_course   = STM_LMS_User::has_course_access( $post_id, $lesson_id, false );

                            if ( isset( $has_access ) ) {
                                $has_course = $has_access;
                            }

                            $has_access = ( $has_course || ( empty( $course_price ) && ! $not_salebale ) ) && $is_prerequisite_passed;

                            if ( 'stm-quizzes' === $material['post_type'] ) {
                                $questions = get_post_meta( $material['post_id'], 'questions', true );
                                $icon      = 'stmlms-quiz';
                                $hint      = esc_html__( 'Quiz', 'masterstudy-lms-learning-management-system' );
                                if ( ! empty( $questions ) ) :
                                    $meta = sprintf(
                                    /* translators: %s: number */
                                        _n(
                                            '%s question',
                                            '%s questions',
                                            count( explode( ',', $questions ) ),
                                            'masterstudy-lms-learning-management-system'
                                        ),
                                        count( explode( ',', $questions ) )
                                    );
                                endif;
                            }
                            else {
                                $meta = get_post_meta($material['post_id'], 'duration', true);
                                $type = get_post_meta($material['post_id'], 'type', true);

                                switch ( $type ) :
                                    case 'slide':
                                        $icon = 'stmlms-slides-css';
                                        $hint = esc_html__('Slides', 'masterstudy-lms-learning-management-system');
                                        break;
                                    case 'video':
                                        $icon = '
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10 0C4.48 0 0 4.48 0 10C0 15.52 4.48 20 10 20C15.52 20 20 15.52 20 10C20 4.48 15.52 0 10 0ZM8 14.5V5.5L14 10L8 14.5Z" fill="#3366FF"/>
                                            </svg>
                                        ';
                                        $hint = esc_html__('Video', 'masterstudy-lms-learning-management-system');
                                        break;
                                    case 'stream':
                                        $icon = 'fab fa-youtube';
                                        $hint = esc_html__('Live Stream', 'masterstudy-lms-learning-management-system');
                                        break;
                                    case 'zoom_conference':
                                        $icon = 'fas fa-video';
                                        $hint = esc_html__('Zoom meeting', 'masterstudy-lms-learning-management-system');
                                        break;
                                endswitch;
                            }

                            if ( $has_access || $is_previewed ) :
                                if ( absint( $current_lesson ) === absint( $lesson_id ) || $is_previewed ) {
                                    $classes .= " stm-lms-course__lessons--item__current";
                                }
                    ?>
                                <a href="<?php echo esc_url( STM_LMS_Lesson::get_lesson_url( $post_id, $lesson_id ) ); ?>" class="<?php echo esc_attr( $classes ); ?>">
                            <?php else : ?>
                                <div class="<?php echo esc_attr( $classes ); ?>">
                            <?php endif; ?>
                            <div class="stm-lms-course__curriculum-item--icon" data-toggle="tooltip" data-placement="bottom" title="<?php echo wp_kses_post( $hint ); ?>">
                                <?php
                                    if ( 'video' === $type ) :
                                        echo $icon;
                                    else :
                                ?>
                                        <i class="<?php echo esc_attr( $icon ); ?>"></i>
                                <?php endif; ?>
                            </div>
                            <span class="stm-lms-course__curriculum-item--name">
                                <?php echo esc_html( $material['title'] ); ?>
                            </span>
                        <?php if ( $has_access || $is_previewed ) : ?>
                            </a>
                        <?php else : ?>
                            </div>
                        <?php endif; ?>
                    <?php
                            endforeach;
                        endforeach;
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="stm-lms-course__sidebar--actions">
            <?php
                STM_LMS_Templates::show_lms_template( 'course/parts/am-course-price', array( 'course_id' => $post_id ) );
                STM_LMS_Templates::show_lms_template( 'global/buy-button', array( 'course_id' => $post_id ) );
                STM_LMS_Templates::show_lms_template( 'global/wish-list', array( 'course_id' => $post_id ) );
            ?>
        </div>
    </div>

    <?php STM_LMS_Templates::show_lms_template('global/expired_course', array('course_id' => $post_id)); ?>

	<?php STM_LMS_Templates::show_lms_template('global/completed_info', array('course_id' => $post_id)); ?>

	<?php STM_LMS_Templates::show_lms_template('course/parts/info', array('course_id' => $post_id)); ?>

	<?php STM_LMS_Templates::show_lms_template('course/parts/dynamic_sidebar', array('course_id' => $post_id)); ?>

</div>