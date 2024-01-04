<?php
/**
 * @var $lectures
 * @var $duration
 * @var $level
 */


if( ! empty( $lectures['lessons'] ) ) : ?>
	<div class="stm_lms_course__meta">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M2 8.66797H3.33333V7.33464H2V8.66797ZM2 11.3346H3.33333V10.0013H2V11.3346ZM2 6.0013H3.33333V4.66797H2V6.0013ZM4.66667 8.66797H14V7.33464H4.66667V8.66797ZM4.66667 11.3346H14V10.0013H4.66667V11.3346ZM4.66667 4.66797V6.0013H14V4.66797H4.66667ZM2 8.66797H3.33333V7.33464H2V8.66797ZM2 11.3346H3.33333V10.0013H2V11.3346ZM2 6.0013H3.33333V4.66797H2V6.0013ZM4.66667 8.66797H14V7.33464H4.66667V8.66797ZM4.66667 11.3346H14V10.0013H4.66667V11.3346ZM4.66667 4.66797V6.0013H14V4.66797H4.66667Z" fill="white"/>
        </svg>
        <?php echo esc_html( $lectures['lessons'] ); ?>
	</div>
<?php endif; ?>
