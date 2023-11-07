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

        }, 99);
    }, 99);