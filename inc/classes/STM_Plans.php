<?php
    namespace LMS\inc\classes;

    use STM_LMS_Options;

    class STM_Plans extends STM_Settings
    {
        public $plans;

        public function __construct()
        {

            parent::__construct();

            $this->plans = self::get();

        }

        public static function get()
        {
            return STM_LMS_Options::get_option( self::$_plans_key, array() );
        }

        public function enable( $course_id ): bool
        {
            if ( ! empty( $this->plans ) ) {
                foreach ($this->plans as $plan) {
                    $price = self::price( $course_id, $plan['name'] );
                    if ( ! empty( $price ) ) {
                        return true;
                    }
                }
            }

            return false;
        }

        public static function price_key( $plan ): string
        {
            return 'price_' . strtolower( $plan );
        }

        public static function price( $post_id, $plan )
        {
            return get_post_meta( $post_id, self::price_key( $plan ), true );
        }
    }