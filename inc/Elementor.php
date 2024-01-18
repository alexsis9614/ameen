<?php
    namespace LMS\inc;

    use Elementor\Controls_Manager;
    use Elementor\Widget_Base;
    use Elementor_STM_Testimonials;
    use Elementor_STM_Child_Testimonials;

    class Elementor
    {
        public function __construct()
        {
            add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ), 20 );

            add_filter( 'elementor/widget/render_content', array( $this, 'render_content' ), 10, 2 );

            add_action( 'elementor/widget/before_render_content', array( $this, 'before_render_content' ), 10, 1 );
        }

        public function register_widgets( $widgets_manager )
        {
            if ( class_exists('Elementor_STM_Testimonials') ) {
                $team_widget = new Elementor_STM_Testimonials();

                $widgets_manager->unregister( $team_widget->get_name() );

                require_once __DIR__ . '/widgets/class-testimonials.php';

                $widgets_manager->register( new Elementor_STM_Child_Testimonials() );
            }

            if ( class_exists( 'Elementor_STM_Stats_Counter' ) ) {
                require_once __DIR__ . '/widgets/Stats_Counter.php';

                $widgets_manager->register( new widgets\Stats_Counter() );
            }

            if ( class_exists( 'StmLmsElementor\Widgets\StmLmsCoursesCarousel' ) ) {
                require_once __DIR__ . '/widgets/Courses_Carousel.php';

                $widgets_manager->register( new widgets\Courses_Carousel() );
            }
        }

        public function render_content( $widget_content, Widget_Base $widget )
        {
            if ( 'accordion' === $widget->get_name() ) {
                $this->render_accordion( $widget_content, $widget );
            }
            else if ( 'media-carousel' === $widget->get_name() ) {
                $this->render_media_carousel( $widget );
            }

            return $widget_content;
        }

        public function before_render_content( $widget )
        {
            if ( 'media-carousel' === $widget->get_name() ) {
                wp_enqueue_script('stm-script-testimonials');
            }
        }

        public function render_accordion( &$widget_content, Widget_Base $widget )
        {
            $widget_content = preg_replace(
                '/<a class="elementor-accordion-title" (.*?)>(.*?)<\/a>/',
                '<span class="elementor-accordion-title" $1>$2</span>',
                $widget_content
            );

            $widget->add_style_depends( 'stm-accordion-style_1' );
        }

        public function render_media_carousel( Widget_Base $widget )
        {
            $prefix = 'ameen_';

            $widget->start_controls_section('section_ameen_settings', array(
                'label' => esc_html__( 'Ameen Settings', 'masterstudy-child' ),
            ));

            $widget->add_control(
                $prefix . 'settings_enable',
                array(
                    'type' => Controls_Manager::SWITCHER,
                    'label' => esc_html__( 'Enable settings', 'masterstudy-child' ),
                    'default' => 'yes',
                )
            );

            $widget->end_controls_section();

            if ( $widget->get_settings_for_display( $prefix . 'settings_enable' ) ) {
                $widget->add_style_depends( 'stm-style-testimonials' );
            }
        }
    }