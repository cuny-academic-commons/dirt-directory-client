<?php

/**
 * Miscellanous utility functions.
 *
 * @since 1.0.0
 */

/**
 * Add non-persistent caching group.
 *
 * @since 1.1.1
 */
function ddc_add_non_persistent_caching_group() {
	wp_cache_add_non_persistent_groups( array(
		'ddc_bp_group_members',
		'ddc_bp_users',
	) );
}

/**
 * Register CSS and JS assets.
 *
 * @since 1.0.0
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
 * Enqueue assets on CPT pages.
 *
 * BP pages are handled by BP integration pieces.
 *
 * @since 1.0.0
 */
function ddc_enqueue_assets() {
	if ( ddc_is_tool_directory() || ddc_is_tool_page() ) {
		wp_enqueue_style( 'dirt-directory-client' );
		wp_enqueue_script( 'dirt-directory-client' );
	}
}
add_action( 'wp_enqueue_scripts', 'ddc_enqueue_assets' );

/**
 * Get a local tool object by either the local ID or the remote NID.
 *
 * @since 1.0.0
 *
 * @param int $tool_id      Optional. Local tool ID. Will take precedence if provided.
 * @param int $tool_node_id Optional. DiRT tool node ID.
 * @return WP_Post|null WP_Post on success or null on failure.
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
				$_tool = ddc_parse_tool( $tool_data );
				$tool_id = ddc_create_tool( $_tool );
				$tool = get_post( $tool_id );
			}
		}
	}

	return $tool;
}

/**
 * Get local tools.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of optional parameters.
 *     @type string $order Sort order. Accepts 'ASC' or 'DESC'. Default 'ASC'.
 *     @type string $orderby Field to order by. Accepts 'name' or 'date'. Default 'name'.
 *     @type int $posts_per_page Number of posts to return per page. Default -1 (no limit).
 *     @type int|bool $user_id If present, limit results to those used by given user ID. Default false.
 *     @type string $search_terms Filter results based on search terms.
 *     @type string $categories Array of categories to match. Should be passed as taxonomy term 'name' properties.
 * }
 * @return array Array of results (WP_Post objects).
 */
function ddc_get_tools( $args = array() ) {
	$r = array_merge( array(
		'order'          => 'ASC',
		'orderby'        => 'name',
		'posts_per_page' => -1,
		'user_id'        => false,
		'search_terms'   => '',
		'categories'     => array(), // By 'name'.
	), $args );

	$query_args = array(
		'post_type'   => 'ddc_tool',
		'post_status' => 'publish',
		'tax_query'   => array(),
		'orderby'     => 'name',
		'order'       => 'ASC',
	);

	// posts_per_page
	// @todo Sanitize?
	$query_args['posts_per_page'] = $r['posts_per_page'];

	// orderby
	if ( in_array( $r['orderby'], array( 'name', 'date' ) ) ) {
		$query_args['orderby'] = $r['orderby'];
	}

	// order
	if ( 'DESC' === strtoupper( $r['order'] ) ) {
		$query_args['order'] = 'DESC';
	}

	// @todo support for multiple users
	if ( false !== $r['user_id'] ) {
		$query_args['tax_query'][] = array(
			'taxonomy' => 'ddc_tool_is_used_by_user',
			'terms' => ddc_get_user_term( $r['user_id'] ),
			'field' => 'slug',
		);
	}

	if ( ! empty( $r['categories'] ) ) {
		// Can't pass 'name' properly to tax query. Fixed in WP 4.2 - #WP27810.
		$cat_ids = array();
		foreach ( (array) $r['categories'] as $cat_name ) {
			$_cat = get_term_by( 'name', $cat_name, 'ddc_tool_category' );
			if ( $_cat ) {
				$cat_ids[] = $_cat->term_id;
			}
		}

		$query_args['tax_query'][] = array(
			'taxonomy' => 'ddc_tool_category',
			'terms' => $cat_ids,
			'field' => 'id',
		);
	}

	// search_terms
	if ( ! empty( $r['search_terms'] ) ) {
		$query_args['s'] = $r['search_terms'];
	}

	$tools_query = new WP_Query( $query_args );

	// Add DiRT-specific info to post objects
	foreach ( $tools_query->posts as &$post ) {
		$post->dirt_node_id   = get_post_meta( $post->ID, 'dirt_node_id', true );
		$post->dirt_link      = get_post_meta( $post->ID, 'dirt_link', true );
		$post->dirt_thumbnail = get_post_meta( $post->ID, 'dirt_thumbnail', true );
		$post->dirt_image     = get_post_meta( $post->ID, 'dirt_image', true );
	}

	return $tools_query->posts;
}

/**
 * Parse raw tool data into a standard format.
 *
 * Depending on the particular endpoint, the API returns results that are formatted slightly differently. The current
 * function, surely the worst one in this entire plugin, standardizes them.
 *
 * @since 1.0.0
 *
 * @param object $tool Tool data from an API request.
 * @return array|bool A standardized array on success, false if the wrong kind of `$tool` is passed.
 */
function ddc_parse_tool( $tool ) {
	// The API returns an error string when nothing is found.
	if ( is_string( $tool ) ) {
		return false;
	}

	$_tool = array(
		'node_id' => '',
		'title' => '',
		'link' => '',
		'snippet' => '',
		'thumbnail' => '',
		'image' => '',
		'description' => '',
		'categories' => array(),
	);

	if ( isset( $tool->node ) ) {
		$node = $tool->node;
	} else {
		$node = $tool;
	}

	if ( isset( $node->nid ) ) {
		$_tool['node_id'] = $node->nid;
	}

	$_tool['title'] = $tool->title;

	if ( isset( $tool->link ) ) {
		$_tool['link'] = $tool->link;
	} else if ( $_tool['node_id'] ) {
		$_tool['link'] = 'http://dirtdirectory.org/node/' . $_tool['node_id'];
	}

	if ( isset( $tool->snippet ) ) {
		$_tool['snippet'] = $tool->snippet;
	}

	if ( isset( $node->field_logo->und[0]->filename ) ) {
		$_tool['thumbnail'] = $node->field_logo->und[0]->filename;
	}

	if ( isset( $node->field_logo->und[0]->uri ) ) {
		$_tool['image'] = $node->field_logo->und[0]->uri;
	}

	if ( isset( $node->body->und[0]->value ) ) {
		$_tool['description'] = $node->body->und[0]->value;
	} else if ( isset( $node->body->en[0]->value ) ) {
		$_tool['description'] = $node->body->en[0]->value;
	}

	if ( isset( $node->field_tadirah_goals_methods->und ) ) {
		$ddc_cats = ddc_categories();
		foreach ( $node->field_tadirah_goals_methods->und as $cat ) {
			if ( isset( $cat->tid ) ) {
				// Whee!
				foreach ( $ddc_cats as $ddc_cat ) {
					if ( $cat->tid == $ddc_cat['tid'] ) {
						$_tool['categories'][] = $ddc_cat['name'];
						break;
					}
				}
			}
		}
	}

	return $_tool;
}

/**
 * Get the slug used to build group/user tabs.
 *
 * Put into a separate function so that it can be abstracted at some point in the future.
 *
 * @since 1.0.0
 *
 * @return string
 */
function ddc_get_slug() {
	return 'dirt';
}
