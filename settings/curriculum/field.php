<?php
    /**
     * @var $field
     * @var $field_id
     * @var $field_value
     * @var $field_label
     * @var $field_name
     * @var $section_name
     *
     */

    wp_enqueue_script('stm-lms-curriculum', STM_THEME_CHILD_DIRECTORY_URI . '/assets/js/curriculum.js', null, STM_THEME_CHILD_VERSION);
//    wp_enqueue_script('stm-lms-curriculum', STM_LMS_URL . '/settings/curriculum/js/curriculum.js', null, stm_lms_custom_styles_v());
    wp_enqueue_script('stm-lms-curriculum-add-item', STM_LMS_URL . '/settings/curriculum/js/add_item.js',null,  stm_lms_custom_styles_v());
    wp_enqueue_script('stm-lms-curriculum-search', STM_LMS_URL . '/settings/curriculum/js/search.js', null,  stm_lms_custom_styles_v());
    stm_lms_register_style('admin/curriculum_v2');
    wp_localize_script('stm-lms-curriculum', 'stm_lms_manage_course_id', ( isset( $_REQUEST['post'] ) && ! empty( $_REQUEST['post'] ) ) ? $_REQUEST['post'] : '');
?>

    <curriculum inline-template
                v-on:curriculum_changed="<?php echo esc_attr($field_value); ?> = $event"
                v-bind:curriculum_saved="<?php echo esc_attr($field_value); ?>">

        <div class="stm_lms_curriculum_v2_wrapper" v-bind:class="{'loaded' : loaded, 'dragging' : onDrag}">

            <div v-if="loading">
                <?php esc_html_e('Loading curriculum...', 'masterstudy-lms-learning-management-system'); ?>
            </div>

            <div class="stm_lms_curriculum_v2" v-else>

                <draggable :list="sections"
                           class="sections dragArea"
                           :options="{ group: 'section'}" @start="startDrag"
                           handle=".section_move"
                           @end="endDrag">

                    <div class="section"
                         v-for="(section, section_key) in sections"
                         v-bind:class="{'hovered' : section.hovered}">

                        <?php stm_lms_curriculum_v2_load_template('section_data'); ?>

                        <?php
                            if ( class_exists( 'STM_THEME_CHILD_Curriculum' ) ) {
                                STM_THEME_CHILD_Curriculum::curriculum_load_template('section_items');
                            }
                            else {
                                stm_lms_curriculum_v2_load_template( 'section_items' );
                            }
                        ?>

                        <?php stm_lms_curriculum_v2_load_template('add_items'); ?>

                    </div>

                </draggable>

                <?php stm_lms_curriculum_v2_load_template('add_section'); ?>

                <?php if (!empty($field_name)) : ?>

                        <input type="hidden"
                               name="<?php echo esc_attr($field_name . '_plans'); ?>"
                               v-bind:placeholder="<?php echo esc_attr($field_label); ?>"
                               v-bind:id="'<?php echo esc_attr($field_id . '_plans'); ?>'"
                               v-model="<?php echo esc_attr('plans'); ?>"/>

                <?php endif; ?>

            </div>

        </div>

    </curriculum>

<?php if (!empty($field_name)) : ?>
    <!-- Here We store actual value in hidden input -->
    <!-- Mostly it needed for metabox area, where WordPress saves field automatically after post update -->
    <input type="hidden"
           name="<?php echo esc_attr($field_name); ?>"
           v-bind:placeholder="<?php echo esc_attr($field_label); ?>"
           v-bind:id="'<?php echo esc_attr($field_id); ?>'"
           v-model="<?php echo esc_attr($field_value); ?>"/>

<?php endif;