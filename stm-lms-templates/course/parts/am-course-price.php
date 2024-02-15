<?php
    /**
     * @var $course_id
     * */

    use LMS\inc\classes\STM_Course;

    $not_salebale = get_post_meta( $course_id, 'not_single_sale', true );
    $price        = get_post_meta( $course_id, 'price', true );
    $sale_price   = STM_LMS_Course::get_sale_price( $course_id );
    $percent      = 0;

    if ( ! empty( $sale_price ) ) {
        $percent = (( (float) $price - (float) $sale_price ) / (float) $price) * 100;
    }

    $plans        = new LMS\inc\classes\STM_Plans;
    $plans_enable = $plans->enable( $course_id );

    if ( ! $not_salebale || $plans_enable ) :

        if ( $plans_enable ) {
//            $price = $plans->price( $course_id, 'standard' );
        }
?>
    <div class="stm-lms__price--wrapper">
        <?php if ( empty( $price ) && empty( $sale_price ) ) : ?>
            <div class="stm-lms-course__sidebar--price stm-lms-course__price--free">
                <?php
                    $_courses = STM_Course::get_course_bundle( $course_id );
                    if ( empty( $_courses ) ) {
                        esc_html_e('Free', 'masterstudy-child');
                    } else {
                        esc_html_e('Free Courses', 'masterstudy-child');
                    }
                ?>
            </div>
        <?php elseif ( ! empty( $price ) && ! empty( $sale_price ) ) : ?>
            <del class="stm-lms-course__sidebar--old-price">
                <?php echo STM_LMS_Helpers::display_price( $price ); ?>
            </del>
            <div class="stm-lms-course__sidebar--price">
                <?php
                    echo STM_LMS_Helpers::display_price( $sale_price );

                    if ( ! empty( $percent ) ) :
                ?>
                    <span class="stm-lms-course__sidebar--percent">
                        <?php echo absint( $percent ) . '%'; ?>
                    </span>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="stm-lms-course__sidebar--price">
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