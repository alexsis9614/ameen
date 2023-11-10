<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    do_action( 'stm_lms_single_course_start', get_the_ID() );

    stm_lms_register_style( 'course' );

    do_action( 'stm_lms_custom_content_for_single_course' );

    get_template_part( 'partials/breadcrumb' );

    STM_LMS_Templates::show_lms_template(
        'global/completed_label',
        array('course_id' => get_the_ID())
    );

    $tabs = array(
        'description'  => esc_html__( 'Course description', 'masterstudy-child' ),
//        'curriculum'   => esc_html__( 'Curriculum', 'masterstudy-lms-learning-management-system' ),
//        'faq'          => esc_html__( 'FAQ', 'masterstudy-lms-learning-management-system' ),
//        'announcement' => esc_html__( 'Announcement', 'masterstudy-lms-learning-management-system' ),
        'reviews'      => esc_html__( 'Reviews', 'masterstudy-lms-learning-management-system' ),
    );
    $tabs = apply_filters( 'stm_lms_course_tabs', $tabs, get_the_ID() );

    $active      = array_search( reset( $tabs ), $tabs, true );
    $tabs_length = count( $tabs );
?>

<div class="row">
	<div class="col-md-8">
        <div class="stm_lms_course__top">
            <h1 class="stm_lms_course__title"><?php the_title(); ?></h1>

            <?php if ( $tabs_length > 0 ) : ?>
                <div class="nav-tabs-wrapper">
                    <ul class="nav nav-tabs" role="tablist">

                        <?php foreach ( $tabs as $slug => $name ) : ?>
                            <li role="presentation" class="<?php echo ( $slug === $active ) ? 'active' : ''; ?>">
                                <a href="#<?php echo esc_attr( $slug ); ?>" role="tab" aria-controls="<?php echo esc_attr( $slug ); ?>" data-toggle="tab">
                                    <?php echo wp_kses_post( $name ); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="stm_lms_course__image">
            <?php the_post_thumbnail('img-870-440'); ?>
        </div>

        <div class="stm_lms_course__published">
            <?php echo get_the_date( 'M d Y' ) ?>
        </div>

		<?php
            STM_LMS_Templates::show_lms_template(
                'course/parts/tabs',
                array(
                    'tabs'        => $tabs,
                    'tabs_length' => $tabs_length,
                    'active'      => $active,
                )
            );

            if ( STM_LMS_Options::get_option( 'enable_related_courses', false ) ) {
                STM_LMS_Templates::show_lms_template( 'course/parts/related' );
            }
		?>
	</div>

	<div class="col-md-4">
		<?php STM_LMS_Templates::show_lms_template( 'course/sidebar' ); ?>
	</div>
</div>
<?php STM_LMS_Templates::show_lms_template( 'course/sticky/panel' ); ?>
