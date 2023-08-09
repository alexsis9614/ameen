<?php
    namespace LMS\inc\classes;

    use STM_LMS_Cart;
    use STM_LMS_Helpers;
    use STM_LMS_Woocommerce;
    use WC_Order;
    use STM_LMS_Curriculum;
    use STM_LMS_Options;
    use STM_LMS_User;

    class STM_Cart extends STM_LMS_Cart
    {
        public $prefix   = 'woocommerce';
        public $statuses = array();
        public static $plan;
        public static $order_key_plans = 'stm_lms_course_plans';

        public function __construct()
        {
            $this->statuses = array(
                'created'   => array(
                    'completed' => array( $this, 'order_created' )
                ),
                'cancelled' => array(
                    'pending'       => array( $this, 'order_cancelled' ),
                    'failed'        => array( $this, 'order_cancelled' ),
                    'on-hold'       => array( $this, 'order_cancelled' ),
                    'processing'    => array( $this, 'order_cancelled' ),
                    'refunded'      => array( $this, 'order_cancelled' ),
                    'cancelled'     => array( $this, 'order_cancelled' ),
                )
            );

            remove_action('template_redirect', 'pmpro_account_redirect');

            add_action( 'wp_ajax_stm_lms_child_add_to_cart', array( $this, 'add_to_cart' ) );

            add_filter( $this->prefix . '_cart_item_name', array( $this, 'cart_item_name' ), 10, 2 );

            foreach ($this->statuses as $items) {
                foreach ( $items as $status => $callback ) {
                    add_action( $this->prefix . '_order_status_' . $status, $callback, 5 );
                }
            }

            add_action( $this->prefix . '_checkout_update_order_meta', array( $this, 'before_create_order' ), 200, 1 );

            add_action( 'stm_lms_woocommerce_order_approved', array( $this, 'order_approved' ), 30, 2 );
        }

        public function order_approved( $course, $user_id )
        {
            if ( get_post_type( $course['item_id'] ) === STM_LMS_Curriculum::$courses_slug ) {
                $user_course = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $user_id, $course['item_id'] ) );
                $end_time    = STM_Course::get_end_time( $course['item_id'], $user_id );

                if ( ! empty( $user_course ) && ! empty( $end_time ) ) {
                    stm_lms_update_user_course_endtime( $user_course['user_course_id'], $end_time );
                }
            }
        }

        public static function get_plans( $order_id )
        {
            return get_post_meta( $order_id, self::$order_key_plans, true );
        }

        public static function update_plans( $order_id, $plans )
        {
            return update_post_meta( $order_id, self::$order_key_plans, $plans );
        }

        public function before_create_order( $order_id )
        {
            $cart  = WC()->cart->get_cart();
            $plans = array();

            foreach ( $cart as $cart_item ) {
                $course_id = get_post_meta( $cart_item['product_id'], STM_LMS_Woocommerce::$product_meta_name, true );

                if ( empty( $course_id ) || empty( $cart_item['plan'] ) ) {
                    continue;
                }

                $plans[] = apply_filters(
                    'stm_lms_before_create_order_plan',
                    array(
                        'item_id'  => $course_id,
                        'plan'     => $cart_item['plan']
                    ),
                    $cart_item
                );
            }

            self::update_plans( $order_id, $plans );
        }

        public function order_cancelled( $order_id )
        {
            $order   = new WC_Order( $order_id );
            $user_id = $order->get_user_id();
            $courses = self::get_plans( $order_id );

            foreach ( $courses as $course ) {
                STM_Plans::delete_user_meta_key( $user_id, $course['item_id'], $course['plan'] );

                do_action( 'stm_lms_woocommerce_order_cancelled', $course, $user_id );
            }
        }

        public function order_created( $order_id )
        {
            $order   = new WC_Order( $order_id );
            $user_id = $order->get_user_id();
            $courses = self::get_plans( $order_id );

            foreach ( $courses as $course ) {
                if ( get_post_type( $course['item_id'] ) === STM_LMS_Curriculum::$courses_slug ) {
                    STM_Plans::update_user_meta_key( $user_id, $course['item_id'], $course['plan'] );
                }

//                do_action( 'stm_lms_woocommerce_order_approved', $course, $user_id );
                do_action( 'stm_lms_woocommerce_send_message_approved', $course, $user_id, $order_id );
            }
        }

        public function cart_item_name($name, $cart_item)
        {
            if ( isset( $cart_item['plan'] ) && ! empty( $cart_item['plan'] ) ) {
                $name .= ' - <strong>' . ucfirst( $cart_item['plan'] ) . '</strong>';
            }

            return $name;
        }

        public static function _stm_lms_delete_from_cart( $item_id )
        {
            if ( empty( $item_id ) ) return;

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $product_id = get_post_meta( $item_id, STM_LMS_Woocommerce::$product_meta_name, true );
                if ( $cart_item['product_id'] == $product_id ) {
                    WC()->cart->remove_cart_item( $cart_item_key );
                }
            }
        }

        public static function add_to_cart() {
            check_ajax_referer( 'stm_lms_add_to_cart', 'nonce' );

            if ( ! is_user_logged_in() || empty( $_GET['item_id'] ) ) {
                die;
            }

            $item_id = intval( $_GET['item_id'] );
            $user    = STM_LMS_User::get_current_user();
            $user_id = $user['id'];

            $r = self::_add_to_cart( $item_id, $user_id );

            wp_send_json( $r );
        }

        public static function _add_to_cart( $item_id, $user_id ) {

            $response   = array();
            self::$plan = STM_Plans::key( $_GET['plan'] );

            $not_salebale = get_post_meta( $item_id, 'not_single_sale', true );
            if ( $not_salebale ) {
                die;
            }

            self::_stm_lms_delete_from_cart( $item_id );

            $quantity  = 1;
            $price     = STM_Plans::price( $item_id, self::$plan );

            $is_woocommerce = self::woocommerce_checkout_enabled();

            $item_added = count( stm_lms_get_item_in_cart( $user_id, $item_id, array( 'user_cart_id' ) ) );

            if ( ! $item_added ) {
                stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price' ) );
            }

            if ( ! $is_woocommerce ) {
                $response['text']     = esc_html__( 'Go to Cart', 'masterstudy-child' );
                $response['cart_url'] = esc_url( self::checkout_url() );
            } else {
                $response['added']    = self::$plan ? self::woo_add_to_cart( $item_id, self::$plan ) : STM_LMS_Woocommerce::add_to_cart( $item_id );
                $response['text']     = esc_html__( 'Go to Cart', 'masterstudy-child' );
                $response['cart_url'] = esc_url( wc_get_cart_url() );
            }

            $response['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );

            return apply_filters( 'stm_lms_add_to_cart_r', $response, $item_id );
        }

        public static function woo_add_to_cart( $item_id, $plan ) {
            $product_id = self::woo_create_product( $item_id, $plan );

            // Load cart functions which are loaded only on the front-end.
            include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
            include_once WC_ABSPATH . 'includes/class-wc-cart.php';

            if ( is_null( WC()->cart ) ) {
                wc_load_cart();
            }

            return WC()->cart->add_to_cart( $product_id, 1, 0, array(), array(
                'plan' => $plan
            ) );
        }

        public static function woo_create_product( $id, $plan ) {
            $product_id = STM_LMS_Woocommerce::has_product( $id );

            $title                  = get_the_title( $id );
            $price                  = STM_Curriculum::price( $id, $plan );
            $sale_price             = '';
            $sale_price_dates_start = '';
            $sale_price_dates_end   = '';
            if ( empty( $price ) ) {
                $price                  = get_post_meta( $id, 'price', true );
                $sale_price             = get_post_meta( $id, 'sale_price', true );
                $sale_price_dates_start = get_post_meta( $id, 'sale_price_dates_start', true );
                $sale_price_dates_end   = get_post_meta( $id, 'sale_price_dates_end', true );
            }
            $thumbnail_id           = get_post_thumbnail_id( $id );
            $now                    = time() * 1000;
            $bundle_price           = ( class_exists( 'STM_LMS_Course_Bundle' ) ) ? STM_LMS_Course_Bundle::get_bundle_price( $id ) : null;
            $bundle_price           = ( $bundle_price <= 0 ) ? null : $bundle_price;

            $product = array(
                'post_title'  => $title,
                'post_type'   => 'product',
                'post_status' => 'publish',
            );

            if ( $product_id ) {
                $product['ID'] = $product_id;
            }

            $product_id = wp_insert_post( $product );

            wp_set_object_terms(
                $product_id,
                array( 'exclude-from-catalog', 'exclude-from-search' ),
                'product_visibility'
            );

            if ( isset( $sale_price_dates_start ) && isset( $sale_price_dates_end ) ) {
                if ( empty( $sale_price_dates_start ) || 'NaN' === $sale_price_dates_start ) {
                    $price = ( ! empty( $sale_price ) ) ? $sale_price : $price;

                    delete_post_meta( $product_id, '_sale_price_dates_from' );
                    delete_post_meta( $product_id, '_sale_price_dates_to' );
                } else {
                    $price = ( $now > $sale_price_dates_start && $now < $sale_price_dates_end ) ? $sale_price : $price;

                    update_post_meta(
                        $product_id,
                        '_sale_price_dates_from',
                        gmdate( 'Y-m-d', ( $sale_price_dates_start / 1000 ) + 24 * 60 * 60 )
                    );
                    update_post_meta(
                        $product_id,
                        '_sale_price_dates_to',
                        gmdate( 'Y-m-d', ( $sale_price_dates_end / 1000 ) + 24 * 60 * 60 )
                    );
                }
            }

            if ( isset( $price ) ) {
                update_post_meta( $product_id, '_regular_price', $price );
            }

            if ( isset( $sale_price ) ) {
                update_post_meta( $product_id, '_sale_price', $sale_price );
            }

            if ( isset( $price ) ) {
                update_post_meta( $product_id, '_price', $price );
            }

            if ( isset( $bundle_price ) ) {
                update_post_meta( $product_id, 'stm_lms_bundle_price', $bundle_price );
                update_post_meta( $product_id, '_regular_price', $bundle_price );
                update_post_meta( $product_id, '_price', $bundle_price );
            }

            if ( isset( $thumbnail_id ) ) {
                set_post_thumbnail( $product_id, $thumbnail_id );
            }

            wp_set_object_terms( $product_id, 'stm_lms_product', 'product_type' );

            update_post_meta( $id, STM_LMS_Woocommerce::$product_meta_name, $product_id );
            update_post_meta( $product_id, STM_LMS_Woocommerce::$product_meta_name, $id );
            update_post_meta( $product_id, '_sold_individually', 1 );
            update_post_meta( $product_id, '_virtual', 1 );
            update_post_meta( $product_id, '_downloadable', 1 );

            return $product_id;
        }
    }