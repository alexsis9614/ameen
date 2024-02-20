<?php
    if ( ! empty( $page_id ) ) {
        $transparent_header = get_post_meta($page_id, 'transparent_header', true);
    }
    $header_margin = stm_option('menu_top_margin');

    if ( empty( $header_margin ) && $header_margin === '' ) {
        $header_margin = 5;
    }

    if ( ! empty( $transparent_header ) ) {
        $header_margin += 4;
    }

    $menu_style    = 'style="margin-top:' . $header_margin . 'px;"';
    $show_wishlist = stm_option('default_show_wishlist', true);
    $show_search   = stm_option('default_show_search', true);
    $show_socials  = stm_option('default_show_socials', true);
    $app_url       = stm_option('header_5_app_url');
?>
<div class="header_main_menu_wrapper" <?php echo sanitize_text_field( $menu_style ); ?>>

    <div class="collapse navbar-collapse">
        <ul class="header-menu">
			<?php
                wp_nav_menu(
                    array(
                        'theme_location' => 'primary',
                        'depth'          => 3,
                        'container'      => false,
                        'menu_class'     => 'header-menu clearfix',
                        'items_wrap'     => '%3$s',
                        'fallback_cb'    => false
                    )
                );
			?>
        </ul>
    </div>

</div>