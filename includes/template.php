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

	// Tool image
	if ( ! empty( $tool_data['thumbnail'] ) && 'dirt_logo_default.png' !== $tool_data['thumbnail'] ) {
		$image_url = DDC_IMAGE_BASE . 'styles/thumbnail/public/logos/' . $tool_data['thumbnail'];
	} else {
		$image_url = str_replace( 'public://', DDC_IMAGE_BASE, $tool_data['image'] );
	}

	$tool = ddc_get_tool( 'node_id', $tool_data['node_id'] );

	$tool_id = false;
	if ( $tool ) {
		$tool_id = $tool->ID;
	}

	$local_tool_url = '';
	if ( $tool ) {
		$local_tool_url = get_permalink( $tool );
	}

	$img_tag = '';
	if ( $image_url ) {
		$img_tag = sprintf(
			'<a href="%s"><img src="%s" /></a>',
			$local_tool_url ? esc_attr( $local_tool_url ) : esc_attr( $tool_data['link'] ),
			esc_attr( $image_url )
		);
	}

	$html .= sprintf(
		'<div class="dirt-tool-image">%s</div>',
		$img_tag
	);

	// Tool name
	if ( $local_tool_url ) {
		$tool_title = sprintf(
			'<a href="%s">%s</a>',
			esc_attr( $local_tool_url ),
			esc_html( $tool_data['title'] )
		);
	} else {
		$tool_title = esc_html( $tool_data['title'] );
	}

	$html .= sprintf(
		'<div class="dirt-tool-name">%s</div>',
		$tool_title
	);

	$used_by_query = ddc_get_users_of_tool( $tool->ID, array(
		'count' => 3,
	) );
	$used_by_users = $used_by_query['users'];

	// Action button
	$url_base = bp_get_requested_url();
	if ( is_user_logged_in() ) {
		if ( in_array( bp_loggedin_user_id(), wp_list_pluck( $used_by_users, 'ID' ) ) ) {
			$url_base = add_query_arg( 'remove_dirt_tool', $tool_data['node_id'] );
			$button = sprintf(
				'<div class="dirt-tool-action dirt-tool-action-remove"><label for="dirt-tool-remove-%1$d" class="dirt-tool-action-label"><a href="%2$s">I use this</a></label> <input checked="checked" type="checkbox" value="%d" name="dirt-tool-remove[%1$d]" id="dirt-tool-remove-%1$d" data-tool-id="%1$d" data-tool-node-id="%5$d" data-nonce="%4$s"><span class="dirt-tool-action-question dirt-tool-action-question-remove">%3$s</span></div>',
				$tool_id,
				wp_nonce_url( $url_base, 'ddc_remove_tool' ),
				__( 'Click to remove this tool from your list', 'dirt-directory-client' ),
				wp_create_nonce( 'ddc_toggle_tool_' . $tool_data['node_id'] ),
				$tool_data['node_id']
			);
		} else {
			$url_base = add_query_arg( 'add_dirt_tool', $tool_data['node_id'] );
			$button = sprintf(
				'<div class="dirt-tool-action dirt-tool-action-add"><label for="dirt-tool-add-%1$d" class="dirt-tool-action-label"><a href="%2$s">I use this</a></label> <input type="checkbox" value="%d" name="dirt-tool-add[%1$d]" id="dirt-tool-add-%1$d" data-tool-id="%1$d" data-tool-node-id="%5$d" data-nonce="%4$s"><span class="dirt-tool-action-question dirt-tool-action-question-add">%3$s</span></div>',
				$tool_id,
				wp_nonce_url( $url_base, 'ddc_add_tool' ),
				__( 'Click to show that you use this tool', 'dirt-directory-client' ),
				wp_create_nonce( 'ddc_toggle_tool_' . $tool_data['node_id'] ),
				$tool_data['node_id']
			);
		}

		$html .= $button;
	}

	// Tool description
	if ( ! empty( $tool_data['description'] ) ) {
		$link = $tool_data['link'];
		if ( ! $link ) {
			$link = 'http://dirtdirectory.org/node/' . $tool_data['node_id'];
		}

		$description  = strip_tags( trim( $tool_data['description'] ) );
		$description .= sprintf(
			'<a class="dirt-external-link" target="_blank" href="%s">%s</a>',
			esc_attr( $link ),
			__( 'Learn more on DiRTDirectory.org', 'dirt-directory-client' )
		);

		$html .= sprintf(
			'<div class="dirt-tool-description-toggle"><a class="dirt-tool-description-toggle-link dirt-tools-description-toggle-link-show" href="#">%s</a><a class="dirt-tool-description-toggle-link dirt-tool-description-toggle-link-hide" href="#">%s</a></div><div class="dirt-tool-description">%s</div>',
			__( 'Show Description', 'dirt-directory-client' ),
			__( 'Hide Description', 'dirt-directory-client' ),
			wpautop( $description )
		);
	}

	if ( ! empty( $used_by_users ) ) {
		$used_by_list_items = array();
		foreach ( $used_by_users as $used_by_user ) {
			$used_by_list_items[] = sprintf(
				'<span class="dirt-tool-user dirt-tool-user-%d">
					<a href="%s">%s</a>
				</span>',
				$used_by_user->ID,
				bp_core_get_user_domain( $used_by_user->ID ) . ddc_get_slug() . '/',
				bp_core_get_user_displayname( $used_by_user->ID )
			);
		}

		if ( ! empty( $used_by_list_items ) ) {
			$used_by_list_item_count = $used_by_query['total'] - 3;
			if ( $used_by_list_item_count < 0 ) {
				$used_by_list_item_count = 0;
			}

			if ( $used_by_list_item_count ) {
				$text = sprintf(
					_n( 'Used by %s and %s other user &mdash; <a href="%s">Show all users</a>', 'Used by %s and %s other users &mdash; <a href="%s">Show all users</a>', $used_by_list_item_count, 'dirt-directory-client' ),
					implode( ', ', $used_by_list_items ),
					number_format_i18n( $used_by_list_item_count ),
					ddc_get_tool_directory_url()
				);
			} else {
				$text = sprintf(
					__( 'Used by %s &mdash; <a href="%s">Show all users</a>', 'dirt-directory-client' ),
					implode( ', ', $used_by_list_items ),
					ddc_get_tool_directory_url()
				);
			}

			$html .= sprintf(
				'<div class="dirt-tool-users" id="dirt-tool-%d-users">%s</div>',
				$tool_id,
				$text
			);
		}
	}

	return $html;
}

/**
 * Get a link to the tools directory.
 *
 * @since 1.0
 *
 * @return string
 */
function ddc_get_tool_directory_url() {
	return home_url( 'tool' );
}

function ddc_categories() {
	// @todo Better cache busting.
	$cats = get_option( 'ddc_categories' );

	if ( ! $cats ) {
		$cats = array();

		$c = new DiRT_Directory_Client();
		$taxonomies = $c->get_taxonomies();
		$cat_vid = 0;
		foreach ( $taxonomies as $tax ) {
			if ( 'categories' === $tax->machine_name ) {
				$cat_vid = $tax->vid;
				break;
			}
		}

		if ( ! $cat_vid ) {
		//	update_option( 'ddc_categories', $cats );
		}

		$categories = $c->get_taxonomy_terms( $cat_vid );
		foreach ( $categories as $category ) {
			$cats[] = array(
				'tid' => $category->tid,
				'name' => $category->name,
			);
		}

		update_option( 'ddc_categories', $cats );
	}

	return $cats;
}
