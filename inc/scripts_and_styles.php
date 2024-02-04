<?php
    use Elementor\Plugin;

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

            wp_dequeue_style(  'stm-stats_counter-style_1' );
            wp_register_style( 'stm-stats_counter-style_1', get_template_directory_uri() . '/assets/css/vc_modules/stats_counter/style_1.css', array(), STM_THEME_CHILD_VERSION );

            if ( ! wp_is_mobile() ) {
                wp_register_script( 'stm-stats_counter', STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/stm_stats_counter.js', array(), STM_THEME_CHILD_VERSION );
            }

//            wp_dequeue_style( 'stm-lms-courses' );

            wp_register_style( 'stm-courses_carousel-style_4', STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/courses_carousel/style_4.css', array(), STM_THEME_CHILD_VERSION );

            wp_dequeue_style( 'elementor-accordion' );
            wp_register_style( 'stm-accordion-style_1', STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/accordion.css', array( 'elementor-frontend' ), STM_THEME_CHILD_VERSION );

            wp_register_style( 'stm-style-testimonials', STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/testimonials.css', array( 'elementor-frontend' ), STM_THEME_CHILD_VERSION );
            wp_register_script( 'stm-script-testimonials', STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/testimonials.js', array( 'elementor-frontend' ), STM_THEME_CHILD_VERSION );

            wp_dequeue_style( 'stm-instructors_carousel-style_1' );
            wp_dequeue_style( 'stm-instructors_carousel-style_2' );
            wp_register_style( 'stm-instructors_carousel-style_3', STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/instructors_carousel.css', array( 'elementor-frontend' ), STM_THEME_CHILD_VERSION );

            if ( defined( 'ELEMENTOR_ASSETS_URL' ) ) {
                $e_swiper_latest     = Plugin::$instance->experiments->is_feature_active( 'e_swiper_latest' );
                $e_swiper_asset_path = $e_swiper_latest ? ELEMENTOR_ASSETS_URL . 'lib/swiper/v8/' : ELEMENTOR_ASSETS_URL . 'lib/swiper/';
                $e_swiper_version    = $e_swiper_latest ? '8.4.5' : '5.3.6';

                $is_test_mode = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'ELEMENTOR_TESTS' ) && ELEMENTOR_TESTS;

                $e_swiper_css = ( ! $is_test_mode ) ? $e_swiper_asset_path . '/css/swiper.min.css' : $e_swiper_asset_path . '/css/swiper.css';
                $e_swiper_js  = ( ! $is_test_mode ) ? $e_swiper_asset_path . '/swiper.min.js' : $e_swiper_asset_path . '/swiper.js';

                wp_register_style(
                    'swiper',
                    $e_swiper_css,
                    array(),
                    $e_swiper_version
                );

                wp_register_script(
                    'swiper',
                    $e_swiper_js,
                    array(),
                    $e_swiper_version,
                    true
                );

                wp_register_script(
                    'swiper-ameen-bundle',
                    STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/ameen-course-bundle.js',
                    array( 'swiper' ),
                    $e_swiper_version,
                    true
                );

                wp_register_style(
                  'ameen-bundle',
                    STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/course-bundle.css',
                    array( 'swiper' ),
                    $e_swiper_version
                );
            }

        }, 99);
    }, 99);

    function change_my_script( $tag, $handle, $src ) {

        if ( 'my-script' === $handle ) {
            // return str_replace( ' src', ' async src', $tag );
            return str_replace( ' src', ' defer src', $tag );
        }

        return $tag;
    }