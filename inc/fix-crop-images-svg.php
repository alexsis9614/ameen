<?php
    add_action( 'init', function () {
        remove_filter('wp_get_attachment_image_src', 'stm_get_thumbnail_filter', 100);

        add_filter( 'wp_get_attachment_image_src', function ( $image, $attachment_id, $size = 'thumbnail', $icon = false ) {
            $file = get_attached_file( $attachment_id );
            if ( ! $file ) {
                return false;
            }

            $type = stm_mime_content_type( $file );

            if ( ! in_array( $type, array( 'image/svg+xml', 'image/svg' ) ) ) {
                return stm_get_thumbnail( $attachment_id, $size, $icon = false );
            }

            return $image;
        }, 100, 4);
    });