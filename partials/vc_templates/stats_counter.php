<?php
    /**
     * @var $id
     * @var $title
     * @var $counter_value
     * @var $duration
     * @var $icon
     * @var $icon_size
     * @var $css
     * @var $css_class
     * @var $prefix
     * @var $suffix
     */

    $icon_url = '';

    if ( is_array( $icon ) ) {
        $icon_url = $icon[ 'url' ];
        $icon     = false;
    }
    else {
        $icon = ( isset( $icon ) && ! empty( trim( $icon ) ) ) ? $icon : '';
    }

    $icon_style = '';

    if ( ! empty( $icon_size ) ) {
        foreach ( $icon_size as $icon_key => $icon_item ) {
            if ( empty( $icon_item ) ) continue;

            $icon_style .= esc_attr( $icon_key ) . ': ' . esc_attr( $icon_item ) . 'px;';
        }

        if ( ! empty( $icon_style ) ) {
            $icon_style = "style='" . $icon_style . "'";
        }
    }
?>

<div class="stats_counter<?php echo esc_attr( $css_class ); ?>"
     data-id="<?php echo esc_attr( $id ); ?>"
     data-value="<?php echo esc_attr( $counter_value ); ?>"
     data-suffix="<?php echo esc_attr( $suffix ); ?>"
     data-prefix="<?php echo esc_attr( $prefix ); ?>"
     data-duration="<?php echo esc_attr( $duration ); ?>">

    <div class="icon" <?php echo $icon_style; ?>>
        <?php if ( $icon ) : ?>
            <i style="font-size: <?php echo esc_attr( $icon_size ); ?>px;" class="fa <?php echo esc_attr( $icon ); ?>"></i>
        <?php else : ?>
            <img src="<?php echo esc_attr( $icon_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
        <?php endif; ?>
    </div>

    <div class="h1" id="<?php echo esc_attr( $id ); ?>">
        <?php echo esc_attr( $prefix . $counter_value . $suffix ); ?>
    </div>

	<?php if ( $title ) : ?>
		<div class="stats_counter_title h5">
            <?php echo sanitize_text_field( $title ); ?>
        </div>
	<?php endif; ?>

</div>
