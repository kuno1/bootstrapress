<?php
/*
	Plugin Name: BootstraPress
	Plugin URI: https://github.com/kuno1/bootstrapress
	Description: Twitter Bootstrap UI wrapper.
	Author: Kunoichi INC.
	Version: 0.4.3
	Author URI: https://kunoichiwp.com
 */

use Kunoichi\BootstraPress\Helper\ImageMenu;
use Kunoichi\BootstraPress\NavbarMenu;

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
// Add menu
new NavbarMenu( 'menu-1', [

] );

new ImageMenu( 'menu-1' );

add_filter( 'nav_menu_css_class', function( $classes, $item, $depth )  {
	if ( ImageMenu::has_image( $item ) ) {
		$classes[] = 'menu-item-with-img';
	}
	return $classes;
}, 10, 3 );

add_filter( 'nav_menu_item_title', function( $title, $item, $args, $depth ) {
	if ( ImageMenu::has_image( $item ) ) {
		$title = sprintf(
			'<img src="%s" alt="%s" class="menu-image" /><span class="menu-string">%s</span>',
			esc_url( ImageMenu::get_menu_image_url( $item ) ),
			esc_attr( $title ),
			esc_html( $title )
		);
	}
	return $title;
}, 10, 4 );

add_filter( 'the_content', function( $content ) {
	if ( is_singular() ) {
		ob_start();
		\Kunoichi\BootstraPress\Breadcrumb::display();
		$breadcrumb = ob_get_contents();
		ob_end_clean();
		$content =  $breadcrumb . $content;
	}
	return $content;
} );

