<?php
    defined( 'ABSPATH' ) || exit;

    wp_enqueue_script( 'vue.js' );
    wp_enqueue_script( 'vue-resource.js' );
    do_action( 'stm_lms_template_main' );

    if ( function_exists( 'vc_asset_url' ) ) {
        wp_enqueue_style( 'stm_lms_wpb_front_css', vc_asset_url( 'css/js_composer.min.css' ), array( '' ), STM_LMS_VERSION );
    }

    $otp = new STM_THEME_CHILD_OTP();
?>

<?php STM_LMS_Templates::show_lms_template( 'modals/preloader' ); ?>

<div class="stm-lms-wrapper stm-lms-wrapper__login">

	<div class="container">

		<div class="row">

            <?php if ( $otp->otp_enable ) : ?>

                <div class="col-md-3"></div>

                <div class="col-md-6">
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/sign-in' ); ?>
                </div>

                <div class="col-md-3"></div>

            <?php else : ?>

                <div class="col-md-6">
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/login' ); ?>
                </div>

                <div class="col-md-6">
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/register' ); ?>
                </div>

            <?php endif; ?>

		</div>

	</div>

</div>
