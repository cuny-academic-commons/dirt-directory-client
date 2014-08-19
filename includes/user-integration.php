<?php

/**
 * Functionality that links tools to users.
 *
 * @since 1.0
 */

/**
 * Associate a tool with a user.
 *
 * @since 1.0
 *
 * @param int $tool_id ID of the tool.
 * @param int $user_id ID of the user.
 * @return int
 */
function ddc_associate_tool_with_user( $tool_id, $user_id ) {
	$tt_ids = wp_set_object_terms( $tool_id, ddc_get_user_term( $user_id ), 'ddc_tool_is_used_by_user', true );

	if ( ! empty( $tt_ids ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Dissociate a tool from a user.
 *
 * @since 1.0
 *
 * @param int $tool_id ID of the tool.
 * @param int $user_id ID of the user.
 * @return bool
 */
function ddc_dissociate_tool_from_user( $tool_id, $user_id ) {
	$existing_terms = wp_get_object_terms( $tool_id, 'ddc_tool_is_used_by_user', array(
		'fields' => 'slugs',
	) );

	$user_term = ddc_get_user_term( $user_id );

	if ( ! in_array( $user_term, $existing_terms ) ) {
		return false;
	}

	$new_terms = array_diff( $existing_terms, array( $user_term ) );

	// Don't append - overwrite
	wp_set_object_terms( $tool_id, $new_terms, 'ddc_tool_is_used_by_user', false );

	return true;
}
/**
 * Get the unique slug for ddc_tool_is_used_by_user terms.
 *
 * @since 1.0
 *
 * @param int $user_id
 * @return string
 */
function ddc_get_user_term( $user_id ) {
	return 'ddc_tool_is_used_by_user_' . $user_id;
}

/**
 * Get the user ID from a ddc_tool_is_used_by_user term slug.
 *
 * @since 1.0
 * @param string $slug
 * @return int
 */
function ddc_get_user_id_from_usedby_term_slug( $slug ) {
	$user_id = substr( $slug, 25 );
	return intval( $user_id );
}

/**
 * Create a local Tool object.
 *
 * @since 1.0
 *
 * @param array $args {
 *
 * }
 * @return int
 */
function ddc_create_tool( $args = array() ) {
	$r = array_merge( array(
		'title' => '',
		'link' => '',
		'node_id' => 0,
	), $args );

	// No checking for dupes

	// @todo post_author? do we care?
	$tool_id = wp_insert_post( array(
		'post_type' => 'ddc_tool',
		'post_title' => $r['title'],
		'post_status' => 'publish',
	) );

	if ( $tool_id ) {
		update_post_meta( $tool_id, 'dirt_link', $r['link'] );
		update_post_meta( $tool_id, 'dirt_node_id', $r['node_id'] );
	}

	return $tool_id;
}

/**
 * Fetch a Tool object.
 *
 * @since 1.0
 *
 * @param string $by Field to query by. 'node_id', 'link', 'title'.
 * @param int|string $value Value to query by.
 * @return null|WP_Post
 */
function ddc_get_tool( $by, $value ) {
	$tool = null;

	switch ( $by ) {
		// Postmeta
		case 'node_id' :
		case 'link' :
			$posts = new WP_Query( array(
				'post_type' => 'ddc_tool',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'dirt_' . $by,
						'value' => $value,
					),
				),
				'posts_per_page' => 1,
			) );

			if ( ! empty( $posts->posts ) ) {
				$tool = $posts->posts[0];
			}

			break;

		case 'title' :
			// No way to do this in the API
			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ddc_tool' AND post_status = 'publish' AND post_title = %s LIMIT 1", $value ) );

			if ( $post_id ) {
				$tool = get_post( $post_id );
			}

			break;
	}

	return $tool;
}

/**
 * Get users of a given tool.
 *
 * @param int $tool_id
 * @return bool|array $users False on failure, users on success.
 */
function ddc_get_users_of_tool( $tool_id ) {
	$terms = wp_get_object_terms( $tool_id, 'ddc_tool_is_used_by_user' );

	$user_ids = array( 0 );
	foreach ( $terms as $term ) {
		$user_ids[] = ddc_get_user_id_from_usedby_term_slug( $term->slug );
	}

	$users = bp_core_get_users( array(
		'type'    => 'alphabetical',
		'include' => $user_ids,
	) );

	return $users['users'];
}

/** Action functions *********************************************************/

/**
 * Catch add and remove requests.
 *
 * @since 1.0
 */
function ddc_catch_add_remove_requests() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	if ( ! empty( $_GET['add_dirt_tool'] ) ) {
		$action       = 'add';
		$url_action   = 'add_dirt_tool';
		$nonce_action = 'ddc_add_tool';
		$tool_node_id = intval( $_GET['add_dirt_tool'] );
	} else if ( ! empty( $_GET['remove_dirt_tool'] ) ) {
		$action       = 'remove';
		$url_action   = 'remove_dirt_tool';
		$nonce_action = 'ddc_remove_tool';
		$tool_id      = intval( $_GET['remove_dirt_tool'] );
	}

	if ( empty( $action ) ) {
		return;
	}

	$nonce = '';
	if ( ! empty( $_GET['_wpnonce'] ) ) {
		$nonce = urldecode( $_GET['_wpnonce'] );
	}

	$redirect_to = remove_query_arg( array(
		$url_action,
		'_wpnonce',
	), bp_get_requested_url() );

	if ( ! wp_verify_nonce( $nonce, $nonce_action ) ) {
		bp_core_redirect( $redirect_to );
	}

	// If we've gotten this far, process the request

	// Tool IDs are passed on remove actions
	if ( ! empty( $tool_id ) ) {
		$tool = get_post( $tool_id );

	// When adding tools, we look up by nid, in case a new tool
	// has to be created
	} else {
		$tool = ddc_get_tool( 'node_id', $tool_node_id );
		if ( empty( $tool ) ) {
			$c = new DiRT_Directory_Client();
			$tool_data = $c->get_item_by_node_id( $tool_node_id );
			if ( ! empty( $tool ) ) {
				$tool_id = ddc_create_tool( array(
					'title'   => $tool_data->title,
					'link'    => $tool_data->link,
					'node_id' => $tool_node_id,
				) );

				$tool = get_post( $tool_id );
			}
		}
	}

	if ( empty( $tool ) ) {
		bp_core_add_message( 'Could not find tool.', 'error' );
		bp_core_redirect( $redirect_to );
	}

	switch ( $action ) {
		case 'remove' :
			$removed = ddc_dissociate_tool_from_user( $tool->ID, bp_loggedin_user_id() );

			if ( $removed ) {
				bp_core_add_message( __( 'You have successfully removed this tool.', 'dirt-directory-client' ) );
			} else {
				bp_core_add_message( __( 'There was a problem removing this tool.', 'dirt-directory-client' ), 'error' );
			}

			break;

		case 'add' :
			$added = ddc_associate_tool_with_user( $tool->ID, bp_loggedin_user_id() );

			if ( $added ) {
				bp_core_add_message( __( 'You have successfully added this tool.', 'dirt-directory-client' ) );
			} else {
				bp_core_add_message( __( 'There was a problem adding this tool.', 'dirt-directory-client' ), 'error' );
			}

			break;
	}

	bp_core_redirect( $redirect_to );
}
add_action( 'bp_actions', 'ddc_catch_add_remove_requests' );
