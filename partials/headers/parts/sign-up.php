<?php
    /**
     * @var $args
    */

    $title = esc_html__('Sign up', 'masterstudy-child');
    if( is_user_logged_in() ) $title = esc_html__('My account', 'masterstudy-child');

    if ( ! empty( $args ) ) extract( $args );

    if ( class_exists( 'STM_LMS_User' ) ) :
?>
    <a
        href="<?php echo esc_url( STM_LMS_User::login_page_url() ); ?>"
        class="btn btn-default"
        data-text="<?php echo esc_attr($title); ?>"
        data-target=".stm-lms-modal-login"
        data-lms-modal="login">
        <span><?php echo esc_attr($title); ?></span>
    </a>
<?php endif;