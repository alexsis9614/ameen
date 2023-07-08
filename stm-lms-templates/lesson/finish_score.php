<?php

/**
 * @var $post_id
 * @var $item_id
 */

if ( class_exists( 'STM_THEME_CHILD_Curriculum' ) ) {
    $last_item_id = STM_THEME_CHILD_Curriculum::get_last_lesson($post_id, $item_id);
}
else {
    $last_item_id = STM_LMS_Lesson::get_last_lesson($post_id, $item_id);
}


if (!empty($last_item_id) and $item_id == $last_item_id) {
    STM_LMS_Templates::show_lms_template('lesson/finish_score_popup', compact('post_id', 'item_id'));
}