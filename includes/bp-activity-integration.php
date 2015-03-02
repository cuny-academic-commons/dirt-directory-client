<?php
/**
 * Integration with the BuddyPress Activity component.
 *
 * @since 1.0.0
 */

/**
 * Register activity actions.
 *
 * @since 1.0.0
 */
function ddc_register_activity_actions() {
	bp_activity_set_action(
		'dirt',
		'tool_marked_used',
		__( 'Digital Research Tool Used', 'buddypress' ),
		'ddc_format_activity_action_tool_marked_used',
		__( 'Digital Research Tools Used', 'buddypress' ),
		array( 'activity', 'member', 'member_groups' )
	);
}
add_action( 'bp_register_activity_actions', 'ddc_register_activity_actions' );

/**
 * Activity action format callback.
 *
 * @since 1.0.0
 *
 * @param string $action   Action string.
 * @param object $activity Activity object.
 * @return string Formatted activity string.
 */
function ddc_format_activity_action_tool_marked_used( $action, $activity ) {
	$user_link = sprintf(
		'<a href="%s">%s</a>',
		bp_core_get_user_domain( $activity->user_id ) . 'dirt/',
		bp_core_get_user_displayname( $activity->user_id )
	);

	$tool = get_post( $activity->item_id );
	$tool_link = sprintf(
		'<a href="%s">%s</a>',
		get_permalink( $tool ),
		esc_html( $tool->post_title )
	);

	$action = sprintf(
		__( '%1$s uses the digital research tool %2$s' ),
		$user_link,
		$tool_link
	);

	return $action;
}

/**
 * Generate a "uses the tool" activity item on tool association.
 *
 * @since 1.0.0
 *
 * @param int $tool_id Local ID of the tool.
 * @param int $user_id ID of the user.
 */
function ddc_create_tool_marked_used_activity( $tool_id, $user_id ) {
	bp_activity_add( array(
		'component' => 'dirt',
		'type' => 'tool_marked_used',
		'user_id' => $user_id,
		'item_id' => $tool_id,
	) );
}
add_action( 'ddc_associated_tool_with_user', 'ddc_create_tool_marked_used_activity', 10, 2 );

/**
 * Delete tool_marked_used activity item when dissociating.
 *
 * @since 1.0.0
 *
 * @param int $tool_id Local ID of the tool.
 * @param int $user_id ID of the user.
 */
function ddc_delete_tool_marked_used_activity( $tool_id, $user_id ) {
	$activity = bp_activity_get( array(
		'filter' => array(
			'object' => 'dirt',
			'user_id' => $user_id,
			'action' => 'tool_marked_used',
			'primary_id' => $tool_id,
		),
	) );

	if ( ! empty( $activity['activities'] ) ) {
		bp_activity_delete_by_activity_id( $activity['activities'][0]->id );
		return true;
	}

	return false;
}
add_action( 'ddc_dissociated_tool_from_user', 'ddc_delete_tool_marked_used_activity', 10, 2 );
