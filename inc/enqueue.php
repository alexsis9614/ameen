<?php
    add_action('wp_footer', function () {
        if ( ! is_user_logged_in() ) {
            STM_LMS_Templates::show_lms_template('modals/login');
        }
    });