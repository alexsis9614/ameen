<?php
    /**
     * @var $style
     * @var $testimonials_max_num
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
    wp_enqueue_style(
            'video.js',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/video-js.min.css',
            null,
        STM_THEME_CHILD_VERSION,
            'all'
    );
    wp_enqueue_script(
        'video.min.js',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/video.min.js',
        array('jquery'),
        STM_THEME_CHILD_VERSION,
        true
    );
    wp_enqueue_script(
        'testimonials-video.js',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/init-video.js',
        array('jquery', 'video.js'),
        STM_THEME_CHILD_VERSION,
        true
    );
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
                'image'      => stm_get_VC_attachment_img_safe( get_post_thumbnail_id(), '100x100', 'thumbnail', true ),
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

//                    $video_idx = 'youtube' === $testimonial['video_type'] ? ms_plugin_get_youtube_id( $testimonial['video'] ) : ms_plugin_get_vimeo_id( $testimonial['video'] );
//                    $youtube   = 'https://www.youtube.com/embed/' . $video_idx . '?&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1';
//                    $vimeo     = 'https://player.vimeo.com/video/' . $video_idx . '?loop=false&amp;byline=false&amp;portrait=false&amp;title=false&amp;speed=true&amp;transparent=0&amp;gesture=media';
            ?>
                <div
                    id="video-<?php echo esc_attr( $video_idx ); ?>"
                    class="stm_testimonials_single stm_testimonials_video_single"
                    <?php echo $attr; ?>
                    data-type="<?php echo esc_attr( $testimonial['video_type'] ); ?>"
                >

                    <div class="testimonials_image">
                        <img src="<?php echo esc_url( $testimonial['image'] ); ?>" title="<?php echo esc_attr( $testimonial['title'] ); ?>"  alt="<?php echo esc_attr( $testimonial['title'] ); ?>"/>
                    </div>

                    <iframe allowfullscreen webkitallowfullscreen
                            mozallowfullscreen></iframe>

                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <?php wp_reset_postdata(); ?>

<?php endif; ?>