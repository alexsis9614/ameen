<div class="container">
    <div class="header_top">

        <div class="logo-unit">
            <?php get_template_part('partials/headers/parts/logo'); ?>
        </div>

        <div class="header-center-top">
            <?php get_template_part('partials/headers/parts/menu'); ?>
        </div>

        <div class="center-unit">
            <?php get_template_part('partials/headers/parts/center'); ?>
        </div>

        <div class="right-unit">
            <?php get_template_part('partials/headers/parts/right'); ?>
        </div>

    </div>
</div>

<?php
    $cats = stm_option('header_course_categories_online', array());

    if ( ! empty( $cats ) ) :
?>

    <div class="categories-courses">
		<?php get_template_part('partials/headers/parts/courses_categories_with_search'); ?>
    </div>

<?php endif; ?>
