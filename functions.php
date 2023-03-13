<?php
    define('STM_THEME_CHILD_VERSION', time());
    define('STM_THEME_CHILD_DIRECTORY', get_stylesheet_directory());
    define('STM_THEME_CHILD_DIRECTORY_URI', get_stylesheet_directory_uri());

    require_once __DIR__ . '/inc/enqueue.php';
    require_once __DIR__ . '/inc/sms/play-mobile-uz.php';
    require_once __DIR__ . '/inc/otp.php';
    require_once __DIR__ . '/inc/elementor.php';
    require_once __DIR__ . '/inc/testimonial.php';

    if ( class_exists( 'STM_LMS_Curriculum' ) ) {
        require_once __DIR__ . '/inc/curriculum.php';
    }

    if ( class_exists('STM_LMS_Cart') ) {
        require_once __DIR__ . '/inc/add-to-cart.php';
        remove_action('template_redirect', 'pmpro_account_redirect');
    }