<?php
    /**
     * @var $id
     * @var $title
     * @var $counter_value
     * @var $duration
     * @var $icon
     * @var $icon_size
     * @var $icon_height
     * @var $icon_text_alignment
     * @var $icon_text_color
     * @var $counter_text_color
     * @var $text_font_size
     * @var $counter_text_font_size
     * @var $border
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

    $styles = array( "color:" . esc_attr( $counter_text_color ) );
    if( ! empty( $text_font_size ) ) {
        $styles[] = "font-size: {$text_font_size}px; line-height: {$text_font_size}px; margin-bottom: 20px;";
    }
    $style = "style='" . implode(';', $styles) . "'";

    $counter_styles = array("color:" . esc_attr($icon_text_color));
    if( ! empty( $counter_text_font_size ) ) {
        $counter_styles[] = "font-size: {$counter_text_font_size}px; line-height: {$counter_text_font_size}px";
    }
    $counter_style = "style='" . implode(';', $counter_styles) . "'";

    if( ! empty( $border ) ) {
        $css_class .= ' with_border_' . $border;
    }

    if( ! empty( $icon_width )) {
        $css_class .= ' icon_width_enabled';
    }
?>

<div class="stats_counter<?php echo esc_attr( $css_class ); ?> text-<?php echo esc_attr($icon_text_alignment); ?>"
     style="color:<?php echo esc_attr($icon_text_color); ?>"
     data-id="<?php echo esc_attr( $id ); ?>"
     data-value="<?php echo esc_attr( $counter_value ); ?>"
     data-suffix="<?php echo esc_attr( $suffix ); ?>"
     data-prefix="<?php echo esc_attr( $prefix ); ?>"
     data-duration="<?php echo esc_attr( $duration ); ?>">
	<?php if( $icon ) : ?>
		<div class="icon" style="height: <?php echo esc_attr( $icon_height ); ?>px;
            <?php if( ! empty( $icon_width ) ) echo 'width:' . esc_attr($icon_width) . 'px;'; ?>
            <?php if( ! empty( $icon_background_color ) ) echo 'background-color:' . esc_attr($icon_background_color) . ';'; ?>">
            <i style="font-size: <?php echo esc_attr( $icon_size ); ?>px;" class="fa <?php echo esc_attr( $icon ); ?>"></i>
        </div>
	<?php else : ?>
        <div class="icon" style="height: <?php echo esc_attr( $icon_height ); ?>px;
            <?php if(!empty($icon_width)) echo 'width:' . esc_attr($icon_width) . 'px;'; ?>
            <?php if(!empty($icon_background_color)) echo 'background-color:' . esc_attr($icon_background_color) . ';'; ?>">
            <img src="<?php echo esc_attr( $icon_url ); ?>" alt="<?php echo esc_attr( $title ); ?>">
        </div>
    <?php endif; ?>
	<?php if( wp_is_mobile() ) : ?>
		<div class="h1" id="<?php echo esc_attr( $id ); ?>" <?php echo sanitize_text_field( $style ); ?>>
            <?php echo esc_attr( $counter_value ); ?>
        </div>
	<?php else : ?>
		<div class="h1" id="<?php echo esc_attr( $id ); ?>" <?php echo sanitize_text_field( $style ); ?>></div>
	<?php
        endif;

        if ( $title ) :
    ?>
		<div class="stats_counter_title h5" <?php echo sanitize_text_field($counter_style); ?>>
            <?php echo sanitize_text_field( $title ); ?>
        </div>
	<?php endif; ?>
</div>
