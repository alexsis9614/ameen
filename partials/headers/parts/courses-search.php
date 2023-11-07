<?php

    if ( class_exists( 'STM_LMS_Course' ) ) :
        wp_enqueue_script('vue-resource.js');
        stm_module_styles('vue-autocomplete', 'vue2-autocomplete');
        stm_module_scripts('vue-autocomplete', 'vue2-autocomplete', array());
        stm_module_scripts('courses_search', 'courses_search');
?>

    <script>
        var stm_lms_search_value = '<?php echo (!empty($_GET['search'])) ? sanitize_text_field($_GET['search']) : ''; ?>';
    </script>

    <div class="stm_lms_courses_search vue_is_disabled" id="stm_lms_courses_search" v-bind:class="{'is_vue_loaded' : vue_loaded}">
        {{ search }}
        <form autocomplete="off">
            <span class="search-courses-input--icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.7559 14.255H14.9659L14.6859 13.985C15.6659 12.845 16.2559 11.365 16.2559 9.75501C16.2559 6.16501 13.3459 3.255 9.75586 3.255C6.16586 3.255 3.25586 6.16501 3.25586 9.75501C3.25586 13.345 6.16586 16.255 9.75586 16.255C11.3659 16.255 12.8459 15.665 13.9859 14.685L14.2559 14.965V15.755L19.2559 20.745L20.7459 19.255L15.7559 14.255ZM9.75586 14.255C7.26586 14.255 5.25586 12.245 5.25586 9.75501C5.25586 7.26501 7.26586 5.25501 9.75586 5.25501C12.2459 5.25501 14.2559 7.26501 14.2559 9.75501C14.2559 12.245 12.2459 14.255 9.75586 14.255Z" fill="#97999D"/>
                </svg>
            </span>

            <autocomplete
                    name="search"
                    placeholder="<?php esc_attr_e('General search', 'masterstudy-child'); ?>"
                    url="<?php echo esc_url( rest_url('stm-lms/v1/courses', 'json') ); ?>"
                    param="search"
                    anchor="value"
                    label="label"
                    id="search-courses-input"
                    :on-select="searchCourse"
                    :on-input="searching"
                    :on-ajax-loaded="loaded"
                    :debounce="1000"
                    model="search">
            </autocomplete>

            <a v-bind:href="'<?php echo esc_url(STM_LMS_Course::courses_page_url()) ?>?search=' + url"
               class="btn btn-default stm_lms_courses_search__button sbc">
                <span><?php esc_html_e('Search', 'masterstudy-child'); ?></span>
            </a>
        </form>
    </div>

<?php endif;