<?php
    defined( 'ABSPATH' ) || exit;

    get_header();

    $lms_current_user = STM_LMS_User::get_current_user( '', true, true );

    stm_lms_register_style( 'user' );
    do_action( 'stm_lms_template_main' );

    STM_LMS_Templates::show_lms_template( 'modals/preloader' );
?>
    <div class="stm-lms-wrapper user-account-page">
        <div class="container">
            <?php
                do_action( 'stm_lms_admin_after_wrapper_start', $lms_current_user );

                STM_LMS_Templates::show_lms_template(
                    'account/private/parts/top_info',
                    array(
                        'title' => esc_html__( 'Booking 1 on 1 meeting with the instructor', 'masterstudy-child' ),
                    )
                );

                if ( defined('BOOKIT_VERSION') ) {
                    echo do_shortcode('[bookit-booking-zoom]');
                }
                else {
                    esc_html_e('Bookit plugin disabled', 'masterstudy-child');
                }
            ?>
        </div>
    </div>

<?php
    get_footer();