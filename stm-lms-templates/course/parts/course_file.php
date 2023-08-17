<?php
    /**
     *
     * @var $id
     */

    $plans        = new LMS\inc\classes\STM_Plans;
    $plans_enable = $plans->enable( $id );

    if ( $plans_enable ) {
        $has_course   = STM_LMS_User::has_course_access( get_the_ID(), 0, false );

        if ( ! $has_course ) {
            return;
        }
    }

    if ( ! empty( $id ) ) {
        STM_LMS_Templates::show_lms_template('global/files',
            array(
                'item_id' => $id,
                'pack_name' => 'course_files_pack',
                'file_in_pack' => 'course_files',
                'file_in_pack_name' => 'course_files_label',
            )
        );
    }