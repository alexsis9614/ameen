<?php
    add_action('wp_footer', function () {
        if ( ! is_user_logged_in() ) {
            STM_LMS_Templates::show_lms_template('modals/login');
        }
    });

    add_action( 'template_include', function ( $template ) {
        global $wp_query;

        if ( $wp_query->query_vars['lms_template'] === 'stm-lms-user-settings' ) {
            wp_dequeue_script( 'stm-lms-edit_account' );
            wp_deregister_script( 'stm-lms-edit_account' );

            wp_enqueue_script(
                'stm-lms-edit_account',
                STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/edit_account.js',
                array('vue.js', 'vue-resource.js'),
                STM_THEME_CHILD_VERSION,
                true
            );
            $data = wp_json_encode( STM_LMS_User::get_current_user() );
            wp_add_inline_script('stm-lms-edit_account',
                "var stm_lms_edit_account_info = {$data}");
        }

        if ( is_singular( 'stm-courses' ) ) {
            wp_enqueue_style(
                'stm-lms-child-course',
                STM_THEME_CHILD_DIRECTORY_URI . '/assets/css/course.css',
                array(),
                STM_THEME_CHILD_VERSION
            );
        }

        return $template;
    }, 100);

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

    class Filterable_Scripts extends WP_Scripts
    {
        function localize($handle, $object_name, $l10n)
        {
            $l10n = apply_filters('stm_script_filtering_l10n', $l10n, $handle, $object_name);
            return parent::localize($handle, $object_name, $l10n);
        }
    }

    add_action('admin_init', 'filter_bookit_styles_and_scripts');

    function filter_bookit_styles_and_scripts()
    {
        $GLOBALS['wp_scripts'] = new Filterable_Scripts;
    }

    add_filter('stm_script_filtering_l10n', function ($l10n, $handle, $object_name) {

        if ( 'bookit_window' !== $object_name ) {
            return $l10n;
        }

        $theme_uri_bookit = STM_THEME_CHILD_DIRECTORY_URI . '/assets/bookit/dist/';

        if ( 'bookit-dashboard-js' === $handle ) {
            wp_dequeue_script( $handle );

            $handle = 'stm-' . $handle;

            $l10n['translations']['calendar_view_booking_exam'] = esc_html__('Booking an exam', 'masterstudy-child');

            wp_enqueue_script( $handle, $theme_uri_bookit . 'dashboard/js/app.js', [], STM_THEME_CHILD_VERSION );
            wp_localize_script( $handle, 'bookit_window', $l10n );
        }

        return $l10n;
    }, 10, 3);