<?php
    new STM_THEME_CHILD_Telegram();

    use Bookit\Classes\Database\Appointments;
    use Bookit\Classes\Database\Services;

    class STM_THEME_CHILD_Telegram
    {
        public function __construct()
        {
            add_filter('stm_wpcfto_boxes', [$this, 'boxes']);

            add_filter('stm_wpcfto_fields', [$this, 'fields']);

            add_action('stm_lms_woocommerce_send_message_approved', [$this, 'order_approved'], 20, 3);

            add_shortcode('lms_send_telegram_statistic_month', [$this, 'statistic_month']);

            add_shortcode('lms_send_telegram_statistic_week', [$this, 'statistic_week']);

            add_action('stm_lms_after_user_register', [$this, 'user_register'], 10, 2);

            add_action('bookit_appointment_created', [$this, 'appointment_created']);

            add_action('stm_lms_progress_updated', [$this, 'progress_updated'], 10, 2);

            add_action('wp', [$this, 'cron_statistic_every_day']);

            add_action( 'every_day_statistic', [$this, 'statistic'] );

            add_action( 'save_post_stm-reviews', [$this, 'add_review'], 10, 3 );
        }

        public function add_review( $post_id, $post, $update )
        {
            if ( ! $update ) {
                $course_id = intval( $_POST['post_id'] );;
                $mark      = ( ! empty( $_POST['mark'] ) ) ? intval( $_POST['mark'] ) : 0;
                $user      = STM_LMS_User::get_current_user();

                /*
                 * Emoji codes getting is https://www.phpclasses.org/browse/file/155930.html
                 * */
                if ( $mark >= 0 && $mark <= 2 ) {
                    $sticker = "\u{1f621}";
                }
                else if ( $mark > 2 && $mark <= 3 ) {
                    $sticker = "\u{1f61e}";
                }
                else{
                    $sticker = "\u{1f44c}";
                }

                $message = sprintf(
                    esc_html__('Created new review %s', 'masterstudy-child') . "\n" .
                    esc_html__('Course name: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Review owner: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Review: %s', 'masterstudy-child'),
                    $sticker,
                    get_the_title( $course_id ),
                    $user['login'],
                    $post->post_content
                );

                $this->send_message( $message );
            }
        }

        public function cron_statistic_every_day()
        {
            if( ! wp_next_scheduled( 'every_day_statistic' ) ) {
                wp_schedule_event( strtotime('17:00:00'), 'daily', 'every_day_statistic');
            }
        }

        public function boxes( $boxes )
        {
            if ( is_array( $boxes ) && ! empty( $boxes ) ) {
                $boxes['stm_telegram_users_settings'] = array(
                    'post_type' => array( 'telegram_subscribers' ),
                    'label'     => esc_html__( 'Users Settings', 'masterstudy-child' ),
                );
            }

            return $boxes;
        }

        public function fields( $fields )
        {
            if ( is_array( $fields ) && ! empty( $fields ) ) {
                $fields['stm_telegram_users_settings'] = array(
                    'section_telegram_settings'      => array(
                        'name'   => esc_html__( 'Settings', 'masterstudy-child' ),
                        'label'  => esc_html__( 'General Settings', 'masterstudy-child' ),
                        'icon'   => 'fa fa-cog',
                        'fields' => array(
                            'send_notification'         => array(
                                'type'  => 'radio',
                                'label' => esc_html__( 'Accept to send notifications', 'masterstudy-child' ),
                                'options' => array(
                                    ''        => esc_html__( 'Pending', 'masterstudy-child' ),
                                    'approve' => esc_html__( 'Approve', 'masterstudy-child' ),
                                    'cancel'  => esc_html__( 'Cancel', 'masterstudy-child' ),
                                )
                            ),
                        ),
                    ),
                );
            }

            return $fields;
        }

        public function order_approved($course_data, $user_id, $order_id)
        {
            if ( ! empty( $order_id ) ) {
                $order = new WC_Order( $order_id );

                if ( ! empty( $order ) ) {
                    $user      = new WP_User( $order->get_user_id() );
                    $course_id = $course_data['item_id'];
                    $course    = get_post( $course_id );

                    if ( ! is_wp_error( $user ) && $user->exists() && ! is_wp_error( $course ) ) {
                        $message = sprintf(
                            esc_html__('Created new order #%d', 'masterstudy-child') . "\n" .
                            esc_html__('Course name: %s', 'masterstudy-child') . "\n" .
                            esc_html__('Course price: %s', 'masterstudy-child'),
                            $order_id,
                            $course->post_title,
                            wc_price( ( $course_data['price'] ?? $order->get_total() ) )
                        );

                        if ( ! empty( $order->get_coupon_codes() ) ) {
                            $message .= "\n" . sprintf(
                                    esc_html__('Coupon codes: %s', 'masterstudy-child'),
                                    implode(', ', $order->get_coupon_codes())
                                );
                        }

                        $this->send_message( $message );
                    }
                }
            }
        }

        public function get_subscribers()
        {
            $args = array(
                'post_type' => 'telegram_subscribers',
                'post_per_page' => -1,
                'nopaging' => true,
                'meta_query' => array(
                    array(
                        'key' => 'send_notification',
                        'value' => 'approve'
                    )
                )
            );

            return new WP_Query( $args );
        }

        public function send_message($message)
        {
            $query = $this->get_subscribers();

            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) : $query->the_post();
                    telegram_sendmessage(
                        get_post_field( 'post_title', get_the_ID() ),
                        $message,
                        false
                    );
                endwhile;
            }
        }

        public function get_orders_info( $date = 'now' )
        {
            $date = wp_date("Y-m-d H:i:s", strtotime( $date ));
            $date_string = "> '$date'";

            global $wpdb;

            return $wpdb->get_row( "
                SELECT DISTINCT count(p.ID) as count, SUM(pm.meta_value) as total_earned FROM {$wpdb->prefix}posts as p
                LEFT JOIN {$wpdb->prefix}postmeta as pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
                WHERE p.post_type = 'shop_order' AND p.post_date {$date_string}
                AND p.post_status IN ('wc-on-hold','wc-processing','wc-completed')
            ", ARRAY_A);
        }

        public function get_users_info( $date = 'today' )
        {
            if ( $date === 'today' ) {
                $args = [
                    'date_query' => [
                        [ 'after'  => 'today', 'inclusive' => true ],
                    ]
                ];
            }
            else if ( $date === 'month' ) {
                $args = [
                    'date_query' => [
                        [
                            'column' => 'user_registered',
                            'after' => array(
                                'year'  => current_time( 'Y' ),
                                'month' => current_time( 'm' )
                            ),
                            'inclusive' => true
                        ],
                    ]
                ];
            }
            else if ( $date === 'week' ) {
                $day = wp_date('W', time());
                $week_start_day = wp_date('d', strtotime('-' . $day . ' days'));
                $week_end = wp_date('d', strtotime('+'.(6 - $day).' days'));
                $args = [
                    'date_query' => [
                        [
                            'column' => 'user_registered',
                            'before' => array(
                                'year'   => current_time( 'Y' ),
                                'month'  => current_time( 'm' ),
                                'day'    => $week_start_day,
                            ),
                            'after'  => array(
                                'year'   => current_time( 'Y' ),
                                'month'  => current_time( 'm' ),
                                'day'    => $week_end,
                            ),
                            'inclusive' => true
                        ],
                    ]
                ];
            }

            if ( ! empty( $args ) ) {
                $args['role'] = 'subscriber';
                $query = new WP_User_Query( $args );

                return $query->total_users ?? 0;
            }

            return 0;
        }

        public function get_popular_course()
        {
            $args = array(
                'post_type' 	 => array( 'product' ),
                'meta_key'  	 => 'total_sales',
                'orderby'   	 => 'meta_value_num',
                'order' 		 => 'desc',
                'posts_per_page' => 1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => 'stm_lms_product_id',
                        'compare' => 'EXISTS'
                    )
                )
            );

            $popular_products = new WP_Query( $args );

            if ( ! empty( $popular_products->posts ) ) {
                return get_the_title( array_shift( $popular_products->posts ) );
            }

            return '';
        }

        public function statistic_week( $atts )
        {
            $atts = shortcode_atts( array(
                'chat_id' => 0
            ), $atts );

            $chat_id = $atts['chat_id'];
            $message = esc_html__('To receive notifications, the site administrator must approve your account in the admin panel', 'masterstudy-child');

            if ( ! $chat_id ) {
                $json    = file_get_contents('php://input');
                $data    = (array)json_decode($json, true);

                if ( $data['message']['chat']['type'] == 'private' ) {
                    $chat_id = $data['message']['from']['id'];
                } else if ( $data['message']['chat']['type'] == 'group' || $data['message']['chat']['type'] == 'supergroup' ) {
                    $chat_id = $data['message']['chat']['id'];
                } else if ( $data['callback_query']['message']['text'] ) {
                    $chat_id = $data['callback_query']['message']['chat']['id'];
                }
            }

            if ( ! $chat_id ) {
                return $message;
            }

            $post = get_page_by_title( $chat_id, OBJECT, 'telegram_subscribers');

            if ( $post ) {
                $chat_id = $post->ID;
            }

            $access = get_post_meta($chat_id, 'send_notification', true);

            if ( $access !== 'approve' ) {
                return $message;
            }

            $message = esc_html__('No orders current week', 'masterstudy-child');

            try {
                $day          = wp_date('w', time());
                $week_start   = wp_date('Y-m-d 00:00:00', strtotime('-' . $day . ' days'));
                $orders_info  = $this->get_orders_info( $week_start );
                $found_orders = $orders_info['count'];
                $total_earned = $orders_info['total_earned'];

                $message = sprintf(
                    esc_html__('From the date: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Count orders: %d', 'masterstudy-child') . "\n" .
                    esc_html__('Total earned: %s', 'masterstudy-child') . "\n" .
                    esc_html__('User count registered: %d', 'masterstudy-child'),
                    wp_date('d F Y', strtotime( $week_start )),
                    $found_orders,
                    wc_price( $total_earned ),
                    $this->get_users_info('week')
                );

                $popular_course = $this->get_popular_course();

                if ( ! empty( $popular_course ) ) {
                    $message .= sprintf( "\n" . esc_html__('Popular course on sale: %s', 'masterstudy-child'),
                        $popular_course
                    );
                }

            } catch (Exception $e) {
                telegram_log('', '', $e->getMessage());
            }

            return $message;
        }

        public function statistic_month( $atts )
        {
            $atts = shortcode_atts( array(
                'chat_id' => 0
            ), $atts );

            $chat_id = $atts['chat_id'];
            $message = esc_html__('To receive notifications, the site administrator must approve your account in the admin panel', 'masterstudy-child');

            if ( ! $chat_id ) {
                $json    = file_get_contents('php://input');
                $data    = (array)json_decode($json, true);

                if ( $data['message']['chat']['type'] == 'private' ) {
                    $chat_id = $data['message']['from']['id'];
                } else if ( $data['message']['chat']['type'] == 'group' || $data['message']['chat']['type'] == 'supergroup' ) {
                    $chat_id = $data['message']['chat']['id'];
                } else if ( $data['callback_query']['message']['text'] ) {
                    $chat_id = $data['callback_query']['message']['chat']['id'];
                }

                $post = get_page_by_title( $chat_id, OBJECT, 'telegram_subscribers');

                if ( $post ) {
                    $chat_id = $post->ID;
                }
            }

            if ( ! $chat_id ) {
                return $message;
            }

            $post = get_page_by_title( $chat_id, OBJECT, 'telegram_subscribers');

            if ( $post ) {
                $chat_id = $post->ID;
            }

            $access = get_post_meta($chat_id, 'send_notification', true);

            if ( $access !== 'approve' ) {
                return $message;
            }

            $message = esc_html__('No orders current month', 'masterstudy-child');

            try {
                $orders_info  = $this->get_orders_info( current_time( 'Y-m-01 00:00:00' ) );
                $found_orders = $orders_info['count'];
                $total_earned = $orders_info['total_earned'];

                $message = sprintf(
                    esc_html__('From the date: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Count orders: %d', 'masterstudy-child') . "\n" .
                    esc_html__('Total earned: %s', 'masterstudy-child') . "\n" .
                    esc_html__('User count registered: %d', 'masterstudy-child'),
                    current_time( '01 F Y' ),
                    $found_orders,
                    wc_price( $total_earned ),
                    $this->get_users_info('month')
                );

                $popular_course = $this->get_popular_course();

                if ( ! empty( $popular_course ) ) {
                    $message .= sprintf( "\n" . esc_html__('Popular course on sale: %s', 'masterstudy-child'),
                        $popular_course
                    );
                }

            } catch (Exception $e) {
                telegram_log('', '', $e->getMessage());
            }

            return $message;
        }

        public function statistic()
        {
            $message = esc_html__('No orders today', 'masterstudy-child');

            try {
                $orders_info  = $this->get_orders_info( current_time( 'Y-m-d 00:00:00' ) );
                $found_orders = $orders_info['count'];
                $total_earned = $orders_info['total_earned'];

                $message = sprintf(
                    esc_html__('Day: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Count orders: %d', 'masterstudy-child') . "\n" .
                    esc_html__('Total earned: %s', 'masterstudy-child') . "\n" .
                    esc_html__('User count registered: %d', 'masterstudy-child'),
                    current_time('d F Y'),
                    $found_orders,
                    wc_price( $total_earned ),
                    $this->get_users_info()
                );
            } catch (Exception $e) {
                telegram_log('', '', $e->getMessage());
            }

            $this->send_message( $message );
        }

        public function user_register(WP_User $user, $data)
        {
            if ( ! is_wp_error( $user ) ) {
                $otp = new STM_THEME_CHILD_OTP;
                $valid_number = $otp->valid_phone( $data['phone'] );
                $date_format  = get_option( 'date_format', 'Y F j' );
                $time_format  = get_option( 'time_format', 'H:i' );

                $message = sprintf(
                    esc_html__('Register new user #%d', 'masterstudy-child') . "\n" .
                    esc_html__('Phone number: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Created date: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Created time: %s', 'masterstudy-child'),
                    $user->ID,
                    $valid_number,
                    wp_date( $date_format ),
                    wp_date( $time_format )
                );

                $this->send_message( $message );
            }
        }

        public function appointment_created($appointment_id)
        {
            if ( $appointment_id ) {
                $appointments = new Appointments;
                $appointment  = $appointments->get('id', $appointment_id);

                $services = new Services;
                $service  = $services->get('id', $appointment->service_id);

                $date_format  = get_option( 'date_format', 'Y F j' );
                $time_format  = get_option( 'time_format', 'H:i' );

                $message = sprintf(
                    esc_html__('New appointment booking #%d', 'masterstudy-child') . "\n" .
                    esc_html__('Course name: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Selected date: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Selected time: %s', 'masterstudy-child'),
                    $appointment->id,
                    $service->title,
                    wp_date( $date_format, $appointment->date_timestamp ),
                    wp_date( $time_format, $appointment->start_time )
                );

                $this->send_message( $message );
            }
        }

        public function progress_updated($course_id, $user_id)
        {
            $total_progress = STM_THEME_CHILD_Curriculum::get_total_progress( $user_id, $course_id );

            if ( ! empty( $total_progress ) && $total_progress['course_completed']  ) {
                $date_format  = get_option( 'date_format', 'Y F j' );
                $time_format  = get_option( 'time_format', 'H:i' );

                $user = STM_LMS_User::get_current_user( $user_id );

                $message = sprintf(
                    esc_html__('Student the course completed #%d', 'masterstudy-child') . "\n" .
                    esc_html__('Course name: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Student name: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Completed date: %s', 'masterstudy-child') . "\n" .
                    esc_html__('Completed time: %s', 'masterstudy-child'),
                    $user_id,
                    get_the_title( $course_id ),
                    $user['login'],
                    wp_date( $date_format ),
                    wp_date( $time_format )
                );

                $this->send_message( $message );
            }
        }
    }