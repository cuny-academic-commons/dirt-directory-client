<?php

/**
 * Admin-only functionality.
 *
 * @since 1.0.0
 */

/**
 * Run necessary upgrade routines.
 *
 * @since 1.0.0
 */
function ddc_upgrade() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$ddc_version = get_option( 'ddc_version' );

	if ( version_compare( $ddc_version, DDC_VERSION, '>=' ) ) {
		return;
	}

	$ddc_version = floatval( $ddc_version );

	if ( version_compare( $ddc_version, '1.0', '<' ) ) {
		flush_rewrite_rules();
	}

	update_option( 'ddc_version', DDC_VERSION );
}
add_action( 'admin_init', 'ddc_upgrade' );
