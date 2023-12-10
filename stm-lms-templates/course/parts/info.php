<?php
/**
 * @var $course_id
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

use MasterStudy\Lms\Repositories\CurriculumSectionRepository;
use MasterStudy\Lms\Repositories\CurriculumMaterialRepository;

stm_lms_register_style( 'course_info' );

$meta            = STM_LMS_Helpers::parse_meta_field( $course_id );
$section_ids     = ( new CurriculumSectionRepository() )->get_course_section_ids( $course_id );
$lessons_count   = ( new CurriculumMaterialRepository() )->count_by_type( $section_ids, 'stm-lessons' );
$meta_fields     = array();

if ( ! empty( $meta['current_students'] ) ) {
    $meta_fields[ esc_html__( 'Enrolled', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => sprintf( _n( '%s student', '%s students', $meta['current_students'], 'masterstudy-lms-learning-management-system' ), $meta['current_students'] ),
        'icon' => 'fa-icon-stm_icon_users',
    );
} else {
    $meta_fields[ esc_html__( 'Enrolled', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => sprintf( _n( '%s student', '%s students', 0, 'masterstudy-lms-learning-management-system' ), 0 ),
        'icon' => 'fa-icon-stm_icon_users',
    );
}

if ( ! empty( $meta['duration_info'] ) ) {
    $meta_fields[ esc_html__( 'Duration', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => $meta['duration_info'],
        'icon' => 'fa-icon-stm_icon_clock',
    );
}

if ( ! empty( $lessons_count ) ) {
    $meta_fields[ esc_html__( 'Lectures', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => $lessons_count,
        'icon' => 'fa-icon-stm_icon_bullhorn',
    );
}

if ( ! empty( $meta['video_duration'] ) ) {
    $meta_fields[ esc_html__( 'Video', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => $meta['video_duration'],
        'icon' => 'fa-icon-stm_icon_film-play',
    );
}

if ( ! empty( $meta['level'] ) ) {
    $levels = STM_LMS_Helpers::get_course_levels();

    $meta_fields[ esc_html__( 'Level', 'masterstudy-lms-learning-management-system' ) ] = array(
        'text' => $levels[ $meta['level'] ],
        'icon' => 'lnricons-chart-growth',
    );
}

function stm_lms_course_single_icon( $icon_name )
{
    if ( 'fa-icon-stm_icon_users' === $icon_name ) {
        $icon = '
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7.49935 11.4596C5.54935 11.4596 1.66602 12.4346 1.66602 14.3763L1.66602 15.8346H13.3327V14.3763C13.3327 12.4346 9.44935 11.4596 7.49935 11.4596ZM3.61602 14.168C4.31602 13.6846 6.00768 13.1263 7.49935 13.1263C8.99102 13.1263 10.6827 13.6846 11.3827 14.168H3.61602ZM7.49935 10.0013C9.10768 10.0013 10.416 8.69297 10.416 7.08464C10.416 5.4763 9.10768 4.16797 7.49935 4.16797C5.89102 4.16797 4.58268 5.4763 4.58268 7.08464C4.58268 8.69297 5.89102 10.0013 7.49935 10.0013ZM7.49935 5.83464C8.19102 5.83464 8.74935 6.39297 8.74935 7.08464C8.74935 7.7763 8.19102 8.33464 7.49935 8.33464C6.80768 8.33464 6.24935 7.7763 6.24935 7.08464C6.24935 6.39297 6.80768 5.83464 7.49935 5.83464ZM13.366 11.5096C14.3327 12.2096 14.9993 13.143 14.9993 14.3763V15.8346H18.3327V14.3763C18.3327 12.693 15.416 11.7346 13.366 11.5096ZM12.4993 10.0013C14.1077 10.0013 15.416 8.69297 15.416 7.08464C15.416 5.4763 14.1077 4.16797 12.4993 4.16797C12.0493 4.16797 11.6327 4.2763 11.2493 4.45964C11.7743 5.2013 12.0827 6.10964 12.0827 7.08464C12.0827 8.05964 11.7743 8.96797 11.2493 9.70963C11.6327 9.89297 12.0493 10.0013 12.4993 10.0013Z" fill="#707070"/>
            </svg>
        ';
    }
    else if ( 'fa-icon-stm_icon_clock' === $icon_name ) {
        $icon = '
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M9.99102 1.66797C5.39102 1.66797 1.66602 5.4013 1.66602 10.0013C1.66602 14.6013 5.39102 18.3346 9.99102 18.3346C14.5993 18.3346 18.3327 14.6013 18.3327 10.0013C18.3327 5.4013 14.5993 1.66797 9.99102 1.66797ZM9.99935 16.668C6.31602 16.668 3.33268 13.6846 3.33268 10.0013C3.33268 6.31797 6.31602 3.33464 9.99935 3.33464C13.6827 3.33464 16.666 6.31797 16.666 10.0013C16.666 13.6846 13.6827 16.668 9.99935 16.668ZM10.416 5.83464H9.16602V10.8346L13.541 13.4596L14.166 12.4346L10.416 10.2096V5.83464Z" fill="#707070"/>
            </svg>
        ';
    }
    else if ( 'fa-icon-stm_icon_bullhorn' === $icon_name ) {
        $icon = '
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M2.5 7.49974L2.5 12.4997H5.83333L10 16.6664V3.33307L5.83333 7.49974H2.5ZM8.33333 7.35807V12.6414L6.525 10.8331H4.16667V9.16641H6.525L8.33333 7.35807ZM13.75 9.99974C13.75 8.52474 12.9 7.25807 11.6667 6.64141V13.3497C12.9 12.7414 13.75 11.4747 13.75 9.99974ZM11.6667 2.69141V4.40807C14.075 5.12474 15.8333 7.35807 15.8333 9.99974C15.8333 12.6414 14.075 14.8747 11.6667 15.5914V17.3081C15.0083 16.5497 17.5 13.5664 17.5 9.99974C17.5 6.43307 15.0083 3.44974 11.6667 2.69141Z" fill="#707070"/>
            </svg>
        ';
    }
    else if ( 'fa-icon-stm_icon_film-play' === $icon_name ) {
        $icon = '
            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M8.33268 13.7513L13.3327 10.0013L8.33268 6.2513V13.7513ZM9.99935 1.66797C5.39935 1.66797 1.66602 5.4013 1.66602 10.0013C1.66602 14.6013 5.39935 18.3346 9.99935 18.3346C14.5993 18.3346 18.3327 14.6013 18.3327 10.0013C18.3327 5.4013 14.5993 1.66797 9.99935 1.66797ZM9.99935 16.668C6.32435 16.668 3.33268 13.6763 3.33268 10.0013C3.33268 6.3263 6.32435 3.33464 9.99935 3.33464C13.6743 3.33464 16.666 6.3263 16.666 10.0013C16.666 13.6763 13.6743 16.668 9.99935 16.668Z" fill="#707070"/>
            </svg>
        ';
    }
    else {
        $icon = '<i class="'. esc_html( $icon_name ) .'"></i>';
    }

    return $icon;
}

if ( ! empty( $meta_fields ) ) : ?>
    <div class="stm-lms-course-info heading_font">
        <?php foreach ( $meta_fields as $meta_field_key => $meta_field ) : ?>
            <div class="stm-lms-course-info__single">
                <div class="stm-lms-course-info__single_icon">
                    <?php echo stm_lms_course_single_icon( $meta_field['icon'] ); ?>
                </div>
                <div class="stm-lms-course-info__single_label">
                    <span><?php echo esc_html( $meta_field_key ); ?></span>:
                    <strong><?php echo esc_html( $meta_field['text'] ); ?></strong>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else : ?>
    <div class="stm-lms-course-info">
        <div class="stm-lms-course-info__single"></div>
    </div>
<?php
endif;