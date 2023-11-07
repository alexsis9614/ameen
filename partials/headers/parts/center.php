<?php
    if ( stm_option( 'online_show_search' ) ) :
        get_template_part('partials/headers/parts/categories');
?>
    <div class="stm_courses_search">
		<?php get_template_part('partials/headers/parts/courses-search'); ?>
    </div>
<?php endif; ?>

<?php get_template_part('partials/headers/parts/menu'); ?>
