<?php
    new STM_LMS_Bookit_Sync;

    class STM_LMS_Bookit_Sync
    {
        public static $taxonomy = 'stm_lms_course_taxonomy';

        public static $post_type = 'stm-courses';

        public static $field_name = 'bookit_sync_id';

        public function __construct()
        {
            require_once __DIR__ . '/classes/Service.php';
            require_once __DIR__ . '/classes/Categories.php';
            require_once __DIR__ . '/classes/User.php';

            add_action('save_post', [STM_LMS_Bookit_Service::class, 'create'], 10, 2);
            add_action('stm_lms_pro_course_added', [STM_LMS_Bookit_Service::class, 'course_added'], 10, 2);
            add_action('before_delete_post', [STM_LMS_Bookit_Service::class, 'delete'], 10, 1);
            add_action('wp_trash_post', [STM_LMS_Bookit_Service::class, 'delete'], 10, 1);

            add_action('create_' . self::$taxonomy, [STM_LMS_Bookit_Categories::class, 'create'], 10, 1);
            add_action('edited_' . self::$taxonomy, [STM_LMS_Bookit_Categories::class, 'create'], 10, 1);
            add_action('pre_delete_term', [STM_LMS_Bookit_Categories::class, 'delete'], 10, 2);

            add_action('user_register', [STM_LMS_Bookit_User::class, 'create'], 10, 1);
            add_action('profile_update', [STM_LMS_Bookit_User::class, 'create'], 10, 1);
            add_action('delete_user', [STM_LMS_Bookit_User::class, 'delete'], 10, 1);
        }
    }