<?php

/**
 * Integration with BP Better Directories.
 */

/**
 * Add DiRT fields to the filter form.
 */
function ddc_bpbd_filterable_fields( $fields ) {
	$fields['dirt-tools'] = array(
		'id' => 'dirt-tools',
		'name' => __( 'DiRT Tools', 'dirt-directory-client' ),
		'type' => 'checkbox',
		'slug' => 'dirt-tools',
	);

	return $fields;
}
add_action( 'bpbd_filterable_fields', 'ddc_bpbd_filterable_fields' );

/**
 * Filter user queries as necessary.
 *
 * We'll convert to an 'include' array.
 */
function ddc_bpbd_filter_user_query( BP_User_Query $bp_user_query ) {
	if ( empty( $_GET['bpbd-filter-dirt-tools'] ) ) {
		return;
	}

	foreach ( (array) $_GET['bpbd-filter-dirt-tools'] as $tool_id ) {
		$tool_id = intval( $tool_id );

		// Skipping the DDC function because we don't need the extra info.
		$user_terms = wp_get_object_terms( $tool_id, 'ddc_tool_is_used_by_user' );
		$user_ids = array();
		foreach ( $user_terms as $user_term ) {
			$user_ids[] = ddc_get_user_id_from_usedby_term_slug( $user_term->slug );
		}
	}

	if ( ! empty( $user_ids ) ) {
		$include = $bp_user_query->query_vars['include'];

		if ( ! empty( $include ) ) {
			$include = (array) $include;
			$bp_user_query->query_vars['include'] = array_intersect( $user_ids, $include );
		} else {
			$bp_user_query->query_vars['include'] = $user_ids;
		}
	}
}
add_action( 'bp_pre_user_query_construct', 'ddc_bpbd_filter_user_query' );

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
