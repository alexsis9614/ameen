<?php
    /**
     * Lost password form
     *
     * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
     *
     * HOWEVER, on occasion WooCommerce will need to update template files and you
     * (the theme developer) will need to copy the new files to your theme to
     * maintain compatibility. We try to do this as little as possible, but it does
     * happen. When this occurs the version of the template file will be bumped and
     * the readme will list any important changes.
     *
     * @see https://docs.woocommerce.com/document/template-structure/
     * @package WooCommerce\Templates
     * @version 7.0.1
     */

    defined( 'ABSPATH' ) || exit;

    do_action( 'woocommerce_before_lost_password_form' );

    $otp = new STM_THEME_CHILD_OTP();

    if ( $otp->otp_enable ) {
        wp_enqueue_script(
            'stm-lms-lost-password',
            STM_THEME_CHILD_DIRECTORY_URI . '/assets/bookit/dist/auth/lost-password.js',
            ['jquery', 'vue.js', 'vue-resource.js'],
            STM_THEME_CHILD_VERSION
        );
    }
?>

    <div class="stm-lms-wrapper stm-lms-wrapper__login">

        <div class="container">

            <div class="row">

                <div class="col-md-3"></div>

                <div class="col-md-6">
                    <?php if ( $otp->otp_enable ) : ?>
                        <div
                            id="stm-lms-lost-password<?php if (isset($form_position)) esc_attr_e($form_position); ?>"
                            class="stm-lms-lost-password active vue_is_disabled"
                            v-bind:class="{'is_vue_loaded' : vue_loaded}">

                            <div class="stm-lms-login__top">
                                <h3><?php esc_html_e('Enter phone number', 'masterstudy-child'); ?></h3>
                                <p><?php esc_html_e('We\'ll send a confirmation code by sms', 'masterstudy-child'); ?></p>

                                <?php do_action('stm_lms_login_end'); ?>
                            </div>


                            <div class="stm_lms_login_wrapper">

                                <form method="POST" @submit.prevent="formSubmit">
                                    <template v-if="verify">
                                        <div class="form-group">
                                            <label class="heading_font"><?php esc_html_e( 'Verification code', 'masterstudy-child' ); ?></label>
                                            <input
                                                    type="text"
                                                    v-model="code"
                                                    class="form-control"
                                                    name="code"
                                            />
                                        </div>
                                    </template>
                                    <template v-else-if="password">
                                        <div class="form-group">
                                            <label class="heading_font"><?php esc_html_e( 'New password', 'masterstudy-child' ); ?></label>
                                            <input class="form-control"
                                                   type="password"
                                                   name="password"
                                                   v-model="enter_password"
                                                   autocomplete="new-password"
                                                   placeholder="<?php esc_html_e( 'Enter password', 'masterstudy-child' ); ?>"/>
                                        </div>
                                        <div class="form-group">
                                            <label class="heading_font"><?php esc_html_e( 'Re-enter new password', 'masterstudy-child' ); ?></label>
                                            <input class="form-control"
                                                   type="password"
                                                   name="password_re"
                                                   v-model="password_re"
                                                   autocomplete="new-password"
                                                   placeholder="<?php esc_html_e( 'Confirm password', 'masterstudy-child' ); ?>"/>
                                        </div>
                                    </template>
                                    <template v-else>
                                        <div class="form-group">
                                            <label class="heading_font"><?php esc_html_e( 'Phone', 'masterstudy-child' ); ?></label>
                                            <masked-input
                                                    v-model="phone"
                                                    class="form-control"
                                                    name="phone"
                                                    mask="\+\998 (##) ###-##-##"
                                                    placeholder="<?php esc_html_e( 'Enter phone', 'masterstudy-child' ); ?>"
                                            />
                                        </div>
                                    </template>

                                    <div class="stm_lms_login_wrapper__actions">

                                        <button
                                                type="submit"
                                                class="btn btn-default"
                                                v-bind:class="{'loading': loading}"
                                                v-bind:disabled="loading">
                                            <span v-if="verify"><?php esc_html_e('Submit', 'masterstudy-child'); ?></span>
                                            <span v-else-if="password"><?php esc_html_e('Submit', 'masterstudy-child'); ?></span>
                                            <span v-else><?php esc_html_e('Get code', 'masterstudy-child'); ?></span>
                                        </button>

                                    </div>

                                    <template v-if="verify">
                                        <p class="stm_lms_footer_sign_in text-center">
                                            <?php esc_html_e('Didn\'t get the code? Wrong number?', 'masterstudy-child'); ?> <br />
                                            <button type="submit" class="btn-resend-code" @click="resend = true">
                                                <?php esc_html_e('Resend code', 'masterstudy-child'); ?>
                                            </button>
                                        </p>
                                    </template>
                                    </p>
                                </form>

                            </div>

                            <transition name="slide-fade">
                                <div class="stm-lms-message" v-bind:class="status" v-if="message" v-html="message"></div>
                            </transition>

                        </div>
                    <?php else: ?>
                        <form method="post" class="woocommerce-ResetPassword lost_reset_password">

                            <p><?php echo apply_filters( 'woocommerce_lost_password_message', esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'woocommerce' ) ); ?></p><?php // @codingStandardsIgnoreLine ?>

                            <p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
                                <label for="user_login"><?php esc_html_e( 'Username or email', 'woocommerce' ); ?></label>
                                <input class="woocommerce-Input woocommerce-Input--text input-text" type="text" name="user_login" id="user_login" autocomplete="username" />
                            </p>

                            <div class="clear"></div>

                            <?php do_action( 'woocommerce_lostpassword_form' ); ?>

                            <p class="woocommerce-form-row form-row">
                                <input type="hidden" name="wc_reset_password" value="true" />
                                <button type="submit" class="woocommerce-Button button<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" value="<?php esc_attr_e( 'Reset password', 'woocommerce' ); ?>"><?php esc_html_e( 'Reset password', 'woocommerce' ); ?></button>
                            </p>

                            <?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>

                        </form>
                    <?php endif; ?>
                </div>

                <div class="col-md-3"></div>
            </div>
        </div>
    </div>

<?php
    do_action( 'woocommerce_after_lost_password_form' );