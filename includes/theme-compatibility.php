<?php

/**
 * Integration with BP theme compatibility.
 *
 * This enables us to use native WP theme templates for our CPT pages.
 *
 * @since 1.0.0
 */

/**
 * Set up theme compatibility for DDC pages, when available.
 *
 * This is part of a larger dance that includes filtering BP template locations. What a tangled web we weave. Template
 * loading is a contender for my least favorite part of WordPress.
 *
 * See wp-includes/template-loader.php, {@link http://codex.wordpress.org/Template_Hierarchy}, and especially
 * {@link https://codex.buddypress.org/themes/theme-compatibility-1-7/} for more information about what's going on here.
 *
 * @since 1.0.0
 *
 * @param string $template Template location found by WordPress.
 * @return string
 */
function ddc_template_include( $template ) {
	// Non theme-compat support. Note that your theme must include the dirt/non-theme-compat-index.php template.
	if ( ! bp_use_theme_compat_with_current_theme() ) {
		if ( ddc_is_tool_directory() || ddc_is_tool_page() ) {
			$template = locate_template( 'dirt/non-theme-compat-index.php' );
		}

		remove_filter( 'template_include', 'ddc_template_include_2', 999 );
		return $template;
	}

	if ( ddc_is_tool_directory() || ddc_is_tool_page() ) {
		bp_set_theme_compat_active( true );
		do_action( 'bp_setup_theme_compat' );
	}

	return $template;
}
add_filter( 'template_include', 'ddc_template_include', 0 );

/**
 * Filter template location when using theme compatibility.
 *
 * Loaded late on 'template_include' to ensure that BP has had a chance to run its main theme compatibility logic.
 *
 * @since 1.0.0
 * @see ddc_template_include()
 *
 * @param string $template Template location found by WordPress.
 * @return string
 */
function ddc_template_include_2( $template ) {
	if ( ! bp_use_theme_compat_with_current_theme() ) {
		return $template;
	}

	if ( ddc_is_tool_directory() ) {
		return bp_template_include_theme_compat();
	}

	return $template;
}
add_filter( 'template_include', 'ddc_template_include_2', 999 );

/**
 * Theme compatibility class.
 *
 * See {@link https://codex.buddypress.org/plugindev/how-to-enjoy-bp-theme-compat-in-plugins/} for details.
 *
 * @since 1.0.0
 */
class DDC_Theme_Compat {
	/**
	 * Constructor.
	 *
	 * Hooks into WP at 'bp_setup_theme_compat'.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'bp_setup_theme_compat', array( $this, 'set_up_theme_compat' ) );
	}

	/**
	 * Add appropriate hooks if we determine that we're on a DDC page that requires theme compat.
	 *
	 * @since 1.0.0
	 */
	public function set_up_theme_compat() {
		if ( ddc_is_tool_directory() ) {
			add_filter( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content' ) );
		}

		// Single tool pages don't need dummy post data, because they are native WP posts.
		if ( ddc_is_tool_page() ) {
			add_filter( 'bp_replace_the_content', array( $this, 'single_content' ) );
		}
	}

	/**
	 * Set up dummy WP global post data for the Directory page.
	 *
	 * @since 1.0.0
	 */
	public function directory_dummy_post() {
		bp_theme_compat_reset_post( array(
			'ID'             => 0,
			'post_title'     => __( 'Digital Research Tools', 'dirt-directory-client' ),
			'post_author'    => 0,
			'post_date'      => 0,
			'post_content'   => '',
			'post_type'      => 'ddc_tool',
			'post_status'    => 'publish',
			'is_archive'     => true,
			'comment_status' => 'closed'
		) );
	}

	/**
	 * Load the template that powers the Directory page.
	 *
	 * @since 1.0.0
	 */
	public function directory_content() {
		return bp_buffer_template_part( 'dirt/directory', null, false );
	}

	/**
	 * Load the template that powers the single Tool page.
	 *
	 * @since 1.0.0
	 */
	public function single_content() {
		return bp_buffer_template_part( 'dirt/single', null, false );
	}
}
new DDC_Theme_Compat();
