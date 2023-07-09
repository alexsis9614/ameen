<?php
    define('STM_THEME_CHILD_VERSION', time());
    define('STM_THEME_CHILD_DIRECTORY', get_stylesheet_directory());
    define('STM_THEME_CHILD_DIRECTORY_URI', get_stylesheet_directory_uri());

    add_action( 'after_setup_theme', function () {
        load_theme_textdomain( 'masterstudy-child', STM_THEME_CHILD_DIRECTORY . '/languages' );
    });

    require_once __DIR__ . '/inc/enqueue.php';
//    require_once __DIR__ . '/inc/sms/play-mobile-uz.php';
    require_once __DIR__ . '/inc/sms/eskiz-uz.php';
    require_once __DIR__ . '/inc/otp.php';
    require_once __DIR__ . '/inc/elementor.php';
    require_once __DIR__ . '/inc/testimonial.php';

//    if ( ! function_exists('STM_LMS_Point_System_Settings') ) {
//        require_once __DIR__ . '/inc/referral-program.php';
//    }

    if ( class_exists( 'STM_LMS_Curriculum' ) || file_exists( STM_LMS_PATH . '/settings/curriculum/main.php' ) ) {
        require_once __DIR__ . '/inc/curriculum.php';

        if ( class_exists('STM_LMS_Cart') ) {
            require_once __DIR__ . '/inc/add-to-cart.php';
            remove_action('template_redirect', 'pmpro_account_redirect');
        }

        if ( is_plugin_active('telegram-bot/telegram-bot.php') ) {
            require_once __DIR__ . '/inc/telegram.php';
        }
    }

    if ( class_exists( 'STM_LMS_BuddyPress' ) ) {
        remove_action('stm_lms_before_user_header', array(STM_LMS_BuddyPress::class, 'before_user_header'));
    }

    if ( defined('BOOKIT_VERSION') ) {
        require_once __DIR__ . '/inc/bookit/main.php';
    }

    if ( class_exists( 'STM_LMS_Course' ) ) {
        remove_action('stm_lms_archive_card_price', 'STM_LMS_Course::archive_card_price');
    }

    if ( class_exists( 'Woocommerce' ) ) {
        require_once __DIR__ . '/inc/woocommerce.php';
    }