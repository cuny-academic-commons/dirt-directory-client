<?php

/**
 * Template functions.
 *
 * @since 1.0
 */

/**
 * Generate the markup for a tool.
 *
 * @param array $args {
 *     Tool data
 *     @type string $link URI of the DiRT page of the tool.
 *     @type string $title Title of the tool.
 *     @type int $node_id DiRT node ID.
 * }
 * @return string $html
 */
function ddc_tool_markup( $tool_data ) {
	$html = '';

	// Tool name
	$html .= sprintf(
		'<div class="dirt-tool-name"><a href="%s">%s</a></div>',
		esc_attr( $tool_data['link'] ),
		esc_html( $tool_data['title'] )
	);

	$tool = ddc_get_tool( 'node_id', $tool_data['node_id'] );

	$used_by_users = array();
	if ( ! empty( $tool ) ) {
		$args = array();
		if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
			$args['group_id'] = bp_get_current_group_id();
		}

		$used_by_users = ddc_get_users_of_tool( $tool->ID, $args );
	}

	// Action button
	$url_base = bp_get_requested_url();
	if ( is_user_logged_in() ) {
		if ( in_array( bp_loggedin_user_id(), wp_list_pluck( $used_by_users, 'ID' ) ) ) {
			$url_base = add_query_arg( 'remove_dirt_tool', $tool->ID );
			$button = sprintf(
				'<div class="dirt-tool-action dirt-tool-action-remove"><a href="%s"><span class="dirt-tool-action-question dirt-tool-action-question-remove">%s</span><i class="genericon genericon-star"></i></a></div>',
				wp_nonce_url( $url_base, 'ddc_remove_tool' ),
				__( 'Remove?', 'dirt-directory-client' )
			);
		} else {
			$url_base = add_query_arg( 'add_dirt_tool', $tool_data['node_id'] );
			$button = sprintf(
				'<div class="dirt-tool-action dirt-tool-action-add"><a href="%s"><span class="dirt-tool-action-question dirt-tool-action-question-add">%s</span><i class="genericon genericon-star"></i></a></div>',
				wp_nonce_url( $url_base, 'ddc_add_tool' ),
				__( 'I use this!', 'dirt-directory-client' )
			);
		}

		$html .= $button;
	}

	// Tool description
	$snippet = '';
	if ( ! empty( $tool_data['snippet'] ) ) {
		$html .= sprintf(
			'<div class="dirt-tool-description">%s</div>',
			$tool_data['snippet'] // @todo Sanitize
		);
	}

	if ( ! empty( $used_by_users ) ) {
		$used_by_list_items = array();
		foreach ( $used_by_users as $used_by_user ) {
			$used_by_list_items[] = sprintf(
				'<li>
					<div class="dirt-tool-user-avatar">%s</div>
					<div class="dirt-tool-user-link"><a href="%s">%s</a></div>
				</li>',
				bp_core_fetch_avatar( array(
					'object' => 'user',
					'item_id' => $used_by_user->ID,
					'type' => 'thumb',
				) ),
				bp_core_get_user_domain( $used_by_user->ID ),
				bp_core_get_user_displayname( $used_by_user->ID )
			);
		}

		if ( ! empty( $used_by_list_items ) ) {
			$used_by_list_item_count = count( $used_by_list_items );
			$html .= sprintf(
				'<div class="dirt-tool-users-toggle"><a class="dirt-tool-users-toggle-link dirt-tools-users-toggle-link-show" href="#">%s</a><a class="dirt-tool-users-toggle-link dirt-tool-users-toggle-link-hide" href="#">%s</a></div><ul class="dirt-tool-users">%s</ul>',
				sprintf( _n( 'Show User', 'Show Users (%s)', $used_by_list_item_count, 'dirt-directory-client' ), number_format_i18n( $used_by_list_item_count ) ),
				sprintf( _n( 'Hide User', 'Hide Users (%s)', $used_by_list_item_count, 'dirt-directory-client' ), number_format_i18n( $used_by_list_item_count ) ),
				implode( "\n", $used_by_list_items )
			);
		}
	}

	return $html;
}
