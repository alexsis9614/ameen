<?php
    if ( ! current_user_can( 'list_users' ) ){
        wp_die( __( 'Cheatin&#8217; uh?', 'masterstudy-child' ), 403 );
    }

    require_once ABSPATH . 'wp-admin/includes/class-wp-users-list-table.php';
    require_once STM_THEME_CHILD_DIRECTORY . '/inc/classes/STM_History_Table.php';

    $wp_list_table = new LMS\inc\classes\STM_History_Table( array() );

    $pagenum       = $wp_list_table->get_pagenum();
    $wp_list_table->prepare_items();

    $total_pages   = $wp_list_table->get_pagination_arg( 'total_pages' );

    if ( $pagenum > $total_pages && $total_pages > 0 ) {
        wp_redirect( add_query_arg( 'paged', $total_pages ) );
        exit;
    }
?>

<div class="wrap">
    <h2> <?php echo get_admin_page_title() ?> </h2>

    <form method="get" action="">
        <input type="hidden" name="page" value="<?php echo ( isset( $_REQUEST['page'] ) ) ? esc_attr( $_REQUEST['page'] ) : ''; ?>" />

        <?php $wp_list_table->search_box( __( 'Search Users', 'stm-volunteer-management' ), 'user' ); ?>

        <?php $wp_list_table->display(); ?>
    </form>

    <br class="clear" />
</div>