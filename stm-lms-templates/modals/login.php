<?php
    $otp = new STM_THEME_CHILD_OTP();
?>
<div class="modal fade stm-lms-modal-login" tabindex="-1" role="dialog" aria-labelledby="stm-lms-modal-login">
    <div class="modal-dialog" role="document">
        <?php if ( ! $otp->otp_enable ) : ?>
            <a href="#" class="modal-close"></a>
        <?php endif; ?>
        <div class="modal-content">
            <div class="modal-body">
                <?php if ( $otp->otp_enable ) : ?>
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/sign-in', array( 'form_position' => 'modal' ) ); ?>
                <?php else : ?>
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active">
                            <a href="#stm-lms-login-modal" data-toggle="tab"><?php esc_html_e( 'Login', 'masterstudy-child' ); ?></a>
                        </li>
                        <li role="presentation" class="">
                            <a href="#stm-lms-register" data-toggle="tab"><?php esc_html_e( 'Register', 'masterstudy-child' ); ?></a>
                        </li>
                    </ul>
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/login', array( 'form_position' => '-modal' ) ); ?>
                    <?php STM_LMS_Templates::show_lms_template( 'account/v1/register' ); ?>
                <?php endif; ?>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<?php if ( ! $otp->otp_enable ) : ?>
    <script>
        stm_lms_login(false);
        stm_lms_register(false);
    </script>
<?php endif; ?>
