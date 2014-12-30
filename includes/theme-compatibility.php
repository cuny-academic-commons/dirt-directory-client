<?php

/**
 * Integration with BP theme compatibility.
 *
 * This enables us to use native WP theme templates for our CPT pages.
 *
 * @since 1.0
 */

/**
 * Filter template location for the tool archive.
 *
 * This is part of a larger dance that includes filtering BP template locations. What a tangled web we weave. Template
 * loading is a contender for my least favorite part of WordPress.
 *
 * @since 1.0
 */
function ddc_template_include( $template ) {
	if ( ddc_is_tool_directory() || ddc_is_tool_page() ) {
		bp_set_theme_compat_active( true );
		do_action( 'bp_setup_theme_compat' );
	}

	return $template;
}
add_filter( 'template_include', 'ddc_template_include', 0 );

function ddc_template_include_2( $template ) {
	if ( ddc_is_tool_directory() ) {
		return bp_template_include_theme_compat();
	}

	return $template;
}
add_filter( 'template_include', 'ddc_template_include_2', 999 );

class DDC_Theme_Compat {
	public function __construct() {
		add_action( 'bp_setup_theme_compat', array( $this, 'set_up_theme_compat' ) );
	}

	public function set_up_theme_compat() {
		if ( ddc_is_tool_directory() ) {
			add_filter( 'bp_template_include_reset_dummy_post_data', array( $this, 'directory_dummy_post' ) );
			add_filter( 'bp_replace_the_content', array( $this, 'directory_content' ) );
		}

		if ( ddc_is_tool_page() ) {
			add_filter( 'bp_replace_the_content', array( $this, 'single_content' ) );
		}
	}

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

	public function directory_content() {
		return bp_buffer_template_part( 'dirt/directory', null, false );
	}

	public function single_content() {
		return bp_buffer_template_part( 'dirt/single', null, false );
	}
}
new DDC_Theme_Compat ();
