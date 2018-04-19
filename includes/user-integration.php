<?php

/**
 * Functionality that links tools to users.
 *
 * @since 1.0.0
 */

/**
 * Associate a tool with a user.
 *
 * @since 1.0.0
 *
 * @param int $tool_id ID of the tool.
 * @param int $user_id ID of the user.
 * @return int
 */
function ddc_associate_tool_with_user( $tool_id, $user_id ) {
	$tt_ids = wp_set_object_terms( $tool_id, ddc_get_user_term( $user_id ), 'ddc_tool_is_used_by_user', true );

	if ( ! empty( $tt_ids ) ) {
		do_action( 'ddc_associated_tool_with_user', $tool_id, $user_id, $tt_ids );
		return true;
	} else {
		return false;
	}
}

/**
 * Dissociate a tool from a user.
 *
 * @since 1.0.0
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

	do_action( 'ddc_dissociated_tool_from_user', $tool_id, $user_id );

	return true;
}
/**
 * Get the unique slug for ddc_tool_is_used_by_user terms.
 *
 * @since 1.0.0
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
 * @since 1.0.0
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
 * @since 1.0.0
 *
 * @param array $args {
 *     Optional tool parameters.
 *     @type string $title       Tool title.
 *     @type string $link        Remote URL of tool (DiRT node URI).
 *     @type int    $node_ID     DiRT node ID.
 *     @type string $thumbnail   URL of tool thumbnail image.
 *     @type string $image       URL of tool full image.
 *     @type string $description Tool description.
 *     @type array  $categories  Optional. Array of category names.
 * }
 * @return int ID of the newly created tool.
 */
function ddc_create_tool( $args = array() ) {
	$r = array_merge( array(
		'title' => '',
		'link' => '',
		'node_id' => 0,
		'thumbnail' => '',
		'image' => '',
		'description' => '',
		'categories' => array(),
	), $args );

	// No checking for dupes

	// @todo post_author? do we care?
	$tool_id = wp_insert_post( array(
		'post_type' => 'ddc_tool',
		'post_title' => $r['title'],
		'post_status' => 'publish',
		'post_content' => $r['description'],
	) );

	if ( $tool_id ) {
		update_post_meta( $tool_id, 'dirt_link', $r['link'] );
		update_post_meta( $tool_id, 'dirt_node_id', $r['node_id'] );
		update_post_meta( $tool_id, 'dirt_thumbnail', $r['thumbnail'] );
		update_post_meta( $tool_id, 'dirt_image', $r['image'] );

		wp_set_object_terms( $tool_id, $r['categories'], 'ddc_tool_category' );
	}

	return $tool_id;
}

/**
 * Fetch a Tool object.
 *
 * @since 1.0.0
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
 * @since 1.0.0
 *
 * @param int $tool_id
 * @return bool|array $users False on failure, user IDs on success.
 */
