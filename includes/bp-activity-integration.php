<?php

/**
 * Register activity actions.
 *
 * @since 1.0
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
 * @since 1.0
 */
function ddc_format_activity_action_tool_marked_used( $action, $activity ) {
	$user_link = bp_core_get_userlink( $activity->user_id );

	$tool = get_post( $activity->item_id );

	$action = sprintf(
		__( '%1$s uses the digital research tool %2$s' ),
		$user_link,
		$tool->post_title
	);

	return $action;
}

/**
 * Functionality related to the BP Activity component.
 *
 * @since 1.0
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
 * @since 1.0
 *
 * @param int $tool_id
 * @param int $user_id
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
