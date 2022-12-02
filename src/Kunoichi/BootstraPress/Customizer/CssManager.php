<?php

namespace Kunoichi\BootstraPress\Customizer;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;

/**
 * Bump CSS.
 *
 * @package bootstrapress
 */
class CssManager {

	protected $path = '';

	protected $rules = [];

	/**
	 * CssManager constructor.
	 *
	 * @param string $path Original CSS file path.
	 * @param array $rules
	 */
	public function __construct( $path, $rules ) {
		$this->path  = $path;
		$this->rules = $rules;
		add_action( 'update_option_theme_mods_' . get_option( 'stylesheet' ), [ $this, 'theme_mod_updated' ], 10, 2 );
		add_action( 'customize_preview_init', [ $this, 'update_css_preview' ], 1 );
	}

	/**
	 * Check if theme mod is changed.
	 *
	 * @param string $name
	 *
	 * @return bool|string
	 */
	public function is_mod_changed( $name ) {
		if ( ! isset( $this->rules[ $name ] ) ) {
			return false;
		}
		$rule = $this->rules[ $name ];
		if ( isset( $rule['value'] ) ) {
			return false;
		}
		$value  = get_theme_mod( $name );
		$forced = isset( $rule['forced'] ) ? $rule['forced'] : false;
		if ( ! $value && ! $forced ) {
			return false;
		}
		$default = [ $rule['default'], '' ];
		if ( preg_match( '/^#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])$/u', $default[0], $match ) ) {
			$color = '#';
			foreach ( range( 1, 3 ) as $index ) {
				$color .= $match[ $index ] . $match[ $index ];
			}
			$default[] = $color;
		}
		if ( in_array( $value, $default, true ) ) {
			return false;
		}
		return $value;
	}

	/**
	 * Update production CSS.
	 *
	 * @param array $old_value
	 * @param array $new_value
	 */
	public function theme_mod_updated( $old_value, $new_value ) {
		// Save Css or remove.
		$this->update_css( 'custom' );
	}

	/**
	 * Update CSS in preview pain.
	 */
	public function update_css_preview() {
		if ( ! ( is_customize_preview() && ! is_admin() ) ) {
			// No customizer, so this is not customizer screen.
			// If this is admin, not preview screen.
			return;
		}
		$this->update_css( 'preview' );
	}

	/**
	 * Update CSS file.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function update_css( $name ) {
		$path    = $this->get_custom_css_path( $name );
		$changed = false;
		foreach ( $this->rules as $key => $rule ) {
			if ( $this->is_mod_changed( $key ) ) {
				$changed = true;
				break;
			}
		}
		foreach ( $this->rules as $key => $rule ) {
			error_log( sprintf( '%s => %s', $key, get_theme_mod( $key ) ) );
		}
		if ( $changed ) {
			return $this->dump_css( $name );
		} elseif ( file_exists( $path ) ) {
			return unlink( $path );
		}
	}

	/**
	 * Get converted css.
	 *
	 * @return string
	 */
	public function get_css_content() {
		$content = '';
		if ( file_exists( $this->path ) ) {
			$content = file_get_contents( $this->path );
		}
		foreach ( $this->rules as $name => $rule ) {
			$value = '';
			if ( isset( $rule['value'] ) ) {
				$value = $rule['value'];
			} else {
				$mod_value = $this->is_mod_changed( $name );
				if ( $mod_value ) {
					$value = $mod_value;
				} else {
					continue;
				}
			}
			$content = str_replace( $rule['default'], $value, $content );
		}
		return $content;
	}

	/**
	 * Dump CSS.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function dump_css( $name = 'custom' ) {
		$path = $this->get_custom_css_path( $name );
		if ( ! $path ) {
			return false;
		}
		$content = $this->get_css_content();
		return (bool) file_put_contents( $path, $content );
	}

	/**
	 * Get custom css path.
	 *
	 * @param string $name
	 *
	 * @return bool|string
	 */
	protected function get_custom_css_path( $name = 'custom' ) {
		$dir = wp_upload_dir()['basedir'] . '/styles';
		if ( ! is_dir( $dir ) && ! mkdir( $dir, 0755 ) ) {
			return false;
		}
		return $dir . '/' . $name . '.css';
	}

	/**
	 * Get css URL.
	 *
	 * @return string[]
	 */
	public function get_css_url() {
		$name    = is_customize_preview() ? 'preview' : 'custom';
		$path    = $this->path;
		$changed = [];
		foreach ( $this->rules as $key => $rule ) {
			// If value is set, it means forced value.
			if ( isset( $rule['value'] ) ) {
				continue;
			}
			$mod = $this->is_mod_changed( $key );
			if ( ! $mod ) {
				continue;
			}
			$changed[] = $key . $mod;
		}
		$version = md5_file( $this->path );
		if ( $changed ) {
			// Add hash from changed value.
			$version .= md5( implode( ',', $changed ) );
			// Change file path.
			$path = $this->get_custom_css_path( $name );
		}
		$url = str_replace( ABSPATH, home_url( '/' ), $path );
		return [ $url, $version ];
	}
}
