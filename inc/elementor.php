<?php
    function stm_lms_child_register_widgets( $widgets_manager ) {
        if ( class_exists('Elementor_STM_Testimonials') ) {
            $team_widget = new Elementor_STM_Testimonials();

            $widgets_manager->unregister( $team_widget->get_name() );

            require_once __DIR__ . '/widgets/class-testimonials.php';

            $widgets_manager->register( new Elementor_STM_Child_Testimonials() );
        }
    }
    add_action( 'elementor/widgets/register', 'stm_lms_child_register_widgets', 20 );