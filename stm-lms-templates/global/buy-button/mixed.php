<?php
    /**
     * @var $course_id
     * @var $item_id
     */

    global $wp_query;

	$bundle_widget = $wp_query->get( 'bundle_widget' );

    use LMS\inc\classes\STM_Course;

    $plans        = new LMS\inc\classes\STM_Plans;
    $plans_enable = $plans->enable( $course_id );

    stm_lms_register_script( 'buy-button', array( 'jquery.cookie' ) );
    if ( ! empty( $plans_enable ) ) {
        wp_enqueue_script('buy-plans');
        wp_enqueue_style('buy-plans');
    }
    stm_lms_register_style( 'buy-button-mixed' );

    $item_id      = ( ! empty( $item_id ) ) ? $item_id : '';
    $has_course   = STM_LMS_User::has_course_access( $course_id, $item_id, false );
    $course_price = STM_LMS_Course::get_course_price( $course_id );

    if ( isset( $has_access ) ) {
        $has_course = $has_access;
    }

    $is_prerequisite_passed = true;

    if ( class_exists( 'STM_LMS_Prerequisites' ) ) {
        $is_prerequisite_passed = STM_LMS_Prerequisites::is_prerequisite( true, $course_id );
    }

    do_action( 'stm_lms_before_button_mixed', $course_id );

    if ( apply_filters( 'stm_lms_before_button_stop', false, $course_id ) && false === $has_course ) {
        return false;
    }

    $is_affiliate = STM_LMS_Courses_Pro::is_external_course( $course_id );
    $not_salebale = get_post_meta( $course_id, 'not_single_sale', true );

    $bundle_arrow = '
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M8.08711 5L6.91211 6.175L10.7288 10L6.91211 13.825L8.08711 15L13.0871 10L8.08711 5Z" fill="#323D66"/>
        </svg>
    ';


    if ( ! $is_affiliate ) :
        $_courses = STM_Course::get_course_bundle( $course_id );
