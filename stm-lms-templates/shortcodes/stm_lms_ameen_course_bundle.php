<?php
    /**
     * @var $course_bundle
    */

    use LMS\inc\classes\STM_Course;

    $course = get_post( $course_bundle );

    if ( ! empty( $course ) && $course->ID ) :
        $_courses = STM_Course::get_course_bundle( $course->ID );

        if ( empty( $_courses ) ) {
            return;
        }
?>
<div class="stm_lms_ameen_course_bundle">
    <div class="stm_lms_ameen_course_bundle__slides">
        <div class="stm_lms_ameen_course_bundle__slider">
            <div class="swiper-wrapper">
                <?php foreach ( $_courses as $_course ) : ?>
                    <div class="swiper-slide">
                        <a href="<?php echo esc_url( get_the_permalink( $_course ) ); ?>" class="stm_lms_ameen_course_bundle__item">
                            <img src="<?php echo esc_url( get_the_post_thumbnail_url( $_course ) ); ?>" alt="<?php echo esc_attr( get_the_title( $_course ) ) ?>">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="stm_lms_ameen_course_bundle__content">
        <h3><?php echo esc_html( $course->post_title ); ?></h3>

        <?php echo get_the_excerpt( $course ); ?>
    </div>
</div>
<?php endif;