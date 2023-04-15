<?php
/**
 * Checkout billing information form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-billing.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/** @global WC_Checkout $checkout */

?>
<div class="woocommerce-billing-fields">
    <?php if ( wc_ship_to_billing_address_only() && WC()->cart->needs_shipping() ) : ?>

        <h3><?php esc_html_e( 'Billing &amp; Shipping', 'masterstudy'); ?></h3>

    <?php else : ?>

        <h3><?php esc_html_e( 'Billing details', 'masterstudy'); ?></h3>

    <?php endif; ?>

    <?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>

    <div class="woocommerce-billing-fields__field-wrapper">
        <?php
        $fields = $checkout->get_checkout_fields( 'billing' );

        foreach ( $fields as $key => $field ) {
            if ( isset( $field['country_field'], $fields[ $field['country_field'] ] ) ) {
                $field['country'] = $checkout->get_value( $field['country_field'] );
            }
            woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
        }

        $profile_form = array();
        $forms = get_option('stm_lms_form_builder_forms', array());
        if (class_exists('STM_LMS_Form_Builder') && !empty($forms) && is_array($forms)) {
            foreach ($forms as $form) {
                if ($form['slug'] === 'profile_form') {
                    $profile_form = $form['fields'];
                }
            }
        }

        if ( ! empty( $profile_form ) ) {
            foreach ( $profile_form as $field ) {
                $response = true;
                $args = array(
                    'type'     => $field['type'],
                    'label'    => $field['label'],
                    'required' => true,
                );

                if ( in_array($field['type'], array('select', 'radio')) ) {
                    if ( ! empty( $field['choices'] ) ) {
                        foreach ($field['choices'] as $choice) {
                            $args['options'][$choice] = $choice;
                        }
                    }
                }

                if ( $field['slug'] === 'area-business' ) {
                    $response = 'Yes' === $checkout->get_value( 'gvcnyfv9rpo' );
                }
                else if ( $field['slug'] === 'number-staff' ) {
                    $response = 'Yes' === $checkout->get_value( 'gvcnyfv9rpo' );
                }
                else if ( $field['slug'] === 'area-specialization' ) {
                    $response = 'No' === $checkout->get_value( 'gvcnyfv9rpo' );
                }

                if ( $response ) {
                    woocommerce_form_field( $field['id'], $args, $checkout->get_value( $field['id'] ) );
                }
            }
        }
        ?>
    </div>
    <br />
    <p class="woocommerce-info">
        <span>
            <?php
                echo sprintf(
                    esc_html__('To change your details, go to %s edit profile %s', 'masterstudy-child'),
                    '<a href="'. STM_LMS_User::settings_url() .'">',
                    '</a>'
                );
            ?>
        </span>
    </p>

    <?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
</div>

<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
    <div class="woocommerce-account-fields">
        <?php if ( ! $checkout->is_registration_required() ) : ?>

            <p class="form-row form-row-wide create-account">
                <input class="input-checkbox" id="createaccount" <?php checked( ( true === $checkout->get_value( 'createaccount' ) || ( true === apply_filters( 'woocommerce_create_account_default_checked', false ) ) ), true ) ?> type="checkbox" name="createaccount" value="1" />
                <label for="createaccount" class="checkbox"><?php esc_html_e( 'Create an account?', 'masterstudy'); ?></label>
            </p>

        <?php endif; ?>

        <?php do_action( 'woocommerce_before_checkout_registration_form', $checkout ); ?>

        <?php if ( $checkout->get_checkout_fields( 'account' ) ) : ?>

            <div class="create-account">
                <p class="mg-bt-10"><?php esc_html_e( 'Create an account by entering the information below. If you are a returning customer please login at the top of the page.', 'masterstudy'); ?></p>
                <?php foreach ( $checkout->get_checkout_fields( 'account' ) as $key => $field ) : ?>
                    <?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>

        <?php endif; ?>

        <?php do_action( 'woocommerce_after_checkout_registration_form', $checkout ); ?>
    </div>
<?php endif; ?>
