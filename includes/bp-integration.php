<?php

/**
 * Set up integration with BuddyPress
 *
 * @since 1.0
 */

/**
 * Load group integration.
 *
 * @since 1.0
 */
function ddc_load_bp_group_integration() {
	if ( ! bp_is_active( 'groups' ) ) {
		return;
	}

	require DDC_PLUGIN_DIR . 'includes/bp-groups-integration.php';
	bp_register_group_extension( 'DDC_Group_Extension' );
}
add_action( 'bp_init', 'ddc_load_bp_group_integration' );

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
