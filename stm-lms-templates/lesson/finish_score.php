<?php

/**
 * @var $post_id
 * @var $item_id
 */

$last_item_id = LMS\child\classes\STM_Curriculum::get_last_lesson($post_id, $item_id);


if (!empty($last_item_id) and $item_id == $last_item_id) {
    STM_LMS_Templates::show_lms_template('lesson/finish_score_popup', compact('post_id', 'item_id'));
}