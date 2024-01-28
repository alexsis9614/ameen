<?php
    namespace LMS\inc\widgets;

    if ( ! defined( 'ABSPATH' ) ) {
        exit; // Exit if accessed directly
    }

    use Elementor\Controls_Manager;
    use Elementor\Widget_Base;
    use ElementorPro\Modules\QueryControl\Module;
    use STM_LMS_Templates;

    class Course_Bundle extends Widget_Base
    {
        public function get_name(): string
        {
            return 'ameen_stm_lms_course_bundle';
        }

        /**
         * Retrieve the widget title.
         *
         * @return string Widget title.
         * @since  1.0.0
         *
         * @access public
         */
        public function get_title(): string
        {
            return __( 'Course Bundle', 'masterstudy-child' );
        }

        /**
         * Retrieve the widget icon.
         *
         * @return string Widget icon.
         * @since  1.0.0
         *
         * @access public
         */
        public function get_icon(): string
        {
            return 'lms-icon';
        }

        /**
         * Retrieve the list of categories the widget belongs to.
         *
         * Used to determine where to display the widget in the editor.
         *
         * Note that currently Elementor supports only one category.
         * When multiple categories passed, Elementor uses the first one.
         *
         * @return array Widget categories.
         * @since  1.0.0
         *
         * @access public
         */
        public function get_categories(): array
        {
            return array( 'ameen_lms' );
        }

        /**
         * Register controls for Elementor.
         *
         * @since 1.0.0
         */
        protected function register_controls()
        {
            $this->start_controls_section(
                'section_content',
                array(
                    'label' => __( 'Content', 'masterstudy-child' ),
                    'type'  => Controls_Manager::TAB_CONTENT
                )
            );

            $this->add_control(
                'course_bundle',
                array(
                    'type'         => Module::QUERY_CONTROL_ID,
                    'label'        => __( 'Course Bundle', 'masterstudy-child' ),
                    'autocomplete' => array(
                        'query'    => array(
                            'post_type' => 'stm-courses'
                        ),
                        'object'    => Module::QUERY_OBJECT_POST,
                        'display'   => 'object'
                    ),
                )
            );

            $this->end_controls_section();
        }

        protected function render()
        {
            $settings = $this->get_settings_for_display();

            STM_LMS_Templates::show_lms_template(
                'shortcodes/stm_lms_ameen_course_bundle',
                $settings
            );
        }
    }