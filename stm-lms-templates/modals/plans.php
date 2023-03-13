<?php
    /**
     * @var $course_id
     */

    $curriculum = new STM_THEME_CHILD_Curriculum;
    $plans      = $curriculum->plans;
?>
<div class="modal fade stm-lms-modal-plans" tabindex="-1" role="dialog" aria-labelledby="stm-lms-modal-login">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <a href="#" class="modal-close" data-dismiss="modal"></a>
        <div class="modal-content">
            <div class="modal-body">
                <div class="pricing">
                    <div class="row">
                        <?php
                            foreach ($plans as $plan) :
                                $price = STM_THEME_CHILD_Curriculum::plan_price( $course_id, $plan['name'] );
                        ?>
                            <div class="col-xs-12 col-sm-6 col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="card-content">
                                            <h5 class="card-title text-muted text-uppercase text-center">
                                                <?php echo $plan['name']; ?>
                                            </h5>
                                            <h6 class="card-price text-center">
                                                <?php echo STM_LMS_Helpers::display_price( $price ); ?>
                                            </h6>
                                            <hr>
                                            <?php echo $plan['description']; ?>
                                        </div>
                                        <div class="d-grid">
                                            <a href="#" class="btn btn-default text-uppercase" data-course-plan="<?php echo esc_attr( $plan['name'] ); ?>" data-course-id="<?php echo esc_attr( $course_id ); ?>">
                                                <?php echo $plan['text_button'] ?: esc_html__('Get plan', 'masterstudy-child'); ?>
                                            </a>
                                        </div>
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

<script>
    // stm_lms_login(false);
</script>
