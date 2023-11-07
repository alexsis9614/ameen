<?php
    /**
     * @args
     */

    stm_module_styles( 'lms_categories_megamenu', 'style_1' );

    if ( ! empty( $args ) ) {
        extract( $args );
    }

    $tax_args = array(
        'taxonomy'   => 'stm_lms_course_taxonomy',
        'hide_empty' => false,
        'parent'     => 0,
        'number'     => stm_option( 'course_categories_limit', 10 ),
    );

    $parent_terms = get_terms( $tax_args );

    if ( ! empty( $parent_terms ) && ! is_wp_error( $parent_terms ) ) :
?>
	<div class="stm_lms_categories">
		<i class="stmlms-hamburger"></i>
		<span class="stm_lms_categories--title"><?php esc_html_e( 'Catalog', 'masterstudy-child' ); ?></span>

		<div class="stm_lms_categories_dropdown vue_is_disabled">

			<div class="stm_lms_categories_dropdown__parents">
				<?php
                    foreach ( $parent_terms as $term ) :
                        $parent_id   = $term->term_id;
                        $child_terms = get_terms(
                            array(
                                'taxonomy'   => 'stm_lms_course_taxonomy',
                                'hide_empty' => false,
                                'parent'     => $parent_id,
                            )
                        );
				?>
					<div class="stm_lms_categories_dropdown__parent 
                        <?php
                            if ( empty( $child_terms ) ) {
                                echo 'no-child';
                            }
                        ?>
					">
						<a href="<?php echo esc_url( get_term_link( $term ) ); ?>" class="sbc_h">
							<?php echo esc_html( $term->name ); ?>
						</a>
						<?php if ( ! empty( $child_terms ) ) : ?>
							<div class="stm_lms_categories_dropdown__childs">
								<div class="stm_lms_categories_dropdown__childs_container">
									<?php foreach ( $child_terms as $child_term ) : ?>
										<div class="stm_lms_categories_dropdown__child">
											<a href="<?php echo esc_url( get_term_link( $child_term ) ); ?>">
												<?php echo esc_html( $child_term->name ); ?>
											</a>
										</div>
									<?php endforeach; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>

		</div>

	</div>

	<?php
endif;
