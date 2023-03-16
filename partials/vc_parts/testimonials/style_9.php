<?php
    /**
     * @var $style
     * @var $testimonials_max_num
     * @var $testimonials_title
     * @var $testimonials_text_color
     * @var $testimonials_slides_per_row
     * */

    $testimonials = new WP_Query(
        array(
            'post_type'      => 'testimonial',
            'posts_per_page' => $testimonials_max_num,
        )
    );

    $slide_col = $testimonials_slides_per_row;
    $slide_col = 3;

    wp_enqueue_script( 'owl.carousel' );
    wp_enqueue_style( 'owl.carousel' );
    wp_enqueue_script(
        'stm-testimonials',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/testimonials/style_9.js',
        ['jquery'],
        STM_THEME_CHILD_VERSION
    );
    wp_enqueue_style(
        'stm-testimonials',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/testimonials/style_9.css',
        [],
        STM_THEME_CHILD_VERSION
    );

    add_action( 'wp_footer', 'stm_modal_testimonials', 20 );
?>

<?php
    if ( $testimonials->have_posts() ) :
        $testimonials_data = array();
        while ( $testimonials->have_posts() ) :
            $testimonials->the_post();
            $testimonials_data[] = array(
                'title'      => get_the_title(),
                'excerpt'    => get_the_excerpt(),
                'video_type' => get_post_meta( get_the_ID(), 'testimonial_video_type', true ),
                'video'      => get_post_meta( get_the_ID(), 'testimonial_video_url', true ),
                'user'       => get_post_meta( get_the_ID(), 'testimonial_user', true ),
                'image'      => stm_get_VC_attachment_img_safe( get_post_thumbnail_id(), '700x400', 'thumbnail', true ),
            );
        endwhile;
?>

    <div class="testimonials_main_wrapper simple_carousel_wrapper">
        <div class="stm_testimonials_wrapper_<?php echo $style; ?>" data-slides="<?php echo intval( $slide_col ); ?>">
            <?php
                foreach ( $testimonials_data as $testimonial ) :
                    $attr = $video_idx = '';
                    if ( 'youtube' === $testimonial['video_type'] ) {
                        $video_idx = ms_plugin_get_youtube_id( $testimonial['video'] );
                        $attr     .= 'data-id="' . esc_attr( $video_idx ) . '"';
                    }
                    else if ( 'vimeo' === $testimonial['video_type'] ) {
                        $video_idx = ms_plugin_get_vimeo_id( $testimonial['video'] );
                        $attr     .= 'data-id="' . esc_attr( $video_idx ) . '"';
                    }
            ?>
                <div
                    id="video-<?php echo esc_attr( $video_idx ); ?>"
                    class="stm_testimonials_single stm_testimonials_video_single"
                    <?php echo $attr; ?>
                >

                    <div class="testimonials_image">
                        <img src="<?php echo esc_url( $testimonial['image'] ); ?>" title="<?php echo esc_attr( $testimonial['title'] ); ?>"  alt="<?php echo esc_attr( $testimonial['title'] ); ?>"/>
                        <span class="testimonials-play"
                              data-toggle="modal"
                              data-type="<?php echo esc_attr( $testimonial['video_type'] ); ?>"
                              data-src="https://www.youtube.com/embed/NFWSFbqL0A0"
                              data-target="#review-video-modal"
                              <?php echo $attr; ?>>
                            <svg fill="#f2b91e" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                 viewBox="0 0 297 297" xml:space="preserve">
                                <path d="M148.5,0C66.486,0,0,66.486,0,148.5S66.486,297,148.5,297S297,230.514,297,148.5S230.514,0,148.5,0z M202.79,161.734
                                    l-78.501,45.322c-2.421,1.398-5.326,2.138-8.083,2.138c-8.752,0-16.039-7.12-16.039-15.872v-90.645
                                    c0-8.752,7.287-15.872,16.039-15.872c2.758,0,5.579,0.739,8.001,2.138l78.542,45.322c4.966,2.867,7.951,8.001,7.951,13.734
                                    S207.756,158.867,202.79,161.734z"/>
                            </svg>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php wp_reset_postdata(); ?>

<?php endif; ?>