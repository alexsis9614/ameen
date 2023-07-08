<div class="section_items" v-if="section.opened">

    <section_items inline-template
                   :items="section.items">

        <draggable :list="items"
                   v-bind:class="'count_' + items.length"
                   class="dragArea items"
                   @start="startDrag"
                   @end="endDrag"
                   handle=".item_move"
                   :options="{ group: 'member', dragoverBubble: true }">

            <?php
                if( class_exists( 'STM_THEME_CHILD_Curriculum' ) ) {
                    STM_THEME_CHILD_Curriculum::curriculum_load_template('item');
                }
                else {
                    stm_lms_curriculum_v2_load_template('item');
                }
            ?>

        </draggable>

    </section_items>

</div>