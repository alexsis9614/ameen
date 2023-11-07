<!-- Breads -->
<?php
    $post_id            = get_the_ID();
    $header_style       = stm_option( 'header_style', 'header_default' );
    $transparent_header = get_post_meta( $post_id, 'transparent_header', true );

    if ( function_exists( 'bcn_display' ) ) :
?>
    <div class="stm_lms_breadcrumbs stm_lms_breadcrumbs__<?php echo esc_attr( $header_style ); ?>">
        <div class="stm_breadcrumbs_unit">
            <div class="navxtBreads">
                <?php bcn_display(); ?>
            </div>
        </div>
    </div>
<?php endif; ?>