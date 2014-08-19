<?php

/**
 * Set up integration with BuddyPress
 *
 * @since 1.0
 */

/**
 * Load BP integration files.
 *
 * @since 1.0
 */
function ddc_load_bp_integration() {
	require DDC_PLUGIN_DIR . 'includes/template.php';
	require DDC_PLUGIN_DIR . 'includes/user-integration.php';

	if ( bp_is_active( 'groups' ) ) {
		require DDC_PLUGIN_DIR . 'includes/bp-groups-integration.php';
		bp_register_group_extension( 'DDC_Group_Extension' );
	}
}
add_action( 'bp_init', 'ddc_load_bp_integration' );

/**
 * Load schema for storing tool connections.
 *
 * Custom post type ddc_tool stores local data about the tools, as needed for
 * display.
 *
 * Custom taxonomy ddc_tool_is_used_by_user is a property of tools, pointing to
 * user IDs.
 *
 * @since 1.0
 */
function ddc_load_schema() {
	register_post_type( 'ddc_tool', array(
		'label'  => __( 'DiRT Tools', 'dirt-directory-client' ),
		'public' => false,
	) );

	register_taxonomy( 'ddc_tool_is_used_by_user', 'ddc_tool', array(
		'label'  => __( 'DiRT Tool Users', 'dirt-directory-client' ),
		'public' => false,
	) );
}
add_action( 'init', 'ddc_load_schema' );

/**
 * Add local templates to the BP template stack, so that bp_get_template_part() can be used.
 *
 * @since 1.0
 *
 * @param $array Template stack.
 * @return $array
 */
function ddc_add_to_template_stack( $stack ) {
	$stack[] = DDC_PLUGIN_DIR . 'templates';
	return $stack;
}
add_filter( 'bp_get_template_stack', 'ddc_add_to_template_stack' );
