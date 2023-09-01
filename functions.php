<?php
    define('STM_THEME_CHILD_VERSION', '1.0.1');
    define('STM_THEME_CHILD_DIRECTORY', get_stylesheet_directory());
    define('STM_THEME_CHILD_DIRECTORY_URI', get_stylesheet_directory_uri());

    require_once __DIR__ . '/inc/enqueue.php';
//    require_once __DIR__ . '/inc/sms/play-mobile-uz.php';
    require_once __DIR__ . '/inc/sms/eskiz-uz.php';
    require_once __DIR__ . '/inc/otp.php';
    require_once __DIR__ . '/inc/elementor.php';
    require_once __DIR__ . '/inc/testimonial.php';

    if ( is_plugin_active('telegram-bot/telegram-bot.php') ) {
        require_once __DIR__ . '/inc/telegram.php';
    }

    if ( class_exists( 'STM_LMS_Curriculum' ) && class_exists( 'STM_LMS_Course' ) ) {
        require_once __DIR__ . '/inc/classes/STM_Settings.php';
        require_once __DIR__ . '/inc/classes/STM_Plans.php';
        require_once __DIR__ . '/inc/classes/STM_Curriculum.php';
        require_once __DIR__ . '/inc/classes/STM_Course.php';
        require_once __DIR__ . '/inc/classes/STM_Student_Progress.php';
        require_once __DIR__ . '/inc/classes/STM_Cart.php';
        require_once __DIR__ . '/inc/classes/STM_Limit_Device.php';

        new LMS\inc\classes\STM_Cart();
        new LMS\inc\classes\STM_Course();
        new LMS\inc\classes\STM_Student_Progress();

        add_action('after_setup_theme', function () {
            $limit_device = new LMS\inc\classes\STM_Limit_Device( 0 );

            $limit_device->db_table_create();
        });
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