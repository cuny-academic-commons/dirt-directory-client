<?php

/**
 * Set up integration with BuddyPress.
 *
 * @since 1.0.0
 */

/**
 * Load BP integration files.
 *
 * @since 1.0.0
 */
function ddc_load_bp_integration() {
	require DDC_PLUGIN_DIR . 'includes/template.php';
	require DDC_PLUGIN_DIR . 'includes/user-integration.php';
	require DDC_PLUGIN_DIR . 'includes/theme-compatibility.php';

	if ( bp_is_active( 'groups' ) ) {
		require DDC_PLUGIN_DIR . 'includes/bp-groups-integration.php';
		bp_register_group_extension( 'DDC_Group_Extension' );
	}

	if ( bp_is_active( 'groups' ) ) {
		require DDC_PLUGIN_DIR . 'includes/bp-activity-integration.php';
	}

	if ( class_exists( 'BPBD' ) ) {
		require DDC_PLUGIN_DIR . 'includes/bpbd-integration.php';
	}
}
add_action( 'bp_loaded', 'ddc_load_bp_integration', 20 );

/**
 * Load schema for storing tool connections.
 *
 * Custom post type 'ddc_tool' stores local data about the tools, as needed for display.
 *
 * Custom taxonomy 'ddc_tool_is_used_by_user' is a property of tools, pointing to user IDs.
 *
 * @since 1.0.0
 */
function ddc_load_schema() {
	register_post_type( 'ddc_tool', array(
		'label'  => __( 'DiRT Tools', 'dirt-directory-client' ),
		'public' => true,
		'has_archive' => true,
		'rewrite' => array(
			'slug' => _x( 'tool', 'Tool rewrite slug', 'dirt-directory-client' ),
			'with_front' => false,
		),
	) );

	register_taxonomy( 'ddc_tool_is_used_by_user', 'ddc_tool', array(
		'label'  => __( 'DiRT Tool Users', 'dirt-directory-client' ),
		'public' => false,
	) );

	register_taxonomy( 'ddc_tool_category', 'ddc_tool', array(
		'label'  => __( 'DiRT Tool Category', 'dirt-directory-client' ),
		'public' => true,
	) );
}
add_action( 'init', 'ddc_load_schema' );

/**
 * Add local templates to the BP template stack, so that bp_get_template_part() can be used.
 *
 * @since 1.0.0
 *
 * @param $array Template location stack.
 * @return $array
 */
function ddc_add_to_template_stack( $stack ) {
	$stack[] = DDC_PLUGIN_DIR . 'templates';
	return $stack;
}
add_filter( 'bp_get_template_stack', 'ddc_add_to_template_stack' );

/**
 * AJAX callback for tool use toggle.
 *
 * @since 1.0.0
 */
function ddc_tool_use_toggle_ajax_cb() {
	$data = array(
		'nonce' => '',
		'tool_id' => '',
		'toggle' => '',
		'tool_node_id' => '',
	);

	foreach ( $data as $dkey => &$dvalue ) {
		if ( isset( $_GET[ $dkey ] ) ) {
			$dvalue = stripslashes( $_GET[ $dkey ] );
		}
	}

	// Nonce check.
	if ( ! wp_verify_nonce( $data['nonce'], "ddc_toggle_tool_{$data['tool_node_id']}" ) ) {
		wp_send_json_error( __( 'Could not perform requested action.', 'dirt-directory-client' ) );
	}

	$tool = ddc_get_tool_by_identifier( $data['tool_id'], $data['tool_node_id'] );

	if ( ! $tool ) {
		wp_send_json_error( __( 'Could not find tool.', 'dirt-directory-client' ) );
	}

	$success = false;
	$message = __( 'Could not perform requested action', 'dirt-directory-client' );

	switch ( $data['toggle'] ) {
		case 'remove' :
			$removed = ddc_dissociate_tool_from_user( $tool->ID, bp_loggedin_user_id() );

			if ( $removed ) {
				$message = __( 'You have successfully removed this tool.', 'dirt-directory-client' );
				$success = true;
			} else {
				$message = __( 'There was a problem removing this tool.', 'dirt-directory-client' );
				$success = false;
			}

			break;

		case 'add' :
			$added = ddc_associate_tool_with_user( $tool->ID, bp_loggedin_user_id() );

			if ( $added ) {
				$message = __( 'You have successfully added this tool.', 'dirt-directory-client' );
				$success = true;
			} else {
				$message = __( 'There was a problem adding this tool.', 'dirt-directory-client' );
				$success = false;
			}

			break;
	}

	$retval = array(
		'message' => $message,
		'toggle' => $data['toggle'],
	);

	if ( $success ) {
		wp_send_json_success( $retval );
	} else {
		wp_send_json_error( $retval );
	}
}
add_action( 'wp_ajax_ddc_tool_use_toggle', 'ddc_tool_use_toggle_ajax_cb' );
