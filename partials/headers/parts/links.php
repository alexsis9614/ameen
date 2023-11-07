<?php
    /**
     * @var $args
     */

    $link_1_title = esc_html__( 'Become an Instructor', 'masterstudy-child' );
    $link_2_title = esc_html__( 'For Enterprise', 'masterstudy-child' );
    $link_1_icon  = array(
        'value' => 'lnr lnr-bullhorn'
    );
    $link_2_icon = array(
        'value' => 'stmlms-case'
    );

    if( !empty( $args ) ) extract( $args );

    if( function_exists( 'stm_lms_register_style' ) ) {
        stm_lms_register_style( 'enterprise' );
        stm_lms_register_script( 'enterprise' );
    }

    if( ! empty( $link_1_title ) ) :
        if ( is_user_logged_in() ) :
            $current_user = wp_get_current_user();

            if( ! in_array( 'stm_lms_instructor', $current_user->roles ) ):
                $target = 'stm-lms-modal-become-instructor';
                $modal = 'become_instructor';

                if( function_exists( 'stm_lms_register_style' ) ) {
                    stm_lms_register_style( 'become_instructor' );
                    stm_lms_register_script( 'become_instructor' );
                }
?>
            <a href="#"
               class="stm_lms_bi_link normal_font"
               data-target=".<?php echo esc_attr( $target ); ?>"
               data-lms-modal="<?php echo esc_attr( $modal ); ?>">
                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M6.45268 8.79333L7.99935 7.62L9.53935 8.79333L8.95268 6.89333L10.4993 5.66667H8.60602L7.99935 3.79333L7.39268 5.66667H5.49935L7.03935 6.89333L6.45268 8.79333ZM13.3327 6.33333C13.3327 3.38667 10.946 1 7.99935 1C5.05268 1 2.66602 3.38667 2.66602 6.33333C2.66602 7.68667 3.17268 8.91333 3.99935 9.85333V15L7.99935 13.6667L11.9993 15V9.85333C12.826 8.91333 13.3327 7.68667 13.3327 6.33333ZM7.99935 2.33333C10.206 2.33333 11.9993 4.12667 11.9993 6.33333C11.9993 8.54 10.206 10.3333 7.99935 10.3333C5.79268 10.3333 3.99935 8.54 3.99935 6.33333C3.99935 4.12667 5.79268 2.33333 7.99935 2.33333ZM7.99935 12.3333L5.33268 13.0133V10.9467C6.11935 11.4 7.02602 11.6667 7.99935 11.6667C8.97268 11.6667 9.87935 11.4 10.666 10.9467V13.0133L7.99935 12.3333Z" fill="white"/>
                </svg>
                <span><?php echo sanitize_text_field( $link_1_title ); ?></span>
            </a>
<?php
            endif;
        else:
            if ( class_exists( 'STM_LMS_User' ) ) :
?>
            <a href="<?php echo esc_url( STM_LMS_User::login_page_url() ); ?>"
               class="stm_lms_bi_link normal_font">
                <i class="<?php echo esc_attr( $link_1_icon[ 'value' ] ) ?> secondary_color"></i>
                <span><?php echo sanitize_text_field( $link_1_title ); ?></span>
            </a>

<?php
            endif;
        endif;
    endif;

    if ( ! empty( $link_2_title ) ) :
?>

    <a href="#" class="stm_lms_bi_link normal_font" data-target=".stm-lms-modal-enterprise" data-lms-modal="enterprise">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M10 7.66699V3.66699L8 1.66699L6 3.66699V5.00033H2V14.3337H14V7.66699H10ZM4.66667 13.0003H3.33333V11.667H4.66667V13.0003ZM4.66667 10.3337H3.33333V9.00033H4.66667V10.3337ZM4.66667 7.66699H3.33333V6.33366H4.66667V7.66699ZM8.66667 13.0003H7.33333V11.667H8.66667V13.0003ZM8.66667 10.3337H7.33333V9.00033H8.66667V10.3337ZM8.66667 7.66699H7.33333V6.33366H8.66667V7.66699ZM8.66667 5.00033H7.33333V3.66699H8.66667V5.00033ZM12.6667 13.0003H11.3333V11.667H12.6667V13.0003ZM12.6667 10.3337H11.3333V9.00033H12.6667V10.3337Z" fill="white"/>
        </svg>
        <span><?php echo sanitize_text_field( $link_2_title ); ?></span>
    </a>

<?php endif; ?>