<?php
    namespace LMS\child\classes;

    use STM_LMS_Options;

    class STM_Plans extends STM_Settings
    {
        public $plans;

        public function __construct()
        {

            parent::__construct();

            $this->plans = $this->get();

        }

        public function get()
        {
            return STM_LMS_Options::get_option( $this->_plans_key, array() );
        }
    }