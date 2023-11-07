<?php
    if (function_exists('icl_get_languages')):
        $langs = icl_get_languages('orderby=id&order=asc');
    else :
        $langs = array();
    endif;

    $langs = apply_filters('stm_lms_wpml_switcher', $langs);
?>

<?php if ( ! empty( $langs ) ) : ?>
    <div class="language-switcher-unit">

		<?php foreach ( $langs as $lang ) : ?>

            <div class="stm_lang">
                <a href="<?php echo esc_url( $lang[ 'url' ] ); ?>">
                    <img src="<?php echo esc_url( $lang[ 'country_flag_url' ] ) ?>" alt="" />
                </a>
            </div>

		<?php endforeach; ?>

    </div>
<?php else: ?>
    <ul class="top_bar_info clearfix">
        <li class="hidden-info">
            <div class="stm_lang">
                <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/uz_flag.png' ) ?>"/>
                <?php esc_html_e('English', 'masterstudy'); ?>
            </div>
        </li>
    </ul>
<?php endif;