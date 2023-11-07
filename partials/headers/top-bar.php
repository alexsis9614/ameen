<?php
    global $stm_option;

    $header_top_bar_color = stm_option( 'top_bar_color' );
    $socials              = array_filter( $stm_option[ 'top_bar_use_social' ] );

    if ( empty( $header_top_bar_color ) ) {
        $header_top_bar_color = '#333';
    }
?>
<div class="header_top_bar" style="background-color:<?php echo sanitize_text_field( $header_top_bar_color ); ?>">
    <div class="container">
        <div class="header_top_bar__inner">
			<?php
                if ( stm_option( 'top_bar_wpml' ) ) :
                    get_template_part('partials/headers/parts/wpml_flags');
                endif;

                /* Header Top bar Login */
//                if ( stm_option( 'top_bar_login' ) ) :
//                    get_template_part('partials/headers/parts/woocommerce_login');
//                endif;

                /* Header top bar Socials */
                if ( ! empty( $socials ) && stm_option('top_bar_social') ) :
                    get_template_part('partials/headers/parts/socials');
                endif;

                get_template_part('partials/headers/parts/data_info');
            ?>

            <?php if ( stm_option( 'online_show_links', true ) ) : ?>
                <div class="stm_header_links">
                    <?php get_template_part('partials/headers/parts/links'); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>