function ddc_get_users_of_tool( $tool_id, $args = array() ) {
	$args = array_merge( array(
		'group_id' => false,
		'include_self' => true,
		'count' => false,
		'exclude' => false,
	), $args );

	$terms = get_the_terms( $tool_id, 'ddc_tool_is_used_by_user' );

	$exclude = array();
	if ( ! empty( $args['exclude'] ) ) {
		$exclude = wp_parse_id_list( $args['exclude'] );
	}

	$user_ids = array();

	$group_ids = null;
	if ( false !== $args['group_id'] ) {
		$group_ids = wp_parse_id_list( $args['group_id'] );
	}

	$group_member_ids = array();
	if ( ! empty( $group_ids ) && bp_is_active( 'groups' ) ) {
		foreach ( $group_ids as $group_id ) {
			$group_members = wp_cache_get( $group_id, 'ddc_bp_group_members' );
			if ( false === $group_members ) {
				$group_member_query = new BP_Group_Member_Query( array(
					'group_id' => $args['group_id'],
					'type' => 'alphabetical',
					'group_role' => array( 'admin', 'mod', 'member' ),
				) );
				wp_cache_add( $group_id, $group_member_query->results, 'ddc_bp_group_members' );
				$this_group_member_ids = wp_list_pluck( $group_member_query->results, 'ID' );
			} else {
				$this_group_member_ids = wp_list_pluck( $group_members, 'ID' );
			}
		}

		$group_member_ids = array_merge( $group_member_ids, $this_group_member_ids );
	}

	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			$user_id = ddc_get_user_id_from_usedby_term_slug( $term->slug );

			if ( in_array( $user_id, $exclude ) ) {
				continue;
			}

			// If limiting to a group, check that the user is a member first.
			if ( ! empty( $args['group_id'] ) && bp_is_active( 'groups' ) ) {
				if ( ! in_array( $user_id, $group_member_ids ) && ( ! $args['include_self'] || $user_id != bp_loggedin_user_id() ) ) {
					continue;
				}
			}

			$user_ids[] = $user_id;
		}
	}

	if ( empty( $user_ids ) ) {
		$user_ids = array( 0 );
	} elseif ( $args['count'] && $args['count'] < count( $user_ids ) ) {
		$keys = array_rand( $user_ids, $args['count'] );
		$_user_ids = array();
		foreach ( $keys as $key ) {
			$_user_ids[] = (int) $user_ids[ $key ];
		}
		$user_ids = $_user_ids;
	}

	return $user_ids;
}

/**
 * Get tools of a given user.
 *
 * @since 1.0.0
 *
 * @param int   $user_id ID of the user.
 * @param array $args    See {@see ddc_get_tools()} for a description.
 * @return array
 */
function ddc_get_tools_of_user( $user_id, $args = array() ) {
	$args['user_id'] = $user_id;
	return ddc_get_tools( $args );
}

/**
 * Get tools in use on the site..
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of optional arguments.
 *     @type int $posts_per_page Number of results to return per page. Default: -1 (no limit).
 * }
 * @return array
 */
function ddc_get_tools_in_use( $args = array() ) {
	$r = array_merge( array(
		'posts_per_page' => -1,
	), $args );

	// Allow regular WP_Query stuff to get passed through.
	$tools_query_args = array_merge( array(
		'post_type' => 'ddc_tool',
		'post_status' => 'publish',
	), $r );

	$tools_query = new WP_Query( $tools_query_args );

	// Add DiRT-specific info to post objects
	foreach ( $tools_query->posts as &$post ) {
		$post->dirt_node_id = get_post_meta( $post->ID, 'dirt_node_id', true );
		$post->dirt_link    = get_post_meta( $post->ID, 'dirt_link', true );
	}

	return $tools_query->posts;
}

/** Action functions *********************************************************/

/**
 * Catch add and remove requests.
 *
 * @since 1.0.0
 */
function ddc_catch_add_remove_requests() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$tool_id = $tool_node_id = false;
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
	$tool = ddc_get_tool_by_identifier( $tool_id, $tool_node_id );

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

/**
 * Implementation of BP_Component.
 *
 * Integrates into user profiles.
 *
 * @since 1.0.0
 */
