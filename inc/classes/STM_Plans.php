<?php
    namespace LMS\child\classes;

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
    }