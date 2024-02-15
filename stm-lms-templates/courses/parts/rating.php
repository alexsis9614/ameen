<?php
    /**
     * @var $id
     *
     */

    if ( ! STM_LMS_Options::get_option( 'enable_card_rating', false ) ) {
        return;
    }

    $rating = get_post_meta($id, 'course_marks', true);

    $average = $percent = $total = '';

    if ( ! empty( $rating ) ) {
        $rates = STM_LMS_Course::course_average_rate( $rating );
        $average = $rates['average'];
        $percent = $rates['percent'];
        $total   = count( $rating );
    }
?>

<?php if ( ! empty( $average ) ) : ?>
    <div class="stm_lms_course__panel_rate">
        <div class="average-rating-stars__top">
            <div class="average-rating-stars__av heading_font">
                <?php echo sanitize_text_field( $average ); ?>
            </div>
            <div class="star-rating">
                <span style="width: <?php echo esc_attr( $percent ); ?>%">
                    <strong class="rating"><?php echo sanitize_text_field( $average ); ?></strong>
                </span>
            </div>
            <div class="average-rating-stars__reviews">
                <?php echo '(' . $total . ')'; ?>
            </div>
        </div>
    </div>
<?php endif;