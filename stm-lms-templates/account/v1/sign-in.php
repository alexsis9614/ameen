<?php
    /**
     * @var $form_position
    */

    $form_position = $form_position ?? 'form';

    wp_enqueue_style(
        'stm-lms-sign-in',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/dist/css/sign-in.css',
        [],
        STM_THEME_CHILD_VERSION
    );

    wp_enqueue_script(
        'stm-lms-sign-in',
        STM_THEME_CHILD_DIRECTORY_URI . '/assets/bookit/dist/auth/sign-in.js',
        ['jquery', 'vue.js', 'vue-resource.js'],
        STM_THEME_CHILD_VERSION
    );

    $otp = new STM_THEME_CHILD_OTP();

    wp_localize_script('stm-lms-sign-in', 'stm_lms_sign_in_' . $form_position, array(
        'actions'             => $otp->actions,
        'nonce'               => $otp->nonce,
        'position'            => $form_position,
        'lost_password_nonce' => $otp->lost_password_nonce,
    ));

    stm_lms_register_style('login');

    $settings            = get_option( 'stm_lms_settings', array() );
    $user_account        = ! empty( $settings['user_url'] ) ? $settings['user_url'] : 0;
?>

    <div id="stm-lms-sign-in" class="stm-lms-sign-in active vue_is_disabled"
         v-bind:class="{'is_vue_loaded' : vue_loaded}">

        <button class="modal-close">
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M14 1.41L12.59 0L7 5.59L1.41 0L0 1.41L5.59 7L0 12.59L1.41 14L7 8.41L12.59 14L14 12.59L8.41 7L14 1.41Z" fill="#313843"/>
            </svg>
        </button>

        <div class="stm-lms-login__top">
            <template v-if="limit || sent_limit">
                <h3><?php esc_html_e('Application for limit update', 'masterstudy-child'); ?></h3>
            </template>
            <template v-else>
                <h3><?php esc_html_e('Sign up', 'masterstudy-child'); ?></h3>
                <p>
                    We'll send a one-time verification code to your phone number. Please make sure it is correct
                </p>
                <?php if ( is_page( $user_account ) ) : ?>
                    <p><?php esc_html_e('We\'ll send a confirmation code by sms', 'masterstudy-child'); ?></p>
                <?php endif; ?>
            </template>

            <?php do_action('stm_lms_login_end'); ?>
        </div>

        <div class="stm_lms_login_wrapper">

            <template v-if="limit || sent_limit">
                <transition name="slide-fade">
                    <h4 class="stm_lms_request_limit_title">
                        {{ message }}
                    </h4>
                </transition>
            </template>

            <form method="POST" @submit.prevent="formSubmit" v-if="!sent_limit">
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
                    <template v-if="register">
                        <div class="form-group">
                            <label class="heading_font"><?php esc_html_e( 'Name', 'masterstudy-child' ); ?></label>
                            <input class="form-control"
                                   type="text"
                                   name="login"
                                   autocomplete="login"
                                   v-model="enter_name"
                                   placeholder="<?php esc_html_e( 'Enter name', 'masterstudy-child' ); ?>"/>
                        </div>
                    </template>
                    <div class="form-group">
                        <label class="heading_font"><?php esc_html_e( 'Password', 'masterstudy-child' ); ?></label>
                        <input class="form-control"
                               type="password"
                               name="password"
                               v-model="enter_password"
                               placeholder="********"/>
                    </div>
                    <template v-if="register">
                        <div class="form-group">
                            <label class="heading_font"><?php esc_html_e( 'Password again', 'masterstudy-child' ); ?></label>
                            <input class="form-control"
                                   type="password"
                                   name="password_re"
                                   v-model="password_re"
                                   placeholder="<?php esc_html_e( 'Confirm password', 'masterstudy-child' ); ?>"/>
                        </div>
                    </template>
                </template>
                <template v-else-if="limit"></template>
                <template v-else-if="sent_limit"></template>
                <template v-else>
                    <div class="form-group form-group-phone">
                        <label class="heading_font" for="phone"><?php esc_html_e( 'Phone', 'masterstudy-child' ); ?></label>
                        <select v-model="phone_code"
                                name="phone_code"
                                disabled
                                class="form-control">
                            <option value="998">+998</option>
                        </select>
                        <masked-input
                                v-model="phone"
                                class="form-control"
                                name="phone"
                                id="phone"
                                mask="## ### ## ##"
                                placeholder="-- --- -- --"
                        />
                    </div>
                    <template v-if="sign_in">
                        <div class="form-group">
                            <label class="heading_font"><?php esc_html_e( 'Password', 'masterstudy-child' ); ?></label>
                            <input class="form-control"
                                   type="password"
                                   name="password"
                                   v-model="enter_password"
                                   placeholder="********"/>
                        </div>
                    </template>
                </template>

                <div class="stm_lms_login_wrapper__actions" :class="{'stm_lms_sending_limit_request': limit}" v-if="!sent_limit">

                    <button
                       type="submit"
                       class="btn btn-default"
                       v-bind:class="{'loading': loading}"
                       v-bind:disabled="loading">
                        <span v-if="verify"><?php esc_html_e('Submit', 'masterstudy-child'); ?></span>
                        <span v-else-if="password"><?php esc_html_e('Submit', 'masterstudy-child'); ?></span>
                        <span v-else-if="limit"><?php esc_html_e('Submit request', 'masterstudy-child'); ?></span>
                        <span v-else><?php esc_html_e('Sign up', 'masterstudy-child'); ?></span>
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
                <template v-else>
                    <p class="stm_lms_footer_sign_in text-center">
                        <?php esc_html_e('Already have an account ?', 'masterstudy-child'); ?>
                        <button class="btn-switch-forms-sign" @click.prevent="switchForms">
                            <?php esc_html_e('Kirish', 'masterstudy-child'); ?>
                        </button>
                    </p>
                </template>
            </form>

        </div>

        <template v-if="! limit && ! sent_limit">
            <transition name="slide-fade">
                <div class="stm-lms-message" v-bind:class="status" v-if="message" v-html="message"></div>
            </transition>
        </template>

    </div>

<?php do_action('stm_lms_login_section_end'); ?>