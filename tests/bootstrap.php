<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Clinics
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
	exit( 1 );
}

/**
 * Get bootstrap functions.
 *
 * @throws \Exception
 * @return string Path of CSS file.
 */
function bootstrapress_dev_css() {
	$path = dirname( __DIR__ ) . '/dist/bootstrap.min.css';
	if ( ! file_get_contents( $path ) ) {
		throw new \Exception( 'Bootstrap file does not exist. Please run `npm run extract`.', 404 );
	}
	return $path;
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// Bootstrap
tests_add_filter( 'muplugins_loaded', function() {
	require_once dirname( __DIR__ ) . '/vendor/autoload.php';
} );


// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