?>

    <div class="stm-lms-buy-buttons stm-lms-buy-buttons-mixed stm-lms-buy-buttons-mixed-pro">
        <?php if ( ( $has_course || ( empty( $course_price ) && ! $not_salebale ) ) && $is_prerequisite_passed ) : ?>

            <?php
                $user = STM_LMS_User::get_current_user();
                if ( ! is_user_logged_in() ) :
                    stm_lms_register_style( 'login' );
                    stm_lms_register_style( 'register' );
                    enqueue_login_script();
                    enqueue_register_script();
                    $lesson_url = LMS\inc\classes\STM_Curriculum::item_url( $course_id, 0 );
            ?>

                <a href="<?php echo esc_url( $lesson_url ); ?>" class="btn btn-default">
                    <span><?php esc_html_e( 'Start Course', 'masterstudy-child' ); ?></span>
                </a>
            <?php
                else :
                    $course         = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( get_current_user_id(), $course_id, array( 'current_lesson_id', 'progress_percent' ) ) );
                    $current_lesson = ( ! empty( $course['current_lesson_id'] ) ) ? $course['current_lesson_id'] : '0';
                    $progress       = ( ! empty( $course['progress_percent'] ) ) ? intval( $course['progress_percent'] ) : 0;
                    $lesson_url     = LMS\inc\classes\STM_Curriculum::item_url( $course_id, $current_lesson );
                    $btn_label      = esc_html__( 'Start course', 'masterstudy-child' );

                    if ( $progress > 0 ) {
                        $btn_label = esc_html__( 'Continue', 'masterstudy-child' );
                    }
            ?>
                <a href="<?php echo esc_url( $lesson_url ); ?>" class="btn btn-default start-course">
                    <span>
                        <?php
                            echo esc_html( sanitize_text_field( $btn_label ) );

                            if ( ! empty( $_courses ) && $bundle_widget ) {
                                echo $bundle_arrow;
                            }
                        ?>
                    </span>
                </a>

            <?php endif; ?>

        <?php
            else :
                $price             = get_post_meta( $course_id, 'price', true );
                $sale_price        = STM_LMS_Course::get_sale_price( $course_id );
                $not_in_membership = get_post_meta( $course_id, 'not_membership', true );
                $btn_class         = array( 'btn btn-default' );

                if ( empty( $price ) && ! empty( $sale_price ) ) {
                    $price      = $sale_price;
                    $sale_price = '';
                }

                if ( ! empty( $price ) && ! empty( $sale_price ) ) {
                    $tmp_price  = $sale_price;
                    $sale_price = $price;
                    $price      = $tmp_price;
                }

                if ( $not_salebale ) {
                    $price      = '';
                    $sale_price = '';
                }

                $btn_class[] = 'heading_font';

                if ( is_user_logged_in() ) {
                    $attributes = array();
                    if ( ! $not_salebale && empty( $plans_enable ) ) {
                        $attributes[] = 'data-buy-course="' . intval( $course_id ) . '"';
                    }
                } else {
                    stm_lms_register_style( 'login' );
                    stm_lms_register_style( 'register' );
                    enqueue_login_script();
                    enqueue_register_script();
                    $attributes = array(
                        'data-target=".stm-lms-modal-login"',
                        'data-lms-modal="login"',
                    );
                }

                if ( ! empty( $plans_enable ) && empty( $_courses ) && ! $bundle_widget ) {
                    $attributes = array(
                        'data-target=".stm-lms-modal-plans"',
                        'data-lms-modal="plans"',
                        'data-lms-params="'. esc_attr( json_encode(['course_id' => $course_id]) ) .'"',
                    );

                    $btn_class[] = 'btn-plans';
                }

                $subscription_enabled = ( empty( $not_in_membership ) && STM_LMS_Subscriptions::subscription_enabled() );
                if ( $subscription_enabled ) {
                    $plans_courses = STM_LMS_Course::course_in_plan( $course_id );
                }

                $dropdown_enabled = ! empty( $plans_courses );

                if ( empty( $plans_courses ) ) {
                    $dropdown_enabled = is_user_logged_in() && class_exists( 'STM_LMS_Point_System' );
                }

                $mixed_classes   = array();
                $mixed_classes[] = ( $dropdown_enabled ) ? 'subscription_enabled' : 'subscription_disabled';

                $show_buttons = apply_filters( 'stm_lms_pro_show_button', empty( $plans_enable ), $course_id );
                if ( $show_buttons ) :
        ?>
                <div class="<?php echo esc_attr( implode( ' ', $mixed_classes ) ); ?>">
                    <?php if ( ! $bundle_widget ) : ?>
                        <div class="<?php echo esc_attr( implode( ' ', $btn_class ) ); ?>"
                            <?php
                                if ( ! $dropdown_enabled ) {
                                    echo wp_kses_post( implode( ' ', apply_filters( 'stm_lms_buy_button_auth', $attributes, $course_id ) ) );
                                }
                            ?>
                        >
                    <?php else : ?>
                        <a href="<?php echo esc_url( get_the_permalink( $course_id ) ); ?>" class="<?php echo esc_attr( implode( ' ', $btn_class ) ); ?>"
                            <?php
                                if ( ! $dropdown_enabled ) {
                                    echo wp_kses_post( implode( ' ', apply_filters( 'stm_lms_buy_button_auth', $attributes, $course_id ) ) );
                                }
                            ?>
                        >
                    <?php endif; ?>

					<span>
						<?php
                            if ( ! empty( $plans_enable ) && empty( $_courses ) && ! $bundle_widget ) {
                                esc_html_e( 'See plans', 'masterstudy-child' );
                            } else if ( ! empty( $_courses ) && $bundle_widget ) {
                                esc_html_e( 'View collection', 'masterstudy-child' );
                                echo $bundle_arrow;
                            } else {
                                esc_html_e( 'Get course', 'masterstudy-child' );
                            }
                        ?>
					</span>

                    <?php if ( ! $bundle_widget ) : ?>
                        </div>
                    <?php else : ?>
                        </a>
                    <?php endif; ?>

                    <div class="stm_lms_mixed_button__list">
                        <?php
                            if ( $dropdown_enabled ) :
                                stm_lms_register_style( 'membership' );
                                $subs = STM_LMS_Subscriptions::user_subscription_levels();

                                if ( ! $not_salebale ) :
                        ?>
                                <a class="stm_lms_mixed_button__single" href="#" <?php echo wp_kses_post( implode( ' ', apply_filters( 'stm_lms_buy_button_auth', $attributes, $course_id ) ) ); ?>>
                                    <span><?php esc_html_e( 'One Time Payment', 'masterstudy-child' ); ?></span>
                                </a>
                        <?php
                            endif;

                            if ( $subscription_enabled && ! empty( $plans_courses ) ) :
                                $plans_course_ids = wp_list_pluck( $plans_courses, 'id' );
                                $plans_have_quota = false;
                                $needs_approval   = false;

                                foreach ( $subs as $sub ) {
                                    if ( ! in_array( $sub->ID, $plans_course_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                                        continue;
                                    }

                                    if ( $sub->course_number > 0 ) {
                                        $plans_have_quota = true;
                                        $user_approval    = get_user_meta( get_current_user_id(), 'pmpro_approval_' . $sub->ID, true );

                                        // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                                        if ( ! empty( $user_approval['status'] ) && in_array( $user_approval['status'], array( 'pending', 'denied' ) ) ) {
                                            $needs_approval = true;
                                        }
                                    }
                                }

                                if ( $plans_have_quota ) :
                                    $subs_info = array();

                                    foreach ( $subs as $sub ) {
                                        if ( ! in_array( $sub->ID, $plans_course_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
                                            continue;
                                        }

                                        $subs_info[] = array(
                                            'id'            => $sub->subscription_id,
                                            'course_id'     => get_the_ID(),
                                            'name'          => $sub->name,
                                            'course_number' => $sub->course_number,
                                            'used_quotas'   => $sub->used_quotas,
                                            'quotas_left'   => $sub->quotas_left,
                                        );
                                    }
                            ?>
                                    <button type="button"
                                            data-lms-params='<?php echo wp_json_encode( $subs_info ); ?>'
                                            class=""
                                            data-target=".stm-lms-use-subscription"
                                            data-lms-modal="use_subscription"
                                        <?php
                                        if ( $needs_approval ) {
                                            echo 'disabled="disabled"';}
                                        ?>
                                    >
                                        <span><?php esc_html_e( 'Enroll with Membership', 'masterstudy-child' ); ?></span>
                                        <?php if ( $needs_approval ) : ?>
                                            <small><?php esc_html_e( 'Your membership account is not approved!', 'masterstudy-child' ); ?></small>
                                        <?php endif; ?>
                                    </button>

                            <?php
                                else :
                                    $buy_url   = STM_LMS_Subscriptions::level_url();
                                    $buy_label = esc_html__( 'Enroll with Membership', 'masterstudy-child' );

                                    $plans = array(
                                        $buy_url => $buy_label,
                                    );

                                    if ( ! empty( $plans_courses ) ) {
                                        $plans = array();

                                        foreach ( $plans_courses as $plan_course ) {
                                            $plan_course_limit = get_option( "stm_lms_course_number_{$plan_course->id}", 0 );

                                            if ( empty( $plan_course_limit ) ) {
                                                continue;
                                            }

                                            stm_lms_register_script( 'buy/plan_cookie', array( 'jquery.cookie' ), true );

                                            $buy_url   = add_query_arg( 'level', $plan_course->id, STM_LMS_Subscriptions::checkout_url() );
                                            $buy_label = sprintf(
                                            /* translators: %s: plan name */
                                                esc_html__( 'Available in "%s" plan', 'masterstudy-child' ),
                                                $plan_course->name
                                            );

                                            $plans[ $buy_url ] = $buy_label;
                                        }
                                    }

                                    foreach ( $plans as $plan_url => $plan_label ) :
                            ?>
                                        <a href="<?php echo esc_url( $plan_url ); ?>"
                                           class="btn btn-default btn-subscription btn-outline btn-save-checkpoint"
                                           data-course-id="<?php echo esc_attr( $course_id ); ?>">
                                            <span><?php echo esc_html( $plan_label ); ?></span>
                                        </a>
                            <?php
                                            endforeach;
                                        endif;
                                    endif;
                                endif;

                                do_action( 'stm_lms_after_mixed_button_list', $course_id );
                            ?>
                    </div>
                </div>
            <?php
                    else :
            ?>
                        <a href="#" class="btn btn-default" <?php echo wp_kses_post( implode( ' ', apply_filters( 'stm_lms_buy_button_auth', $attributes, $course_id ) ) ); ?>>
                            <span><?php esc_html_e( 'Get course', 'masterstudy-child' ); ?></span>
                        </a>
            <?php
                        do_action( 'stm_lms_pro_instead_buttons', $course_id );
                    endif;
                endif;

                do_action( 'stm_lms_buy_button_end', $course_id );
        ?>

    </div>

<?php
    endif;