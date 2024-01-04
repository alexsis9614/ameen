<?php
    /**
     * @var $id
     * @var $img_size
     */

    $default_image_size = STM_LMS_Options::get_option( 'courses_image_size', '272x161' );
    $img_size           = ( ! empty( $img_size ) ) ? $img_size : $default_image_size;

    if ( ! empty( $img_container_height ) ) {
        $container_height     = preg_replace( '/[^0-9]/', '', $img_container_height );
        $img_container_height = ( is_admin() ? 'style=height:' : 'data-height=' ) . $container_height . 'px';
    } else {
        $img_container_height = '';
    }

    $lectures = STM_LMS_Course::curriculum_info( $id );
?>

<div class="stm_lms_courses__single--image">

    <a href="<?php the_permalink(); ?>"
       class="heading_font"
       data-preview="<?php esc_attr_e( 'Preview this course', 'masterstudy-lms-learning-management-system' ); ?>">
        <div class="stm_lms_courses__single--image__container" <?php echo esc_attr( $img_container_height ); ?>>
            <?php
                if ( function_exists( 'stm_get_VC_img' ) ) {
                    echo ( stm_lms_lazyload_image( stm_get_VC_img( get_post_thumbnail_id(), $img_size ) ) ); //phpcs:ignore
                }
                else {
                    the_post_thumbnail( $img_size );
                }
            ?>

            <div class="stm_lms_courses__single--info_meta">
                <?php STM_LMS_Templates::show_lms_template( 'courses/parts/meta', compact('lectures' ) ); ?>
            </div>
        </div>
    </a>

</div>
