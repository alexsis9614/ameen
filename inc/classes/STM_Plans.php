<?php
    namespace LMS\inc\classes;

    use STM_LMS_Options;
    use STM_LMS_Templates;

    class STM_Plans extends STM_Settings
    {
        public $plans;

        public function __construct()
        {

            parent::__construct();

            $this->plans = self::get();

        }

        public function get_modal( $course_id )
        {
            $plans_enable = $this->enable( $course_id );

            if ( $plans_enable ) {
                STM_LMS_Templates::show_lms_template( 'modals/plans', array( 'course_id' => $course_id ) );
            }
        }

        public static function display_price( $price ): string
        {
            if ( ! isset( $price ) ) {
                return '';
            }

            $symbol             = '<span class="card-currency">' . STM_LMS_Options::get_option( 'currency_symbol', '$' ) . '</span>';
            $position           = STM_LMS_Options::get_option( 'currency_position', 'left' );
            $currency_thousands = STM_LMS_Options::get_option( 'currency_thousands', ',' );
            $currency_decimals  = STM_LMS_Options::get_option( 'currency_decimals', '.' );
            $decimals_num       = STM_LMS_Options::get_option( 'decimals_num', 2 );

            $price = floatval( $price );

            if ( strpos( $price, '.' ) ) {
                $price = number_format( $price, $decimals_num, $currency_decimals, $currency_thousands );
            } else {
                $price = number_format( $price, 0, '', $currency_thousands );
            }

            if ( 'left' === $position ) {
                return $symbol . $price;
            } else {
                return $price . $symbol;
            }
        }

        public static function get()
        {
            return STM_LMS_Options::get_option( self::$_plans_key, array() );
        }

        public function enable( $course_id ): bool
        {
            if ( ! empty( $this->plans ) ) {
                foreach ( $this->plans as $plan ) {
                    $price = self::price( $course_id, $plan['name'] );
                    if ( ! empty( $price ) ) {
                        return true;
                    }
                }
            }

            return false;
        }

        public static function user_meta_key( $course_id ): string
        {
            return 'stm_lms_course_plan_' . $course_id;
        }

        public static function update_user_meta_key( $user_id, $course_id, $plan )
        {
            return update_user_meta( $user_id, self::user_meta_key( $course_id ), $plan );
        }

        public static function get_user_meta_key( $user_id, $course_id )
        {
            return get_user_meta( $user_id, self::user_meta_key( $course_id ), true );
        }

        public static function delete_user_meta_key($user_id, $course_id, $plan)
        {
            return delete_user_meta( $user_id, self::user_meta_key( $course_id ), self::key( $plan ) );
        }

        public static function curriculum_meta_key( $plan ): string
        {
            return 'course_plan_' . self::key( $plan );
        }

        public static function get_curriculum_meta_key( $post_id, $plan )
        {
            return get_post_meta( $post_id, self::curriculum_meta_key( $plan ), true );
        }

        public static function update_curriculum_meta_key( $post_id, $plan, $value )
        {
            return update_post_meta( $post_id, self::curriculum_meta_key( $plan ), $value );
        }

        public static function key( $plan ): string
        {
            return strtolower( $plan );
        }

        public static function price_key( $plan ): string
        {
            return 'price_' . self::key( $plan );
        }

        public static function price( $post_id, $plan )
        {
            return get_post_meta( $post_id, self::price_key( $plan ), true );
        }

        public function get_plan_price( $post_id )
        {

        }
    }