<?php
/**
 * @var int $post_id
 * @var int $item_id
 * @var string $lesson_type
 * @var array $material_ids
 * @var object $current_material
 * @var boolean $has_access
 * @var boolean $lesson_lock_before_start
 * @var boolean $lesson_locked_by_drip
 * @var array $current_user
 * @var boolean $dark_mode
 */

wp_enqueue_style( 'masterstudy-course-player-navigation' );
wp_enqueue_script( 'masterstudy-course-player-navigation' );

$data = apply_filters( 'masterstudy_lms_course_player_navigation_data', $post_id, $item_id, $material_ids, $current_material );
$is_previewed = STM_LMS_Lesson::is_previewed( $post_id, $item_id );
?>

<div class="masterstudy-course-player-navigation <?php echo esc_attr( $dark_mode ? 'masterstudy-course-player-navigation_dark-mode' : '' ); ?>">
	<div class="masterstudy-course-player-navigation__wrapper">
		<div class="masterstudy-course-player-navigation__prev">
			<?php
			if ( ! empty( $data['prev_lesson'] ) && $has_access || ( $is_previewed && ! empty( $data['prev_lesson_url'] ) ) ) {
				STM_LMS_Templates::show_lms_template(
					'components/nav-button',
					array(
						'title'     => __( 'Previous', 'masterstudy-lms-learning-management-system' ),
						'type'      => 'prev',
						'link'      => $data['prev_lesson_url'],
						'style'     => 'secondary',
						'dark_mode' => $dark_mode,
						'data'      => array(),
					)
				);
			}
			?>
		</div>
		<?php if ( 'completed' === $data['completed'] ) { ?>
			<div class="masterstudy-course-player-navigation__status">
				<?php echo esc_html__( 'Completed', 'masterstudy-lms-learning-management-system' ); ?>
			</div>
			<?php if ( ( 'quiz' === $lesson_type || $data['is_assignment'] ) && empty( $data['next_lesson'] ) ) { ?>
				<div class="masterstudy-course-player-navigation__next"></div>
				<?php
			}
		} elseif ( $has_access && 'quiz' === $lesson_type && 'completed' !== $data['completed'] && ! empty( $current_user['id'] ) ) {
			?>
			<div class="masterstudy-course-player-navigation__submit-quiz masterstudy-course-player-navigation__submit-quiz_hide <?php echo esc_attr( empty( $data['next_lesson'] ) ? 'masterstudy-course-player-navigation__submit-quiz_last' : '' ); ?>">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'title' => __( 'Submit', 'masterstudy-lms-learning-management-system' ),
						'type'  => '',
						'link'  => '#',
						'style' => 'primary',
						'size'  => 'sm',
						'id'    => 'submit-quiz',
						'icon'  => '',
					)
				);
				?>
			</div>
			<?php
		} elseif ( $has_access && $data['is_assignment'] && ! empty( $current_user['id'] ) ) {
			?>
			<div class="masterstudy-course-player-navigation__send-assignment <?php echo esc_attr( empty( $data['next_lesson'] ) ? 'masterstudy-course-player-navigation__send-assignment_last' : '' ); ?>">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/button',
					array(
						'id'    => 'masterstudy-course-player-assignments-send-button',
						'title' => __( 'Submit', 'masterstudy-lms-learning-management-system' ),
						'link'  => '#',
						'icon'  => '',
						'style' => 'primary',
						'size'  => 'sm',
					)
				);
				?>
			</div>
			<?php
		}

		if ( $has_access ) {
			if ( 'uncompleted' === $data['completed'] && ! empty( $data['next_lesson'] ) ) {
				if ( ! empty( $current_user['id'] ) ) {
					$buttont_title    = __( 'Complete & Next', 'masterstudy-lms-learning-management-system' );
					$button_style     = 'primary';
					$button_id        = 'masterstudy-course-player-lesson-submit';
					$next_lesson_data = array(
						'course' => $post_id,
						'lesson' => $item_id,
					);
				}
				if ( 'assignments' === $lesson_type || 'quiz' === $lesson_type || $lesson_lock_before_start || empty( $current_user['id'] ) ) {
					$buttont_title    = __( 'Next', 'masterstudy-lms-learning-management-system' );
					$button_style     = 'secondary';
					$button_id        = 'masterstudy-course-player-lesson-next';
					$next_lesson_data = array();
				}

				if ( ! $lesson_locked_by_drip ) {
					?>
					<div class="masterstudy-course-player-navigation__next">
						<?php
						STM_LMS_Templates::show_lms_template(
							'components/nav-button',
							array(
								'title'     => $buttont_title,
								'id'        => $button_id,
								'type'      => 'next',
								'link'      => $data['next_lesson_url'],
								'style'     => $button_style,
								'dark_mode' => $dark_mode,
								'data'      => $next_lesson_data,
							)
						);
						?>
					</div>
					<?php
				}
			} elseif ( 'uncompleted' === $data['completed'] && empty( $data['next_lesson'] ) && ! $lesson_lock_before_start
					&& ! $lesson_locked_by_drip && 'assignments' !== $lesson_type && 'quiz' !== $lesson_type && ! empty( $current_user['id'] ) ) {
				?>
				<div class="masterstudy-course-player-navigation__next">
					<?php
					STM_LMS_Templates::show_lms_template(
						'components/nav-button',
						array(
							'title'     => __( 'Complete', 'masterstudy-lms-learning-management-system' ),
							'id'        => 'masterstudy-course-player-lesson-submit',
							'type'      => 'next',
							'link'      => '',
							'style'     => 'primary',
							'dark_mode' => $dark_mode,
							'data'      => array(
								'course' => $post_id,
								'lesson' => $item_id,
							),
						)
					);
					?>
				</div>
				<?php
			}
		}

		if ( ! empty( $data['next_lesson'] ) && 'completed' === $data['completed'] && $has_access || $is_previewed ) {
			?>
			<div class="masterstudy-course-player-navigation__next">
				<?php
				STM_LMS_Templates::show_lms_template(
					'components/nav-button',
					array(
						'title'     => __( 'Next', 'masterstudy-lms-learning-management-system' ),
						'type'      => 'next',
						'link'      => $data['next_lesson_url'],
						'style'     => 'secondary',
						'dark_mode' => $dark_mode,
						'data'      => array(),
					)
				);
				?>
			</div>
			<?php
		}
		?>
	</div>
</div>
