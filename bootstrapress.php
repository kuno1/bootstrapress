<?php
/*
	Plugin Name: BootstraPress
	Plugin URI: https://github.com/kuno1/bootstrapress
	Description: Twitter Bootstrap UI wrapper.
	Author: Kunoichi INC.
	Version: 0.2.0
	Author URI: https://kunoichiwp.com
 */

require __DIR__ . '/vendor/autoload.php';

// Load bootstrap.css
$controller = new Kunoichi\BootstraPress\Customizer\ColorController( [
	'primary' => [
		'label' => 'プライマリー',
		'color' => '#0073aa',
	],
	'secondary' => [
		'label' => 'セカンダリ',
		'color' => '#111',
	],
] );

$manager = new Kunoichi\BootstraPress\Customizer\CssManager( get_template_directory() . '/style.css', [
	'color_primary' => [
		'default' => '#0073aa',
	],
	'color_secondary' => [
		'default' => '#111',
	],
] );

