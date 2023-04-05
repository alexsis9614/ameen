<?php
    use Bookit\Classes\Admin\SettingsController;
    use Bookit\Classes\BookitController;
    use Bookit\Classes\Database\Customers;
    use Bookit\Classes\Nonces;
    use Bookit\Classes\Template;
    use Bookit\Helpers\AddonHelper;
    use Bookit\Helpers\TimeSlotHelper;
    use Bookit\Classes\Translations;

    new STM_LMS_Bookit_Sync;

    class STM_LMS_Bookit_Sync extends BookitController
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

            add_filter('stm_lms_menu_items', [$this, 'menu_items']);

            add_shortcode( 'bookit-booking-zoom', [self::class, 'render_shortcode'] );
        }

        /**
         * Shortcode
         * @param $atts
         *
         * @return bool|string
         */
        public static function render_shortcode( $atts ) {
            $atts = shortcode_atts( array(
                'category'  => null,
                'service'   => null,
                'staff'     => null,
                'theme'     => null,
            ), $atts, 'bookit-booking-zoom' );

            return self::calendar( false, $atts['category'], $atts['service'], $atts['staff'], $atts['theme'] );
        }

        /**
         * Render Frontend Bookit Calendar
         *
         * @param bool $display
         * @param bool $is_admin
         * @param null $category_id
         * @param null $service_id
         * @param null $staff_id
         *
         * @return bool|string
         */
        public static function calendar( $display = false, $category_id = null, $service_id = null, $staff_id = null, $theme = null ) {

            $paymentAddon         = AddonHelper::getAddonDataByName(AddonHelper::$paymentAddon);
            $bookitPaymentsActive = $paymentAddon['isCanUse'] ?? false;

            $shortcodeAttributes        = ['category_id' => $category_id, 'service_id' => $service_id, 'staff_id' => $staff_id];
            $base_data = self::get_base_data_by_attributes( $shortcodeAttributes );

            $categories                 = $base_data['categories'];
            $services                   = $base_data['services'];
            $staff                      = $base_data['staff'];
            $settings                   = SettingsController::get_settings();
            $settings['date_format']    = bookit_php_to_moment(get_option('date_format'));
            $settings['time_format']    = bookit_php_to_moment(get_option('time_format'));
            $settings['pro_active']     = bookit_pro_active();
            $settings['payment_active'] = $bookitPaymentsActive ? true : false;
            $user                       = ( is_user_logged_in() ) ? wp_get_current_user() : null;
            $language                   = substr( get_bloginfo( 'language' ), 0, 2 );
            $navigation                 = self::get_step_navigation();

            $time_format    = get_option('time_format');
            $service_start  = 0;
            $service_end    = TimeSlotHelper::DAY_IN_SECONDS;
            $time_slot_list = TimeSlotHelper::getTimeList($service_start, $service_end);

            if ( ! empty( $services ) ) {
                foreach ( $services as $index => &$service ) {
                    if ( ! empty( $user->ID ) ) {
                        $course_id = STM_LMS_Bookit_Service::get_course_id( $service['id'] );
                        $plan = $user->__get('stm_lms_course_plan_' . $course_id);

                        $progress = 0;
                        if ( is_user_logged_in() ) {
                            $my_progress = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( get_current_user_id(), $course_id, array( 'progress_percent' ) ) );
                            if ( ! empty( $my_progress['progress_percent'] ) ) {
                                $progress = (int) $my_progress['progress_percent'];
                            }
                            if ( $progress > 100 ) {
                                $progress = 100;
                            }
                        }

                        if ( $plan !== 'vip' || $progress !== 100 ) {
                            $categories = wp_list_filter( $categories, array('id' => $service['category_id']), 'NOT' );
                            unset( $services[ $index ] );
                            continue;
                        }
                    }

                    if ( is_array( $service ) ) {
                        $service['icon_url'] = ( ! empty( $service['icon_id'] ) ) ? wp_get_attachment_url($service['icon_id']) : null;
                    }
                }
            }

            if ( empty( $services ) ) {
                $categories = array();
            }
            else {
                $services = array_values( $services );
            }

            if ( ! empty( $user->ID ) ) {
                $user = (object) array_merge( (array) $user->data, [ 'customer' => Customers::get('wp_user_id', $user->ID) ] );
            }

            if ( count( $categories ) <= 1 ) {
                $key = array_search('category', array_column($navigation, 'key'));
                array_splice($navigation, $key, 1);
            }

            if ( ! empty( $service_id ) || ( count( $services ) == 1 && ( count( $categories ) == 1 || ! empty( $category_id ) ) ) ) {
                $key = array_search('service', array_column($navigation, 'key'));
                array_splice($navigation, $key, 1);
            }

            self::get_bookit_script_styles( $settings );

            $data = [
                'attributes'    => $shortcodeAttributes,
                'categories'    => $categories,
                'services'      => $services,
                'staff'         => $staff,
                'settings'      => $settings,
                'user'          => $user,
                'language'      => $language,
                'slot_list'     => $time_slot_list,
                'navigation'    => $navigation,
                'theme'         => $theme, // choosen in shortcode theme
                'time_format'  => $time_format,
            ];

            return Template::load_template( 'bookit-calendar', $data, $display );
        }

        private static function get_step_navigation() {
            return [
                [ 'key'  => 'category', 'menu' => __('Category', 'bookit'), 'title' => __('Select Category', 'bookit'), 'requiredFields' => [], 'validation' => false ],
                [ 'key'  => 'service', 'menu' => __('Service', 'bookit'), 'title' => __('Select Service', 'bookit'), 'requiredFields' => ['category_id'], 'validation' => false ],
                [ 'key'  => 'dateTime', 'menu' => __('Date', 'bookit'), 'title' => __('Select Time & Employee', 'bookit'), 'requiredFields' => ['category_id', 'service_id'], 'validation' => false ],
//                [ 'key'  => 'payment', 'menu' => __('Payment', 'bookit'), 'title' => __('Payment', 'bookit'), 'requiredFields' => ['service_id', 'staff_id', 'date_timestamp', 'start_time', 'end_time', 'email', 'full_name' ], 'validation' => false ],
                [ 'key'  => 'confirmation', 'menu' => __('Confirmation', 'bookit'), 'title' => __('Confirmation', 'bookit'), 'requiredFields' => ['service_id', 'staff_id', 'date_timestamp', 'start_time', 'end_time', 'email', 'full_name'], 'validation' => true ],//todo
                [ 'key'  => 'result', 'requiredFields' => [], 'validation' => false ],
            ];
        }

        public static function get_bookit_script_styles( $settings = array() )
        {
            $paymentAddon = AddonHelper::getAddonDataByName(AddonHelper::$paymentAddon);

            if ( empty( $settings ) ) {
                $bookitPaymentsActive = $paymentAddon['isCanUse'] ?? false;

                $settings                   = SettingsController::get_settings();
                $settings['date_format']    = bookit_php_to_moment(get_option('date_format'));
                $settings['time_format']    = bookit_php_to_moment(get_option('time_format'));
                $settings['pro_active']     = bookit_pro_active();
                $settings['payment_active'] = $bookitPaymentsActive ? true : false;
            }

            $styles = BOOKIT_URL . 'assets/dist/frontend/css/app.css';

            if ( $settings['custom_colors_enabled'] == 'true' ) {
                $upload         = wp_upload_dir();
                $styles_path    = $upload['basedir'] . '/bookit/app.css';
                if ( file_exists( $styles_path ) ) {
                    $styles = $upload['baseurl'] . '/bookit/app.css';
                }
            }
            wp_enqueue_script('stm-lms-user-booking', STM_THEME_CHILD_DIRECTORY_URI . '/assets/bookit/dist/frontend/js/app.js', [], STM_THEME_CHILD_VERSION);

            wp_enqueue_style( 'bookit-app', $styles, [], intval( get_option( 'bookit_styles_version', BOOKIT_VERSION ) ));

            $current_user = STM_LMS_User::get_current_user( '', true, true );

            $translations = Translations::get_frontend_translations();

            $translations['service'] = esc_html__('Exam name', 'masterstudy-child');
            $translations['client_name'] = esc_html__('Student name', 'masterstudy-child');

            $ajax_data = [
                'ajax_url'      => admin_url( 'admin-ajax.php' ),
                'translations'  => $translations,
                'nonces'        => Nonces::get_frontend_nonces(),
                'user'          => array(
                    'wp_user_id' => $current_user['id'],
                    'full_name'  => $current_user['login'],
                    'email'      => $current_user['email']
                )
            ];

            wp_localize_script( 'stm-lms-user-booking', 'bookit_window', $ajax_data );
        }

        public function menu_items( $menus )
        {
            if ( ! empty( $menus ) && is_array( $menus ) ) {
                $menus[] = array(
                    'order'        => 130,
                    'id'           => 'booking',
                    'slug'         => 'booking',
                    'lms_template' => 'stm-lms-user-booking',
                    'menu_title'   => esc_html__( 'Product booking', 'masterstudy-child' ),
                    'menu_icon'    => 'fa-book',
                    'menu_url'     => self::menu_url(),
                    'menu_place'   => 'main',
                );
            }

            return $menus;
        }

        public static function menu_url()
        {
            $pages_config = STM_LMS_Page_Router::pages_config();
            $login_url    = STM_LMS_User::login_page_url();

            if ( isset( $pages_config['user_url']['sub_pages']['booking_url'] ) ) {
                $booking_url = $login_url . $pages_config['user_url']['sub_pages']['booking_url']['url'];
            }
            else {
                $booking_url = $login_url;
            }

            return $booking_url;
        }
    }