<?php
    /**
     * @var $course_bundle
    */

    $course = get_post( $course_bundle );

    if ( ! empty( $course ) && $course->ID ) :
?>
<div class="stm_lms_ameen_course_bundle">
    <div class="stm_lms_ameen_course_bundle__slides">

    </div>
    <div class="stm_lms_ameen_course_bundle__content">
        <h3><?php echo esc_html( $course->post_title ); ?></h3>

        <?php echo get_the_excerpt( $course ); ?>
    </div>
</div>
<?php endif;