<?php
    define('STM_THEME_CHILD_VERSION', '1.1.0');
    define('STM_THEME_CHILD_DIRECTORY', get_stylesheet_directory());
    define('STM_THEME_CHILD_DIRECTORY_URI', get_stylesheet_directory_uri());

    require_once __DIR__ . '/inc/enqueue.php';
//    require_once __DIR__ . '/inc/sms/play-mobile-uz.php';
    require_once __DIR__ . '/inc/sms/eskiz-uz.php';
    require_once __DIR__ . '/inc/otp.php';
    require_once __DIR__ . '/inc/elementor.php';
    require_once __DIR__ . '/inc/testimonial.php';
    require_once __DIR__ . '/inc/fix-crop-images-svg.php';
    require_once __DIR__ . '/inc/scripts_and_styles.php';

    if ( is_plugin_active('telegram-bot/telegram-bot.php') ) {
        require_once __DIR__ . '/inc/telegram.php';
    }

    if ( class_exists( 'MasterStudy\Lms\Repositories\CurriculumMaterialRepository' ) && class_exists( 'STM_LMS_Course' ) ) {
        require_once __DIR__ . '/inc/classes/STM_Settings.php';
        require_once __DIR__ . '/inc/classes/STM_Plans.php';
        require_once __DIR__ . '/inc/classes/STM_Curriculum.php';
        require_once __DIR__ . '/inc/classes/STM_Course.php';
        require_once __DIR__ . '/inc/classes/STM_Student_Progress.php';
        require_once __DIR__ . '/inc/classes/STM_Cart.php';
        require_once __DIR__ . '/inc/classes/STM_Limit_Device.php';
        require_once __DIR__ . '/inc/classes/STM_Breadcrumb.php';

        new LMS\inc\classes\STM_Cart();
        new LMS\inc\classes\STM_Course();
        new LMS\inc\classes\STM_Breadcrumb();

        if ( class_exists( 'STM_LMS_Quiz' ) ) {
            require_once __DIR__ . '/inc/classes/STM_Quiz.php';

            new LMS\inc\classes\STM_Quiz();
        }

        new LMS\inc\classes\STM_Student_Progress();

        $limit_device = new LMS\inc\classes\STM_Limit_Device( 0 );

        $limit_device->using_hooks();
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

    if ( defined('STM_LMS_API') ) {
        add_filter( 'stm_lms_api_strings_translations', function ( $app_strings ) {
            $new_strings = array(
                'welcome'               => __('Welcome!', 'masterstudy-lms-learning-management-system-api'),
                'phone_number'          => __('Phone number', 'masterstudy-lms-learning-management-system-api'),
                'fill_the_phone_number' => __('Fill the phone number form', 'masterstudy-lms-learning-management-system-api'),
                'code_error'            => __('Code is error', 'masterstudy-lms-learning-management-system-api'),
                'login_to_account'      => __('Please login to your account', 'masterstudy-lms-learning-management-system-api'),
                'forgot_password'       => __('Forgot password?', 'masterstudy-lms-learning-management-system-api'),
                'dont_have_account'     => __('Don\'t have an account?', 'masterstudy-lms-learning-management-system-api'),
                'create_account'        => __('Create a new account', 'masterstudy-lms-learning-management-system-api'),
                'complete_the_form'     => __('Please complete the form to continue', 'masterstudy-lms-learning-management-system-api'),
                'full_name'             => __('Full name', 'masterstudy-lms-learning-management-system-api'),
                'fill_the_form'         => __('Fill the form', 'masterstudy-lms-learning-management-system-api'),
                'do_you_have_account'   => __('Do you have an account?', 'masterstudy-lms-learning-management-system-api'),
                'hello'                 => __('Hello', 'masterstudy-lms-learning-management-system-api'),
                'languages'             => __('Languages', 'masterstudy-lms-learning-management-system-api'),
                'change_language'       => __('Change language', 'masterstudy-lms-learning-management-system-api'),
                'recently_viewed'       => __('Recently Viewed', 'masterstudy-lms-learning-management-system-api'),
                'clear'                 => __('CLEAR', 'masterstudy-lms-learning-management-system-api'),
                'recent_searches'       => __('Recent Searches', 'masterstudy-lms-learning-management-system-api'),
                'view_courses'          => __('View courses', 'masterstudy-lms-learning-management-system-api'),
                'additional'            => __('Additional', 'masterstudy-lms-learning-management-system-api'),
                'user_agreement'        => __('User agreement', 'masterstudy-lms-learning-management-system-api'),
            );

            return wp_parse_args( $new_strings, $app_strings );
        }, 20);
    }