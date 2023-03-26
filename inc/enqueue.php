<?php
    add_action('wp_footer', function () {
        if ( ! is_user_logged_in() ) {
            STM_LMS_Templates::show_lms_template('modals/login');
        }
    });

    function stm_theme_frontend_stylesheets() {

        wp_enqueue_script( 'owl.carousel' );
        wp_enqueue_style( 'owl.carousel' );
        wp_enqueue_script(
            'stm-testimonials',
            STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/testimonials/style_9.js',
            ['jquery'],
            STM_THEME_CHILD_VERSION
        );
        wp_enqueue_style(
            'stm-testimonials',
            STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/testimonials/style_9.css',
            [],
            STM_THEME_CHILD_VERSION
        );

    }
    add_action( 'elementor/frontend/before_enqueue_styles', 'stm_theme_frontend_stylesheets' );