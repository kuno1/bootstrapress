<?php

namespace Kunoichi\BootstraPress;

/**
 * Class Asset
 * @package Kunoichi\BootstraPress
 */
class Asset {
	
	private static $instance = null;
	
	private $scripts = [];

	private $styles = [];
	
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
		foreach ( $this->styles as list( $handle, $url, $deps, $version ) ) {
			wp_register_style( $handle, $url, $deps, $version );
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
		$info = self::get_url_and_version( $rel_path, $version );
		if ( ! $info ) {
			return false;
		}
		list( $url, $version_no ) = $info;
		self::get_instance()->scripts[] = [ $handle, $url, $deps, $version_no, $in_footer ];
		return true;
	}
	
	/**
	 * Register style assets.
	 *
	 * @param string $handle
	 * @param string $rel_path
	 * @param array  $deps
	 * @param null   $version
	 * @param string $screen
	 * @return bool
	 */
	public static function register_style( $handle, $rel_path, $deps = [], $version = null, $screen = 'screen' ) {
		$info = self::get_url_and_version( $rel_path, $version );
		if ( ! $info ) {
			return false;
		}
		list( $url, $version_no ) = $info;
		self::get_instance()->styles[] = [ $handle, $url, $deps, $version_no, $screen ];
		return true;
	}
	
	/**
	 * Get url and version from relative path.
	 *
	 * @param string $rel_path
	 * @param null|string $version
	 *
	 * @return array|bool
	 */
	private static function get_url_and_version( $rel_path, $version = null ) {
		$path = self::path_from_rel_path( $rel_path );
		if ( ! file_exists( $path ) ) {
			return false;
		}
		if ( is_null( $version ) ) {
			$version = filemtime( $path );
		}
		$url = self::url_from_rel_path( $rel_path );
		return [ $url, $version ];
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
