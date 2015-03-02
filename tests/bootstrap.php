<?php

if ( ! defined( 'BP_TESTS_DIR' ) ) {
	define( 'BP_TESTS_DIR', dirname( __FILE__ ) . '/../../buddypress/tests/phpunit' );
}

if ( file_exists( BP_TESTS_DIR . '/bootstrap.php' ) ) {

	$_develop_dir = getenv( 'WP_DEVELOP_DIR' );
	if ( ! empty( $_develop_dir ) ) {
		$_tests_dir = $_develop_dir . '/tests/phpunit/';
	} else {
		$_tests_dir = getenv('WP_TESTS_DIR');
		if ( !$_tests_dir ) $_tests_dir = '/tmp/wordpress-tests-lib';
	}

	require_once $_tests_dir . '/includes/functions.php';

	function _manually_load_plugin() {
		require BP_TESTS_DIR . '/includes/loader.php';
		require dirname( __FILE__ ) . '/../loader.php';
	}
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

	require $_tests_dir . '/includes/bootstrap.php';

	require_once BP_TESTS_DIR . '/includes/testcase.php';
}

