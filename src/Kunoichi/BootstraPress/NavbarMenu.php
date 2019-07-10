<?php

namespace Kunoichi\BootstraPress;

/**
 * Convert WP Menu to bootstrap
 *
 * @package bootstrapress
 */
class NavbarMenu {
	
	/**
	 * @var string Theme location.
	 */
	public $theme_location = '';
	
	private $args = [];
	
	/**
	 * Constructor.
	 *
	 * @param string $theme_location
	 * @param array  $args
	 */
	public function __construct( $theme_location, $args = [] ) {
		$this->theme_location = $theme_location;
		$this->args = wp_parse_args( $args, [
			'item_class'   => 'nav-item',
			'active_class' => 'active',
		] );
		// Customize li.
		add_filter( 'nav_menu_css_class', [ $this, 'nav_menu_css_class' ], 10, 4 );
		// Customize a.
		add_filter( 'nav_menu_link_attributes', [ $this, 'nav_menu_link_attributes' ], 10, 4 );
	}
	
	/**
	 * Customize list item class.
	 *
	 * @param string[] $classes
	 * @param \WP_Post  $item
	 * @param \stdClass $args
	 * @param int      $depth
	 * @return array
	 */
	public function nav_menu_css_class( $classes, $item, $args, $depth ) {
		if ( $this->theme_location !== $args->theme_location ) {
			// This is not my location!
			return $classes;
		}
		// Add Bootstrap style.
		if ( $this->args['item_class'] ) {
			$classes[] = $this->args['item_class'];
		}
		if ( $item->current && $this->args['active_class'] ) {
			$classes[] = $this->args['active_class'];
		}
		return $classes;
	}
	
	/**
	 * Customize link attributes.
	 *
	 * @param array     $attributes
	 * @param \WP_Post  $item
	 * @param \stdClass $args
	 * @param int       $depth
	 * @return array
	 */
	public function nav_menu_link_attributes( $attributes, $item, $args, $depth ) {
		if ( $this->theme_location !== $args->theme_location ) {
			// This is not my turn.
			return $attributes;
		}
		$classes = isset( $attributes['class'] ) ? explode( ' ', $attributes['class'] ) : [];
		$classes[] = 'nav-link';
		$attributes['class'] = implode( ' ', $classes );
		return $attributes;
	}
}
