<?php
    new STM_LMS_Child_Cart;

    class STM_LMS_Child_Cart extends STM_LMS_Cart
    {
        public function __construct()
        {
            add_action('wp_ajax_stm_lms_child_add_to_cart', [$this, 'stm_lms_add_to_cart']);

            add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);

            add_action('woocommerce_order_status_completed', [$this, 'order_created']);
            add_action('woocommerce_order_status_pending', array( $this, 'order_cancelled' ));
            add_action('woocommerce_order_status_failed', array( $this, 'order_cancelled' ));
            add_action('woocommerce_order_status_on-hold', array( $this, 'order_cancelled' ));
            add_action('woocommerce_order_status_processing', array( $this, 'order_cancelled' ));
            add_action('woocommerce_order_status_refunded', array( $this, 'order_cancelled' ));
            add_action('woocommerce_order_status_cancelled', array( $this, 'order_cancelled' ));

            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'before_create_order' ), 200, 1 );

//            remove_all_filters('stm_lms_add_to_cart_r', array( 'STM_LMS_API_Sessions', 'add' ) );
//            add_filter('stm_lms_add_to_cart_r', array( $this, 'add_to_cart' ), 10, 2);
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

            update_post_meta( $order_id, 'stm_lms_course_plans', $plans );
        }

        public function order_cancelled( $order_id )
        {
            $order   = new WC_Order( $order_id );
            $user_id = $order->get_user_id();
            $courses = get_post_meta( $order_id, 'stm_lms_course_plans', true );

            foreach ( $courses as $course ) {
                delete_user_meta($user_id, 'stm_lms_course_plan_' . $course['item_id'], strtolower( $course['plan'] ));

                do_action( 'stm_lms_woocommerce_order_cancelled', $course, $user_id );
            }
        }

        public function order_created( $order_id )
        {
            $order   = new WC_Order( $order_id );
            $user_id = $order->get_user_id();
            $courses = get_post_meta( $order_id, 'stm_lms_course_plans', true );

            foreach ( $courses as $course ) {
                if ( get_post_type( $course['item_id'] ) === 'stm-courses' ) {
                    update_user_meta($user_id, 'stm_lms_course_plan_' . $course['item_id'], strtolower( $course['plan'] ));
                }

                do_action( 'stm_lms_woocommerce_order_approved', $course, $user_id );
                do_action( 'stm_lms_woocommerce_send_message_approved', $course, $user_id, $order_id );
            }
        }

        public function cart_item_name($name, $cart_item, $cart_item_key)
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

        public static function _stm_lms_add_to_cart( $item_id, $user_id, $plan ) {

            $r = array();

            $not_salebale = get_post_meta( $item_id, 'not_single_sale', true );
            if ( $not_salebale ) {
                die;
            }

            self::_stm_lms_delete_from_cart( $item_id );

//            $item_meta = STM_LMS_Helpers::parse_meta_field( $item_id );
            $quantity  = 1;
            $price     = LMS\child\classes\STM_Curriculum::plan_price( $item_id, $plan );

            $is_woocommerce = self::woocommerce_checkout_enabled();

            $item_added = count( stm_lms_get_item_in_cart( $user_id, $item_id, array( 'user_cart_id' ) ) );

            if ( ! $item_added ) {
                stm_lms_add_user_cart( compact( 'user_id', 'item_id', 'quantity', 'price' ) );
            }

            if ( ! $is_woocommerce ) {
                $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system' );
                $r['cart_url'] = esc_url( self::checkout_url() );
            } else {
                $r['added']    = $plan ? self::woo_add_to_cart( $item_id, $plan ) : STM_LMS_Woocommerce::add_to_cart( $item_id );
                $r['text']     = esc_html__( 'Go to Cart', 'masterstudy-lms-learning-management-system' );
                $r['cart_url'] = esc_url( wc_get_cart_url() );
            }

            $r['redirect'] = STM_LMS_Options::get_option( 'redirect_after_purchase', false );

            return apply_filters( 'stm_lms_add_to_cart_r', $r, $item_id );
        }

        public function stm_lms_add_to_cart()
        {
            check_ajax_referer( 'stm_lms_add_to_cart', 'nonce' );

            if ( ! is_user_logged_in() || empty( $_GET['item_id'] ) ) {
                die;
            }

            $item_id = intval( $_GET['item_id'] );
            $plan    = strtolower( $_GET['plan'] );
            $user    = STM_LMS_User::get_current_user();
            $user_id = $user['id'];

            $r = self::_stm_lms_add_to_cart( $item_id, $user_id, $plan );

            wp_send_json( $r );
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
            $price                  = LMS\child\classes\STM_Curriculum::plan_price( $id, $plan );
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

        public function add( $response, $item_id )
        {
            if ( STM_LMS_API_Sessions::isFromAppToken() ) {
                $response['cart_url'] = add_query_arg('stm_lms_app_buy', $response['cart_url'], get_home_url());
                $response['lesson_id'] = null;

                /*If we have zero price and user - just add it without next steps*/
                $user_id = get_current_user_id();
                if( ! STM_LMS_Course::get_course_price( $item_id ) && $user_id ) {
                    STM_LMS_Course::add_student( $item_id );
                    LMS\child\classes\STM_Course::add_user_course( $item_id, $user_id, 0, 0 );
                    $response['lesson_id'] = (int) LMS\child\classes\STM_Curriculum::get_first_lesson( $item_id );
                }

            }

            return $response;
        }
    }