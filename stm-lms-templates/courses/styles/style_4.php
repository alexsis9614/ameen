<?php
    /**
     * @var $has_sale_price
     * @var $id
     * @var $price
     * @var $sale_price
     * @var $author_id
     * @var $style
     * @var $bundle_courses
     */

    $classes = array( $has_sale_price, $style );

    $bundle_course = STM_LMS_Options::get_option('stm_bundle_course');

    if ( $bundle_courses && absint( $bundle_course ) === absint( $id ) ) {
        $classes[] = 'stm_lms_courses__single--list';
    }

    $post_status = STM_LMS_Course::get_post_status( $id );
?>


<div class="stm_lms_courses__single stm_lms_courses__single_animation <?php echo esc_attr( implode( ' ', $classes ) ); ?>">

    <div class="stm_lms_courses__single__inner">

        <div class="stm_lms_courses__single__inner__image">

            <?php
                $image_args = array(
                    'id'       => $id,
                    'img_size' => $image_size ?? '370x200',
                    'img_container_height' => $img_container_height ?? '',
                );

                if ( $bundle_courses && absint( $bundle_course ) === absint( $id ) ) {
                    $image_args['img_size'] = '370x440';
                }

                STM_LMS_Templates::show_lms_template(
                    'courses/parts/image',
                    $image_args
                );
            ?>

        </div>

        <div class="stm_lms_courses__single--inner">

            <?php STM_LMS_Templates::show_lms_template( 'courses/parts/title' ); ?>

            <div class="stm_lms_courses__author">
                <?php
                    $user = STM_LMS_User::get_current_user( $author_id );
                    echo sprintf(
                        esc_html__( 'Created by: %s', 'masterstudy-child' ),
                        $user['login']
                    );
                ?>
            </div>

            <?php STM_LMS_Templates::show_lms_template( 'courses/parts/rating', array( 'id' => $id ) ); ?>

            <?php if ( $bundle_courses && absint( $bundle_course ) === absint( $id ) ) : ?>
                <div class="stm_lms_courses__single--content">
                    <?php echo get_the_excerpt( $id ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $featured ) ) : ?>
                <div class="featured-course-container">
                    <div class="elab_is_featured_product"><?php esc_html_e( 'Featured', 'masterstudy-lms-learning-management-system' ); ?></div>
                </div>
            <?php
                endif;

                if ( ! empty( $post_status ) ) :
            ?>
                <div class="stm_lms_post_status heading_font <?php echo esc_html( sanitize_text_field( $post_status['status'] ) ); ?>">
                    <?php echo esc_html( sanitize_text_field( $post_status['label'] ) ); ?>
                </div>
            <?php endif; ?>

        </div>

    </div>

</div>