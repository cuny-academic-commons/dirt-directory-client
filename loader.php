<?php

/*
Plugin Name: DiRT Directory Client
Version: 1.0
Description: Interface with the DiRT Directory API http://dirt.projectbamboo.org
Author: Boone B Gorges
Author URI: http://boone.gorg.es
Text Domain: dirt-directory-client
Domain Path: /languages
*/

define( 'DDC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DDC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'DDC_ENDPOINT_URL' ) ) {
	define( 'DDC_ENDPOINT_URL', 'http://dev.bamboodirt.gotpantheon.com/services/' );
}

function ddc_include() {
	require DDC_PLUGIN_DIR . 'includes/dirt-directory-client.php';
	require DDC_PLUGIN_DIR . 'includes/bp-integration.php';
}
add_action( 'bp_include', 'ddc_include' );

