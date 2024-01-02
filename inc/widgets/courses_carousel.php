<?php
    namespace LMS\inc\Elementor;

    use Elementor\Plugin;
    use StmLmsElementor\Widgets\StmLmsCoursesCarousel;
    use STM_LMS_Templates;
    use Elementor\Controls_Manager;

    class Courses_Carousel extends StmLmsCoursesCarousel
    {
        public function get_style_depends(): array
        {
            return array( 'owl.carousel', 'stm-courses_carousel-style_4' );
        }

        public function get_script_depends(): array
        {
            return array( 'imagesloaded', 'owl.carousel', 'stm-courses_carousel', 'stm-image_container-card_image' );
        }

        public function get_name(): string
        {
            return 'ameen_' . parent::get_name();
        }

        public function get_title(): string
        {
            return esc_html__( 'Ameen Courses Carousel', 'masterstudy-elementor-widgets' );
        }

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
            parent::register_controls();

            $card_style = $this->get_controls('course_card_style');

            $card_style['options']['style_4'] = __( 'Ameen Card', 'masterstudy-lms-learning-management-system' );

            $this->update_control(  'course_card_style', $card_style );
        }

        protected function render()
        {
            $settings     = $this->get_settings_for_display();
            $atts         = array(
                'css'                       => '',
                'title_color'               => ! empty( $settings['title_color'] ) ? $settings['title_color'] : '',
                'title'                     => ! empty( $settings['title'] ) ? $settings['title'] : '',
                'query'                     => ! empty( $settings['query'] ) ? $settings['query'] : 'none',
                'prev_next'                 => ! empty( $settings['prev_next'] ) ? $settings['prev_next'] : 'enable',
                'view_all_btn_hide_control' => ! empty( $settings['view_all_btn_hide_control'] ) ? $settings['view_all_btn_hide_control'] : 'enable',
                'prev_next_style'           => ! empty( $settings['prev_next_style'] ) ? $settings['prev_next_style'] : 'style_1',
                'per_row'                   => ! empty( $settings['per_row'] ) ? $settings['per_row'] : 6,
                'posts_per_page'            => ! empty( $settings['posts_per_page'] ) ? $settings['posts_per_page'] : 12,
                'pagination'                => ! empty( $settings['pagination'] ) ? $settings['pagination'] : 'disable',
                'taxonomy'                  => ! empty( $settings['taxonomy'] ) && is_array( $settings['taxonomy'] ) ? implode( ',', $settings['taxonomy'] ) : array(),
                'taxonomy_default'          => ! empty( $settings['taxonomy_default'] ) && is_array( $settings['taxonomy_default'] ) ? implode( ',', $settings['taxonomy_default'] ) : array(),
                'image_size'                => ! empty( $settings['image_size'] ) ? $settings['image_size'] : '',
                'show_categories'           => ! empty( $settings['show_categories'] ) ? $settings['show_categories'] : 'disable',
                'course_card_style'         => ! empty( $settings['course_card_style'] ) ? $settings['course_card_style'] : 'style_1',
                'img_container_height'      => ! empty( $settings['img_container_height'] ) ? $settings['img_container_height'] : '',
            );
            $uniq         = stm_lms_create_unique_id( $atts );
            $atts['uniq'] = $uniq;
            if ( Plugin::$instance->editor->is_edit_mode() ) {
                $this->add_courses_widget_overlay();
            }
            STM_LMS_Templates::show_lms_template( 'shortcodes/stm_lms_ameen_courses_carousel', $atts );
        }
    }