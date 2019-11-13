<?php

namespace Kunoichi\BootstraPress;

/**
 * Class Asset
 * @package Kunoichi\BootstraPress
 */
class Asset {
	
	private static $instance = null;
	
	private $scripts = [];
	
	/**
	 * Asset constructor.
	 */
	private function __construct() {
		// Avoid initialize.
		add_action( 'init', [ $this, 'register_assets' ] );
	}
	
	/**
	 * Register assets.
	 */
	public function register_assets() {
		foreach ( $this->scripts as list( $handle, $url, $deps, $version, $in_footer ) ) {
			wp_register_script( $handle, $url, $deps, $version, $in_footer );
		}
	}
	
	/**
	 * Register script to enqueue.
	 *
	 * @param string $handle
	 * @param string $rel_path Relative path from library root.
	 * @param array $deps
	 * @param null $version
	 * @param bool $in_footer
	 *
	 * @return bool
	 */
	public static function register_script( $handle, $rel_path, $deps = [], $version = null, $in_footer = true ) {
		$path = self::path_from_rel_path( $rel_path );
		if ( ! file_exists( $path ) ) {
			return false;
		}
		if ( is_null( $version ) ) {
			$version = filemtime( $path );
		}
		$url = self::url_from_rel_path( $rel_path );
		self::get_instance()->scripts[] = [ $handle, $url, $deps, $version, $in_footer ];
		return true;
	}
	
	/**
	 * Get file path from relative path.
	 *
	 * @return string
	 */
	private static function path_from_rel_path( $rel_path ) {
		return dirname( dirname( dirname( __DIR__ ) ) ) . '/dist/' . ltrim( $rel_path, '/' );
	}
	
	/**
	 * Convert rel path to URL.
	 *
	 * @param string $rel_path
	 * @return string
	 */
	private static function url_from_rel_path( $rel_path ) {
		$path = self::path_from_rel_path( $rel_path );
		return str_replace( ABSPATH, home_url( '/' ), $path );
	}
	
	/**
	 * Get instance.
	 *
	 * @return self
	 */
	private static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
