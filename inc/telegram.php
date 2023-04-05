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

            add_action('stm_lms_after_user_register', [$this, 'user_register'], 10, 2);

            add_action('bookit_appointment_created', [$this, 'appointment_created']);

            add_action('stm_lms_progress_updated', [$this, 'progress_updated'], 10, 2);
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

        public function statistic_month()
        {
            $query = new WC_Order_Query( array(
                'limit'   => 10,
                'orderby' => 'date',
                'order'   => 'DESC',
                'return'  => 'ids',
            ) );
            try {
                $orders = $query->get_orders();
            } catch (Exception $e) {
                telegram_log('', '', $e->getMessage());
            }

            return '123';
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