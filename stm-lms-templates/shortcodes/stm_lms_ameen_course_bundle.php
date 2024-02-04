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

        $_courses_length = count( $_courses );
?>
<div class="stm_lms_ameen_course_bundle">
    <div class="stm_lms_ameen_course_bundle__slides">
        <div class="stm_lms_ameen_course_bundle__slider">
            <div class="swiper-wrapper">
                <?php
                    $i = 0;
                    echo '<div class="swiper-slide">';
                    foreach ( $_courses as $key => $_course ) :
                        $i++;
                ?>
                    <a href="<?php echo esc_url( get_the_permalink( $_course ) ); ?>" class="stm_lms_ameen_course_bundle__item">
                        <img src="<?php echo esc_url( get_the_post_thumbnail_url( $_course ) ); ?>" alt="<?php echo esc_attr( get_the_title( $_course ) ) ?>">
                    </a>
                <?php
//                    && $i < ($_courses_length - 2)
                    if ( $i % 2 == 0 ) {
                        echo '</div><div class="swiper-slide">';
                    }
                    endforeach;
                ?>
            </div>
        </div>
    </div>
    <div class="stm_lms_ameen_course_bundle__content">
        <h3><?php echo esc_html( $course->post_title ); ?></h3>

        <p>
            <?php echo get_the_excerpt( $course ); ?>
        </p>

        <?php
            STM_LMS_Templates::show_lms_template(
                'course/parts/am-course-price',
                array( 'course_id' => $course->ID )
            );

            STM_LMS_Templates::show_lms_template(
                'global/buy-button',
                array( 'course_id' => $course->ID )
            );
        ?>
    </div>
</div>
<?php endif;