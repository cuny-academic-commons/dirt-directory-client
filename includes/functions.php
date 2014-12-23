<?php

/**
 * Register assets.
 */
function ddc_register_assets() {
	wp_register_style( 'dirt-directory-client', DDC_PLUGIN_URL . 'assets/css/screen.css' );
	wp_register_script( 'dirt-directory-client', DDC_PLUGIN_URL . 'assets/js/ddc.js', array( 'jquery' ) );

	wp_localize_script( 'dirt-directory-client', 'DDC', array(
		'add_gloss' => __( 'Click to show that you use this tool', 'dirt-directory-client' ),
		'remove_gloss' => __( 'Click to remove this tool from your list', 'dirt-directory-client' ),
	) );
}
add_action( 'init', 'ddc_register_assets', 0 );

/**
 * Get a local tool object by either the local ID or the remote NID.
 */
function ddc_get_tool_by_identifier( $tool_id = false, $tool_node_id = false ) {
	$tool = false;

	if ( ! empty( $tool_id ) ) {
		$tool = get_post( $tool_id );

	// When adding tools, we look up by nid, in case a new tool
	// has to be created
	} else {
		$tool = ddc_get_tool( 'node_id', $tool_node_id );
		if ( empty( $tool ) ) {
			$c = new DiRT_Directory_Client();
			$tool_data = $c->get_item_by_node_id( $tool_node_id );
			if ( ! empty( $tool_data ) ) {
				$tool_id = ddc_create_tool( array(
					'title'     => $tool_data->title,
					'link'      => $tool_data->path,
					'node_id'   => $tool_node_id,
					'thumbnail' => $tool_data->node->field_logo->und[0]->filename,
					'image'     => $tool_data->node->field_logo->und[0]->uri,
				) );

				$tool = get_post( $tool_id );
			}
		}
	}

	return $tool;
}
