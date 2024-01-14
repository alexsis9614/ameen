<?php
    add_action( 'init', function () {
        add_action( 'wp_enqueue_scripts', function () {
            $header_style = stm_option( 'header_style', 'header_default' );

            if ( 'header_2' === $header_style ) {
                wp_dequeue_style( 'stm-headers-header_2' );
                wp_deregister_script( 'stm-headers-header_2' );

                $assets_uri = STM_THEME_CHILD_DIRECTORY_URI . '/assets/';

                wp_enqueue_style( 'stm-header_2-style', $assets_uri . '/css/header_2.css', array('stm_theme_style'), STM_THEME_CHILD_VERSION );
            }

            wp_register_script('buy-plans', STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/buy-plans.js', array(), STM_THEME_CHILD_VERSION);
            wp_register_style('buy-plans', STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/buy-plans.css', array(), STM_THEME_CHILD_VERSION);

            wp_dequeue_style( 'stm-stats_counter-style_1' );
            wp_register_style('stm-stats_counter-style_1', get_template_directory_uri() . '/assets/css/vc_modules/stats_counter/style_1.css', array(), STM_THEME_CHILD_VERSION);

            if ( ! wp_is_mobile() ) {
                wp_register_script('stm-stats_counter', STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/stm_stats_counter.js', array(), STM_THEME_CHILD_VERSION);
            }

//            wp_dequeue_style('stm-lms-courses');

            wp_register_style('stm-courses_carousel-style_4', STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/courses_carousel/style_4.css', array(), STM_THEME_CHILD_VERSION);

            wp_dequeue_style('elementor-accordion');
            wp_register_style('stm-accordion-style_1', STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/accordion.css', array(), STM_THEME_CHILD_VERSION);

            wp_register_style('stm-style-testimonials', STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/testimonials.css', array(), STM_THEME_CHILD_VERSION );

//            wp_dequeue_style('stm-lms-login');

        }, 99);
    }, 99);

    function change_my_script( $tag, $handle, $src ) {

        if ( 'my-script' === $handle ) {
            // return str_replace( ' src', ' async src', $tag );
            return str_replace( ' src', ' defer src', $tag );
        }

        return $tag;
    }