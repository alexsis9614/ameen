<?php
    /**
     * @var $post_id
     * @var $item_id
     */

    $completed = STM_LMS_Lesson::is_lesson_completed( null, $post_id, $item_id );

    stm_lms_register_style( 'lesson/total_progress' );
    stm_lms_register_script( 'lesson/total_progress', array( 'vue.js', 'vue-resource.js' ) );
    wp_localize_script(
        'stm-lms-lesson/total_progress',
        'total_progress',
        array(
            'course_id' => $post_id,
            'completed' => (bool) $completed,
        )
    );
    if ( class_exists( 'STM_LMS_Certificate_Builder' ) ) {
        wp_register_script( 'jspdf', STM_LMS_URL . '/assets/vendors/jspdf.umd.js', array(), stm_lms_custom_styles_v(), false );
        wp_enqueue_script(
            'stm_generate_certificate',
            STM_LMS_URL . '/assets/js/certificate_builder/generate_certificate.js',
            array(
                'jspdf',
                'stm_certificate_fonts',
            ),
            stm_lms_custom_styles_v(),
            false
        );
    }
    $disable_smile           = STM_LMS_Options::get_option( 'finish_popup_image_disable', false );
    $failed_image            = STM_LMS_URL . '/assets/img/faces/crying.svg';
    $success_image           = STM_LMS_URL . '/assets/img/faces/kissing.svg';
    $custom_failed_image_id  = STM_LMS_Options::get_option( 'finish_popup_image_failed' );
    $custom_success_image_id = STM_LMS_Options::get_option( 'finish_popup_image_success' );
?>
<div class="stm_lms_finish_score_popup" style="opacity: 0;">

	<div class="stm_lms_finish_score_popup__overlay"></div>

	<div class="stm_lms_finish_score_popup__inner">

		<i class="stm_lms_finish_score_popup__close fa fa-times"></i>

		<div id="stm_lms_finish_score">
			<h4 v-if="loading" class="loading">
				<?php esc_html_e( 'Loading your statistics', 'masterstudy-child' ); ?>
			</h4>

			<div class="stm_lms_finish_score" v-else>

				<div class="stm_lms_finish_score__head">
					<?php
                        if ( ! $disable_smile ) :
                            if ( ! empty( $custom_failed_image_id ) ) {
                                $custom_failed_image_url = wp_get_attachment_image_url( $custom_failed_image_id, 'thumbnail' );
                                if ( ! empty( $custom_failed_image_url ) ) {
                                    $failed_image = $custom_failed_image_url;
                                }
                            }
                            if ( ! empty( $custom_success_image_id ) ) {
                                $custom_success_image_url = wp_get_attachment_image_url( $custom_success_image_id, 'thumbnail' );
                                if ( ! empty( $custom_success_image_url ) ) {
                                    $success_image = $custom_success_image_url;
                                }
                            }
					?>
						<div class="stm_lms_finish_score__face">
							<img src="<?php echo esc_url( $failed_image ); ?>"
								v-if="!stats.course_completed"/>
							<img src="<?php echo esc_url( $success_image ); ?>" v-else/>
						</div>
					<?php endif; ?>
					<div class="stm_lms_finish_score__score <?php echo esc_attr( ( $disable_smile ) ? 'no_face' : '' ); ?>">
						<span><?php esc_html_e( 'Your score', 'masterstudy-child' ); ?></span>
						<h3 v-html="stats.course.progress_percent + '%'"></h3>
					</div>
				</div>

				<div class="stm_lms_finish_score__notice">
					<span v-if="!stats.course_completed"><?php esc_html_e( 'You have NOT completed the course', 'masterstudy-child' ); ?></span>
					<span v-else><?php esc_html_e( 'You have successfully completed the course', 'masterstudy-child' ); ?></span>
				</div>

				<h2 class="stm_lms_finish_score__title" v-html="stats.title"></h2>

				<div class="stm_lms_finish_score__stats">

					<div class="stm_lms_finish_score__stat" v-for="(stat, type) in stats.curriculum">

						<div :class="'stm_lms_finish_score__stat_' + type" v-if="type==='lesson'">
							<i class="far fa-file-alt"></i>
							<span>
								<?php esc_html_e( 'Pages:', 'masterstudy-child' ); ?>
								<strong>{{stat.completed}}/{{stat.total}}</strong>
							</span>
						</div>

						<div :class="'stm_lms_finish_score__stat_' + type" v-if="type==='multimedia'">
							<i class="far fa-play-circle"></i>
							<span>
								<?php esc_html_e( 'Media:', 'masterstudy-child' ); ?>
								<strong>{{stat.completed}}/{{stat.total}}</strong>
							</span>
						</div>

						<div :class="'stm_lms_finish_score__stat_' + type" v-if="type==='quiz'">
							<i class="far fa-question-circle"></i>
							<span>
								<?php esc_html_e( 'Quizzes:', 'masterstudy-child' ); ?>
								<strong>{{stat.completed}}/{{stat.total}}</strong>
							</span>
						</div>

						<div :class="'stm_lms_finish_score__stat_' + type" v-if="type==='assignment'">
							<i class="fas fa-spell-check"></i>
							<span>
								<?php esc_html_e( 'Assignments:', 'masterstudy-child' ); ?>
								<strong>{{stat.completed}}/{{stat.total}}</strong>
							</span>
						</div>

					</div>

				</div>

				<div class="stm_lms_finish_score__buttons">
					<!--Buttons for passed-->
					<div class="inner">
						<?php if ( class_exists( 'STM_LMS_Certificate_Builder' ) ) : ?>
							<a v-if="stats.course_completed" href="#" class="btn btn-default stm_preview_certificate"
								data-course-id="<?php echo esc_attr( $post_id ); ?>">
								<?php esc_html_e( 'Certificate', 'masterstudy-child' ); ?>
							</a>
						<?php endif; ?>

						<a :href="stats.url" class="btn btn-default btn-green">
							<?php esc_html_e( 'View course', 'masterstudy-child' ); ?>
						</a>
                        <?php if ( defined( 'BOOKIT_VERSION' ) ) : ?>
                            <template v-if="stats.booking_url">
                                <a :href="stats.booking_url" class="btn btn-default btn-green">
                                    <?php esc_html_e( 'Booking zoom', 'masterstudy-child' ); ?>
                                </a>
                            </template>
                        <?php endif; ?>
					</div>

				</div>

			</div>

		</div>

	</div>

</div>