class DiRT_Directory_Client_Component extends BP_Component {
	/**
	 * Does the given user have tools?
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $user_has_tools = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::start(
			'ddc',
			__( 'Digital Research Tools', 'dirt-directory-client' ),
			DDC_PLUGIN_DIR,
			array(
				'adminbar_myaccount_order' => 83,
			)
		);
	}

	/**
	 * Set up global data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See {@see BP_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		parent::setup_globals( array(
			'slug'          => 'dirt',
			'has_directory' => false,
		) );
	}

	/**
	 * Set up nav items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $main_nav See {@see BP_Component::setup_nav()}.
	 * @param array $sub_nav  See {@see BP_Component::setup_nav()}.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$main_nav = array(
			'name' => __( 'Digital Research Tools', 'dirt-directory-client' ),
			'slug' => $this->slug,
			'position' => 83,
			'screen_function' => array( $this, 'template_loader' ),
			'default_subnav_slug' => 'tools',
			'show_for_displayed_user' => true, // Going to change this later
		);

		add_action( 'init', array( $this, 'change_tab_visibility' ), 100 );

		$sub_nav[] = array(
			'name' => __( 'Tools', 'dirt-directory-client' ),
			'slug' => 'tools',
			'parent_url' => bp_displayed_user_domain() . $this->slug . '/',
			'parent_slug' => $this->slug,
			'screen_function' => array( $this, 'template_loader' )
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Should the tab be shown for the user?
	 *
	 * Current logic: show if the user has any tools.
	 *
	 * Have to do it like this because post types are not registered at the
	 * time that the nav is set up. Blargh.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function change_tab_visibility() {
		if ( class_exists( 'BP_Core_Nav' ) ) {
			buddypress()->members->nav->edit_nav( array(
				'show_for_displayed_user' => $this->user_has_tools( bp_displayed_user_id() ),
			), 'dirt' );
		} else {
			$bp_nav = buddypress()->bp_nav;

			$bp_nav['dirt']['show_for_displayed_user'] = $this->user_has_tools( bp_displayed_user_id() );

			buddypress()->bp_nav = $bp_nav;
		}
	}

	/**
	 * Does the user have tools?
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	protected function user_has_tools( $user_id ) {
		if ( isset( $this->user_has_tools[ $user_id ] ) ) {
			return (bool) $this->user_has_tools[ $user_id ];
		}

		$tools_query = new WP_Query( array(
			'post_type' => 'ddc_tool',
			'post_status' => 'publish',
			'tax_query' => array(
				array(
					'taxonomy' => 'ddc_tool_is_used_by_user',
					'terms' => ddc_get_user_term( $user_id ),
					'field' => 'slug',
				),
			),
			'posts_per_page' => 1,
			'fields' => 'ID',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
		) );

		$this->user_has_tools[ $user_id ] = $tools_query->have_posts();

		return $this->user_has_tools[ $user_id ];
	}

	/**
	 * Template loader.
	 *
	 * Also responsible for enqueing assets.
	 *
	 * @since 1.0.0
	 */
	public function template_loader() {
		add_action( 'bp_template_content', array( $this, 'template_content_loader' ) );
		wp_enqueue_style( 'dirt-directory-client' );
		wp_enqueue_script( 'dirt-directory-client' );
		bp_core_load_template( 'members/single/plugins' );
	}

	/**
	 * Template content loader.
	 *
	 * @since 1.0.0
	 */
	public function template_content_loader() {
		bp_get_template_part( 'dirt/member' );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since 1.1.0
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar() for a description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		if ( ! $this->user_has_tools( bp_loggedin_user_id() ) ) {
			return;
		}

		$wp_admin_nav[] = array(
			'parent' => buddypress()->my_account_menu_id,
			'id' => 'my-account-dirt',
			'title' => __( 'Digital Research Tools', 'dirt-directory-client' ),
			'href' => bp_loggedin_user_domain() . $this->slug . '/',
		);

		// Add a subnav just so that the styling isn't weird.
		$wp_admin_nav[] = array(
			'parent' => 'my-account-dirt',
			'id' => 'my-account-dirt-tools',
			'title' => __( 'My Tools', 'dirt-directory-client' ),
			'href' => bp_loggedin_user_domain() . $this->slug . '/',
		);

		parent::setup_admin_bar( $wp_admin_nav );
	}
}

/**
 * Bootstrap the BP_Component.
 *
 * @since 1.0.0
 */
function ddc_component_bootstrap() {
	buddypress()->ddc = new DiRT_Directory_Client_Component();
}
ddc_component_bootstrap(); // No need to wait - we're already at bp_loaded
