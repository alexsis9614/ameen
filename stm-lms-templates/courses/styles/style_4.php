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

$level    = get_post_meta( $id, 'level', true );
$duration = get_post_meta( $id, 'duration_info', true );
$lectures = STM_LMS_Course::curriculum_info( $id );
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
            STM_LMS_Templates::show_lms_template(
                'global/wish-list',
                array(
                    'course_id' => $id,
                )
            );
            ?>

        </div>

        <div class="stm_lms_courses__single--inner">

            <?php
            STM_LMS_Templates::show_lms_template( 'courses/parts/title' );

            if ( $bundle_courses && absint( $bundle_course ) === absint( $id ) ) :
                ?>
                <div class="stm_lms_courses__single--content">
                    <?php echo get_the_excerpt( $id ); ?>
                </div>
            <?php endif; ?>

            <div class="stm_lms_courses__single--info_meta">
                <?php STM_LMS_Templates::show_lms_template( 'courses/parts/meta', compact( 'level', 'duration', 'lectures' ) ); ?>
            </div>

            <div class="stm_lms_courses__single--info_meta <?php echo ( $bundle_courses && absint( $bundle_course ) === absint( $id ) ) ? 'stm_lms_courses__single--info_meta__bottom' : '' ?>">
                <?php
                do_action(
                    'stm_lms_archive_card_price',
                    compact(
                        'price',
                        'sale_price',
                        'id'
                    )
                );
                ?>
                <a href="<?php the_permalink(); ?>"  class="button"><?php echo esc_html__( 'Preview', 'masterstudy-lms-learning-management-system' ); ?></a>
            </div>

        </div>

    </div>

</div>