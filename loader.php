<?php

/*
Plugin Name: DiRT Directory Client
Version: 1.0.1
Description: Interface with the DiRT Directory API http://dirt.projectbamboo.org
Author: Boone B Gorges
Author URI: http://boone.gorg.es
Text Domain: dirt-directory-client
Domain Path: /languages
*/

define( 'DDC_VERSION', '1.0.1' );
define( 'DDC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Don't change these constants unless you know what you're doing.
if ( ! defined( 'DDC_ENDPOINT_BASE' ) ) {
	define( 'DDC_ENDPOINT_BASE', 'http://dirtdirectory.org/' );
}

if ( ! defined( 'DDC_ENDPOINT_URL' ) ) {
	define( 'DDC_ENDPOINT_URL', DDC_ENDPOINT_BASE . 'services/' );
}

if ( ! defined( 'DDC_IMAGE_BASE' ) ) {
	define( 'DDC_IMAGE_BASE', DDC_ENDPOINT_BASE . 'sites/dirtdirectory.org/files/' );
}

require DDC_PLUGIN_DIR . 'includes/template.php';
require DDC_PLUGIN_DIR . 'includes/functions.php';

if ( is_admin() ) {
	require DDC_PLUGIN_DIR . 'includes/admin.php';
}

/**
 * Load plugin files.
 *
 * @since 1.0.0
 */
function ddc_include() {
	require DDC_PLUGIN_DIR . 'includes/dirt-directory-client.php';
	require DDC_PLUGIN_DIR . 'includes/bp-integration.php';
}
add_action( 'bp_include', 'ddc_include' );
