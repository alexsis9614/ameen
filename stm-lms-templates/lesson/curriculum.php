<?php
    /**
     * @var $post_id
     * @var $item_id
     * @var $lesson_type
     */

    use \MasterStudy\Lms\Repositories\CurriculumRepository;

    stm_lms_register_style( 'curriculum' );
    stm_lms_register_script( 'curriculum' );

    $post_id       = ( ! empty( $post_id ) ) ? $post_id : get_the_ID();
    $curriculum    = ( new CurriculumRepository() )->get_curriculum( $post_id, true );
    $lesson_style  = STM_LMS_Options::get_option( 'lesson_style', 'default' );
    $_user_id      = get_current_user_id();
    $_plans_object = new Lms\inc\classes\STM_Plans;

    if ( $author_id = get_post_field( 'post_author', $post_id ) ) {
        $author_id = absint( $author_id );
    }
?>
<div class="stm-curriculum__close">
	<i class="lnr lnr-cross"></i>
</div>
<div class="stm-curriculum">
	<h3 class="stm-curriculum__title">
		<?php esc_html_e( 'Course sections', 'masterstudy-child' ); ?>

		<?php if ( 'classic' === $lesson_style ) { ?>
            <div class="stm-lms-curriculum-trigger">
                <i class="fa fa-list-ul"></i>
            </div>
		<?php } ?>
	</h3>
	<?php
        if ( ! empty( $curriculum ) ) {
            foreach ( $curriculum as $index => $section ) {
                $opened = in_array( $item_id, array_column( $section['materials'], 'post_id' ), true ) ? 'opened' : '';
	?>
			<div class="stm-curriculum-section <?php echo esc_attr( $opened ); ?>">
				<div class="stm-curriculum-item stm-curriculum-item__section <?php echo esc_attr( $opened ); ?>">
					<div class="stm-curriculum-section__info">
						<span><?php printf( esc_html__( 'Section %s', 'masterstudy-child' ), esc_html( ++$index ) ); ?></span>
						<?php if ( ! empty( $section['title'] ) ) : ?>
							<h5><?php echo wp_kses_post( $section['title'] ); ?></h5>
						<?php endif; ?>
					</div>
				</div>
				<div class="stm-curriculum-section__lessons">
					<?php
					foreach ( $section['materials'] as $material_index => $material ) {
						$lesson_id          = stm_lms_get_wpml_binded_id( $material['post_id'] );
						$meta               = '';
						$icon               = 'stmlms-text';
						$type               = 'lesson';
						$quiz_info          = array();
						$previous_completed = ( isset( $completed ) ) ? $completed : 'first';

                        if ( ( ! STM_LMS_Instructor::is_instructor( $_user_id ) || $_user_id !== $author_id ) && $_plans_object->enable( $post_id ) ) {
                            $user_plan   = Lms\inc\classes\STM_Plans::get_user_meta_key( $_user_id, $post_id );
                            $course_plan = Lms\inc\classes\STM_Plans::get_curriculum_meta_key( $lesson_id, $user_plan );

                            if ( empty( $course_plan ) ) {
                                continue;
                            }
                        }

						if ( 'stm-quizzes' === $material['post_type'] ) {
							$type      = 'quiz';
							$quiz_info = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_quizzes( $_user_id, $lesson_id, array( 'progress' ) ) );
							$completed = ( STM_LMS_Quiz::quiz_passed( $lesson_id ) ) ? 'completed' : '';
							$icon      = 'stmlms-quiz';
						} else {
							$meta      = get_post_meta( $lesson_id, 'duration', true );
							$completed = ( STM_LMS_Lesson::is_lesson_completed( '', $post_id, $lesson_id ) ) ? 'completed' : '';
							$type      = get_post_meta( $lesson_id, 'type', true );
							if ( 'slide' === $type || 'video' === $type ) {
								$icon = 'stmlms-slides';
							}
							if ( 'zoom_conference' === $type ) {
								$icon = 'fas fa-video';
							}
							if ( 'stream' === $type ) {
								$icon = 'fab fa-youtube';
							}
						}
						$item_classes   = array( 'stm-curriculum-item' );
						$item_classes[] = $type;
						$item_classes[] = "is-{$completed}";
						$item_classes[] = apply_filters( 'stm_lms_prev_status', "{$previous_completed}", $post_id, $lesson_id, $_user_id );

						if ( $lesson_id === $item_id ) {
							$item_classes[] = 'active';
						}
					?>
						<a href="<?php echo esc_url( STM_LMS_Lesson::get_lesson_url( $post_id, $lesson_id ) ); ?>"
							class="<?php echo esc_attr( implode( ' ', $item_classes ) ); ?>">
							<div class="stm-curriculum-item__icon">
								<i class="<?php echo esc_attr( $icon ); ?>"></i>
							</div>
							<div class="stm-curriculum-item__num">
								<?php echo esc_html( ++$material_index ); ?>
							</div>
							<div class="stm-curriculum-item__title">
								<div class="heading_font">
									<?php echo esc_html( $material['title'] ); ?>
								</div>
							</div>
							<div class="stm-curriculum-item__meta">
								<?php
                                    if ( ! empty( $quiz_info['progress'] ) ) {
                                        echo intval( $quiz_info['progress'] );
                                ?>
                                        %
                                <?php
                                    } else {
                                        $questions = get_post_meta( $lesson_id, 'questions', true );
                                        if ( ! empty( $questions ) ) {
                                            $questions_array = explode( ',', $questions );
                                            if ( ! empty( $questions_array ) ) {
                                                /* translators: %s: number of questions */
                                                echo sprintf( esc_html__( '%s questions', 'masterstudy-child' ), count( $questions_array ) );
                                            }
                                        }
                                    }
                                    if ( 'classic' === $lesson_style && 'stream' !== $lesson_type && 'zoom_conference' !== $lesson_type && ! empty( $meta ) ) {
                                        echo esc_html( $meta );
                                    }
								?>
							</div>
							<?php
                                if ( ! isset( $_user_id ) ) {
                                    $_user_id = 0;
                                }
                                echo wp_kses_post(
                                    apply_filters(
                                        'stm_lms_curriculum_item_status',
                                        '
                                            <div class="stm-curriculum-item__completed ' . esc_attr( $completed ) . '">
                                                <i class="fa fa-check"></i>
                                            </div>
                                        ',
                                        $previous_completed,
                                        $post_id,
                                        $lesson_id,
                                        $_user_id
                                    )
                                );
							?>
						</a>
					<?php
					    }
					?>
				</div>
			</div>
		<?php
		    }

            if ( is_user_logged_in() && 'classic' === $lesson_style && 'stream' !== $lesson_type && 'zoom_conference' !== $lesson_type ) {
                $progress    = 0;
                $my_progress = STM_LMS_Helpers::simplify_db_array( stm_lms_get_user_course( $_user_id, $post_id, array( 'progress_percent' ) ) );
                if ( ! empty( $my_progress['progress_percent'] ) ) {
                    $progress = $my_progress['progress_percent'];
                }
                if ( $progress > 100 ) {
                    $progress = 100;
                }
		?>
			<div class="course-progress">
				<h3 class="course-progress__title">
					<?php esc_html_e( 'Your progress', 'masterstudy-child' ); ?>
				</h3>
				<div class="course-progress__bar">
					<div class="progress_line">
						<div class="past primary_bg_color" style="width: <?php echo esc_attr( $progress ); ?>%"></div>
					</div>
					<div class="progress_percent primary_bg_color heading_font">
						<?php echo esc_html( $progress ); ?>%
					</div>
				</div>
			</div>
	<?php
            }
        }
	?>
</div>
