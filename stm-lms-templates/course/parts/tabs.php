<?php
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    } //Exit if accessed directly

    /**
     * @var $tabs
     * @var $tabs_length
     * @var $active
    */

    if ( $tabs_length > 0 ) :
?>

    <div class="tab-content">
        <?php foreach ( $tabs as $slug => $name ) : ?>
            <div role="tabpanel"
                class="tab-pane <?php echo ( $slug === $active ) ? 'active' : ''; ?>"
                id="<?php echo esc_attr( $slug ); ?>">
                <?php STM_LMS_Templates::show_lms_template( 'course/parts/tabs/' . $slug ); ?>
            </div>
        <?php endforeach; ?>
    </div>

<?php endif; ?>