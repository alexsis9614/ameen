<?php
    if ( class_exists( 'STM_LMS_Templates') ) :
        if ( is_user_logged_in() ):
            STM_LMS_Templates::show_lms_template('global/account-dropdown');
        else :
//            get_template_part('partials/headers/parts/log-in');
            get_template_part('partials/headers/parts/sign-up');
        endif;

        if ( stm_option( 'default_show_wishlist', true ) ) {
            STM_LMS_Templates::show_lms_template( 'global/wishlist-button' );
        }
    endif;
?>