<?php

namespace Kunoichi\BootstraPress\Css;


/**
 * Extract css properties.
 *
 * @package boostrapress
 * @property-read bool   $is_css
 * @property-read bool   $is_scss
 * @property-read string $content
 */
class Extractor {
	
	/**
	 * @var string CSS path.
	 */
	protected $path = '';
	
	/**
	 * Content cache.
	 *
	 * @var string|null
	 */
	protected $_content = null;
	
	/**
	 * Extractor constructor.
	 *
	 * @param $path
	 */
	public function __construct( $path ) {
		$this->path = $path;
	}
	
	/**
	 * Extract property.
	 *
	 * @param string $selector
	 * @return string
	 */
	public function extract( $selector, $property ) {
		$selector = str_replace( '.', '\\.', trim( $selector ) );
		if ( ! preg_match( '/' . $selector . '\{([^}]+)}/u', $this->content, $match ) ) {
			return '';
		}
		list( $whole, $content ) = $match;
		if ( ! preg_match( '/' . $property . ' *?:([^;}]+)/u', $content, $prop_match ) ) {
			return '';
		}
		return trim( str_replace( '!important', '', $prop_match[1] ) );
	}
	
	/**
	 * Get theme names.
	 *
	 * @return string[]
	 */
	public function get_themes() {
		$themes = [
			'primary',
			'secondary',
			'success',
			'info',
			'warning',
			'danger',
			'light',
			'dark'
		];
		return apply_filters( 'bootstrapress_themes', $themes );
	}
	
	/**
	 * Extract heading font-size.
	 */
	public function get_text_sizes() {
		$sizes = [];
		foreach ( range( 1, 6 ) as $size ) {
			$font_size = $this->extract( ".h{$size},h{$size}", 'font-size' );
			if ( $font_size ) {
				$sizes[ "h{$size}" ] = $font_size;
			}
		}
		return $sizes;
	}
	
	/**
	 * Get theme colors.
	 *
	 * @return array
	 */
	public function get_text_colors() {
		return array_merge( [ 'white' => '#fff' ], $this->get_theme_colors(), [ 'black' => '#000' ] );
	}
	
	/**
	 * Get background colors.
	 *
	 * @param string $prefix
	 * @return array
	 */
	public function get_bg_colors( $prefix = '' ) {
		$colors = [
			'white' => '#fff',
		];
		foreach ( $this->get_themes() as $theme ) {
			$color = $this->extract( ".alert-{$theme}", 'background-color' );
			if ( $color ) {
				$colors[ $prefix . $theme ] = $color;
			}
		}
		$colors['black'] = '#000';
		return $colors;
	}
	
	/**
	 * Get color pallet.
	 *
	 * @return array
	 */
	public function get_color_palette() {
		return array_merge( $this->get_text_colors(), $this->get_bg_colors( 'bg-' ) );
	}
	
	/**
	 * Get theme color list.
	 *
	 * @return array
	 */
	public function get_theme_colors() {
		$colors = [];
		foreach ( $this->get_themes() as $theme ) {
			$colors[ $theme ] = $this->extract( ':root', "--{$theme}" );
		}
		return $colors;
	}
	
	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'content':
				if ( is_null( $this->_content ) ) {
					$this->_content = file_exists( $this->path ) ? file_get_contents( $this->path ) : '';
				}
				return $this->_content;
			case 'is_scss':
			case 'is_css':
				list( $is, $ext ) = explode( '_', $name );
				return (int) preg_match( '/\.' . $ext . '$/u', $this->path );
			default:
				return null;
		}
	}
}
