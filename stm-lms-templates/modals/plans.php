<?php
    /**
     * @var $course_id
     */

    $curriculum = new LMS\inc\classes\STM_Curriculum;
    $plans      = $curriculum->plans;
    $secondary_color = STM_LMS_Options::get_option('secondary_color', '#f2b91e');
?>
<style type="text/css">
    :root{
        --plans-color: <?php echo esc_attr( $secondary_color ); ?>
    }
    .stm-lms-modal-plans .pricing .card-content ul li:before{
        background-image: url("/wp-content/themes/masterstudy-child/assets/images/list-icon.svg");
    }
    .stm-lms-modal-plans .pricing .card-content ol li:before{
        background-image: url("/wp-content/themes/masterstudy-child/assets/images/list-icon-disable.svg");
    }
</style>
<div class="modal fade stm-lms-modal-plans" tabindex="-1" role="dialog" aria-labelledby="stm-lms-modal-login">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <button class="modal-plans-close" data-dismiss="modal"></button>
        <div class="modal-content">
            <div class="modal-body">
                <div class="pricing">
                    <div class="pricing__title text-center">
                        <?php esc_html_e( 'Pricing list ', 'masterstudy-child' ); ?>
                    </div>
                    <div class="pricing__description text-center">
                        <?php esc_html_e( 'No contract cancel anytime you want', 'masterstudy-child' ); ?>
                    </div>
                    <div class="row">
                        <?php
                            foreach ($plans as $key => $plan) :
                                $price = LMS\inc\classes\STM_Plans::price( $course_id, $plan['name'] );
                        ?>
                            <div class="card">
                                <div class="card-body">
                                    <?php if ( isset( $plan['badge'] ) ) : ?>
                                        <div class="badge">
                                            <?php echo $plan['badge']; ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ( isset( $plan['image'] ) ) : ?>
                                        <div class="card-image">
                                            <img src="<?php echo esc_url( $plan['image'] ); ?>" alt="<?php echo esc_attr( $plan['name'] ); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <?php echo $plan['name']; ?>
                                        </h5>
                                        <h6 class="card-price text-center">
                                            <?php echo \LMS\inc\classes\STM_Plans::display_price( $price ); ?>
                                        </h6>
                                    </div>
                                    <div class="card-content">
                                        <?php echo $plan['description']; ?>
                                    </div>
                                    <div class="d-grid">
                                        <?php
                                            $class = 'btn btn-default';

                                            if ( ! is_user_logged_in() ) {
                                                $class .= ' not-logged';
                                            }
                                        ?>
                                        <button
                                            class="<?php echo $class; ?>"
                                            data-course-plan="<?php echo esc_attr( $plan['name'] ); ?>"
                                            data-course-id="<?php echo esc_attr( $course_id ); ?>"
                                        >
                                            <?php echo $plan['text_button'] ?: esc_html__('Get plan', 'masterstudy-child'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->