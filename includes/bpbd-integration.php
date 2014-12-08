<?php

/**
 * Integration with BP Better Directories.
 */

/**
 * Add DiRT fields to the filter form.
 */
function ddc_bpbd_filterable_fields( $fields ) {
	$fields['dirt'] = array(
		'id' => 'dirt-tools',
		'name' => __( 'DiRT Tools', 'dirt-directory-client' ),
		'type' => 'checkbox',
		'slug' => 'dirt-tools',
	);

	return $fields;
}
add_action( 'bpbd_filterable_fields', 'ddc_bpbd_filterable_fields' );

/**
 * Provide our own render logic for BPBD filter.
 */
function ddc_bpbd_render_field( $retval, $field, $bpbd ) {
	if ( 'dirt-tools' !== $field['id'] ) {
		return $retval;
	}

	?>
	<label for="<?php echo esc_attr( $field['slug'] ) ?>"><?php echo esc_html( $field['name'] ) ?> <span class="bpbd-clear-this"><a href="#">Clear</a></span></label>
	<?php

	$tools = ddc_get_tools_in_use( array(
		'posts_per_page' => '-1',
		'orderby' => 'title',
		'order' => 'ASC',
	) );

	$value = isset( $get_params['dirt-tools'] ) ? $get_params['dirt-tools']['value'] : false;

	?>

	<ul>
	<?php foreach ( $tools as $tool ) : ?>
		<li>
			<input type="checkbox" name="bpbd-filter-dirt-tools[]" value="<?php echo esc_attr( $tool->ID ) ?>" <?php if ( is_array( $value ) && in_array( $tool->ID, $value ) ) : ?>checked="checked"<?php endif ?>/> <?php echo esc_html( $tool->post_title ) ?>
		</li>
	<?php endforeach; ?>
	</ul>

	<?php

	return true;
}
add_filter( 'bpbd_pre_render_field', 'ddc_bpbd_render_field', 10, 3 );
