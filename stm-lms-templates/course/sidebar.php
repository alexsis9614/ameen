<?php
    if ( ! defined( 'ABSPATH' ) ) exit; //Exit if accessed directly

    use \MasterStudy\Lms\Repositories\CurriculumRepository;

    $post_id      = ( ! empty( $post_id ) ) ? $post_id : get_the_ID();
    $curriculum   = ( new CurriculumRepository() )->get_curriculum( $post_id, true );
    $price        = get_post_meta( $post_id, 'price', true );
    $sale_price   = STM_LMS_Course::get_sale_price( $post_id );
    $percent      = 0;
    $not_salebale = get_post_meta(get_the_ID(), 'not_single_sale', true);

    if ( ! empty( $sale_price ) ) {
        $percent = (( (float) $price - (float) $sale_price ) / (float) $price) * 100;
    }
?>

<div class="stm-lms-course__sidebar">

    <div class="stm-lms__sidebar--info">
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
                                <div class="stm-curriculum-section">
                                    <h3><?php echo wp_kses_post( $section['title'] ); ?></h3>
                                </div>
                    <?php
                        endif;

                        foreach ( $section['materials'] as $index => $material ) :
                            if ( empty( $section['materials'] ) ) continue;

                            $icon    = 'stmlms-text';
                            $hint    = esc_html__( 'Text Lesson', 'masterstudy-lms-learning-management-system' );

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
                    ?>
                        <div class="stm-lms-course__lessons--item">
                            <div class="stm-curriculum-item__icon" data-toggle="tooltip" data-placement="bottom" title="<?php echo wp_kses_post( $hint ); ?>">
                                <?php
                                    if ( 'video' === $type ) :
                                        echo $icon;
                                    else :
                                ?>
                                        <i class="<?php echo esc_attr( $icon ); ?>"></i>
                                <?php endif; ?>
                            </div>
                            <span class="stm-curriculum-item__name">
                                <?php echo esc_html( $material['title'] ); ?>
                            </span>
                        </div>
                    <?php
                            endforeach;
                        endforeach;
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="stm-lms__sidebar--actions">
            <?php
                $plans        = new LMS\inc\classes\STM_Plans;
                $plans_enable = $plans->enable( $post_id );

                if ( ! $not_salebale || $plans_enable ) :

                    if ( $plans_enable ) {
                        $price = $plans->price( $post_id, 'standard' );
                    }
            ?>
                <div class="stm-lms__price--wrapper">
                    <?php if ( empty( $price ) && empty( $sale_price ) ) : ?>
                        <div class="stm-lms__sidebar--price">
                            <?php esc_html_e('Free', 'masterstudy-child'); ?>
                        </div>
                    <?php elseif ( ! empty( $price ) && !empty( $sale_price ) ) : ?>
                        <del class="stm-lms__sidebar--old-price">
                            <?php echo STM_LMS_Helpers::display_price( $price ); ?>
                        </del>
                        <div class="stm-lms__sidebar--price">
                            <?php
                                echo STM_LMS_Helpers::display_price( $sale_price );

                                if ( ! empty( $percent ) ) :
                            ?>
                                <span class="stm-lms__sidebar--percent">
                                    <?php echo $percent . '%'; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="stm-lms__sidebar--price">
                            <?php
                                if ( $plans_enable ) {
                                    echo sprintf(
                                        __( 'Starting from %s', 'masterstudy-child' ),
                                        STM_LMS_Helpers::display_price( $price )
                                    );
                                }
                                else {
                                    echo STM_LMS_Helpers::display_price( $price );
                                }
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php
                endif;

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