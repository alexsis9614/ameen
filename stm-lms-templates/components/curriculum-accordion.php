<?php
    /**
     * @var int $course_id
     * @var int $current_lesson_id
     * @var array $user
     * @var array $curriculum
     * @var boolean $dark_mode
     *
     * masterstudy-curriculum-accordion_dark-mode - for dark mode
     * masterstudy-curriculum-accordion__wrapper_opened - for open curriculum list
     * masterstudy-curriculum-accordion__link_current - for current lesson
     * masterstudy-curriculum-accordion__check_completed - for completed lesson
     */

    wp_enqueue_style( 'masterstudy-curriculum-accordion' );
    wp_enqueue_script( 'masterstudy-curriculum-accordion' );

    $_user_id      = $user['id'];
    $_plans_object = new Lms\inc\classes\STM_Plans;

    if ( $author_id = get_post_field( 'post_author', $course_id ) ) {
        $author_id = absint( $author_id );
    }
?>

<div class="masterstudy-curriculum-accordion <?php echo esc_attr( $dark_mode ? 'masterstudy-curriculum-accordion_dark-mode' : '' ); ?>">
	<?php
        foreach ( $curriculum as $section ) {
            $opened               = in_array( $current_lesson_id, array_column( $section['materials'], 'post_id' ), true ) ? 'masterstudy-curriculum-accordion__wrapper_opened' : '';
            $section['materials'] = ms_plugin_curriculum_data( $course_id, $section['materials'] );
            $completed_count      = array_reduce(
                $section['materials'],
                function( $carry, $material ) {
                    if ( isset( $material['completed'] ) && 'completed' === $material['completed'] ) {
                        $carry++;
                    }
                    return $carry;
                },
                0
            );
	?>
		<div class="masterstudy-curriculum-accordion__wrapper <?php echo esc_attr( $opened ); ?>">
			<div class="masterstudy-curriculum-accordion__section">
				<h4 class="masterstudy-curriculum-accordion__section-title"><?php echo esc_html( $section['title'] ); ?></h4>
				<span class="masterstudy-curriculum-accordion__section-count"><?php echo esc_html( $completed_count . '/' . count( $section['materials'] ) ); ?></span>
				<span class="masterstudy-curriculum-accordion__toggler">
				    <img src="<?php echo esc_url( STM_LMS_URL . '/assets/icons/files/new/chevron_up.svg' ); ?>" class="masterstudy-curriculum-accordion__toggler-icon">
				</span>
			</div>
			<ul class="masterstudy-curriculum-accordion__list" style="<?php echo esc_attr( $opened ? 'display:flex' : 'display:none' ); ?>">
				<?php
                    foreach ( $section['materials'] as $material ) {
                        $lesson_id = stm_lms_get_wpml_binded_id( $material['post_id'] );

                        if ( ( ! STM_LMS_Instructor::is_instructor( $_user_id ) || $_user_id !== $author_id ) && $_plans_object->enable( $course_id ) ) {
                            $user_plan      = $_plans_object::get_user_meta_key( $_user_id, $course_id );

                            if ( LMS\inc\classes\STM_Curriculum::plan_exists( $lesson_id, $user_plan ) ) {
                                continue;
                            }
                        }

                        if ( function_exists( 'masterstudy_lms_curriculum_drip_data' ) ) {
                            $material = masterstudy_lms_curriculum_drip_data( $course_id, $curriculum, $material );
                        }
				?>
					<li class="masterstudy-curriculum-accordion__item">
						<a href="<?php echo esc_url( STM_LMS_Lesson::get_lesson_url( $course_id, $material['post_id'] ) ); ?>"
							class="masterstudy-curriculum-accordion__link <?php echo esc_attr( $material['post_id'] === $current_lesson_id ? 'masterstudy-curriculum-accordion__link_current' : '' ); ?><?php echo esc_attr( $material['lesson_locked_by_drip'] ? 'masterstudy-curriculum-accordion__link_disabled' : '' ); ?>">
							<div class="masterstudy-curriculum-accordion__title-wrapper">
								<div class="masterstudy-curriculum-accordion__title">
									<?php echo esc_html( $material['title'] ); ?>
								</div>
								<?php
								    if ( $material['lesson_lock_before_start'] || $material['lesson_locked_by_drip'] ) {
								?>
									<span class="masterstudy-curriculum-accordion__locked">
                                        <?php
                                            STM_LMS_Templates::show_lms_template(
                                                'components/hint',
                                                array(
                                                    'content'   => $material['lesson_lock_message'],
                                                    'side'      => 'right',
                                                    'dark_mode' => $dark_mode,
                                                )
                                            );
                                        ?>
									</span>
								<?php } else { ?>
									<span class="masterstudy-curriculum-accordion__check <?php echo esc_attr( ! empty( $material['completed'] ) ? 'masterstudy-curriculum-accordion__check_completed' : '' ); ?>"></span>
								<?php } ?>
							</div>
							<div class="masterstudy-curriculum-accordion__meta-wrapper">
								<img src="<?php echo esc_url( STM_LMS_URL . "/assets/icons/lessons/{$material['icon']}.svg" ); ?>" class="masterstudy-curriculum-accordion__image">
								<div class="masterstudy-curriculum-accordion__meta">
									<?php
                                        if ( 'stm-quizzes' === $material['post_type'] ) {
                                            /* translators: %s: number */
                                            echo esc_html( ! empty( $material['questions_array'] ) ? sprintf( __( '%d questions', 'masterstudy-lms-learning-management-system' ), count( $material['questions_array'] ) ) : '' );
                                            echo esc_html( empty( $material['questions_array'] ) ? $material['label'] : '' );
                                        }
                                        else {
                                            echo esc_html( $material['duration'] ?? '' );
                                            echo esc_html( $material['meta'] ?? '' );
                                            echo esc_html( empty( $material['meta'] ) && empty( $material['duration'] ) ? $material['label'] : '' );
                                        }
									?>
								</div>
							</div>
						</a>
					</li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>
</div>
