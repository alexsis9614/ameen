<?php
    /**
     * @var $post_id
     * @var $item_id
     */

    $last_item_id = LMS\inc\classes\STM_Curriculum::get_last_lesson( $post_id );

    if ( ! empty( $last_item_id ) && absint( $item_id ) === absint( $last_item_id ) ) {
        STM_LMS_Templates::show_lms_template( 'lesson/finish_score_popup', compact( 'post_id', 'item_id' ) );
    }