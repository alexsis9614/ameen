<?php
    function stm_lms_child_register_widgets($widgets_manager ) {
        if ( class_exists('Elementor_STM_Testimonials') ) {
            $team_widget = new Elementor_STM_Testimonials();

            $widgets_manager->unregister( $team_widget->get_name() );

            require_once __DIR__ . '/widgets/class-testimonials.php';

            $widgets_manager->register( new Elementor_STM_Child_Testimonials() );
        }

        if ( class_exists( 'Elementor_STM_Stats_Counter' ) ) {
            require_once __DIR__ . '/widgets/stats_counter.php';

            $widgets_manager->register( new LMS\inc\Elementor\Stats_Counter() );
        }

        if ( class_exists( 'StmLmsElementor\Widgets\StmLmsCoursesCarousel' ) ) {
            require_once __DIR__ . '/widgets/courses_carousel.php';

            $widgets_manager->register( new LMS\inc\Elementor\Courses_Carousel() );
        }
    }
    add_action( 'elementor/widgets/register', 'stm_lms_child_register_widgets', 20 );

    add_filter( 'elementor/widget/render_content', function ( $widget_content, \Elementor\Widget_Base $widget ) {
        if ( 'accordion' === $widget->get_name() ) {
            $widget_content = preg_replace(
                '/<a class="elementor-accordion-title" (.*?)>(.*?)<\/a>/',
                '<span class="elementor-accordion-title" $1>$2</span>',
                $widget_content
            );

            $widget->add_style_depends('stm-accordion-style_1');
        }

        return $widget_content;
    }, 10, 2 );