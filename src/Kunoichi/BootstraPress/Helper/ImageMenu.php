<?php

namespace Kunoichi\BootstraPress\Helper;

use Kunoichi\BootstraPress\Asset;

/**
 * Add image selector for menu.
 *
 * @package bootstrapress
 */
class ImageMenu {

	private static $initialized = false;

	protected $location = '';

	/**
	 * Constructor
	 *
	 * @param string $theme_location
	 * @param array $args
	 */
	public function __construct( $theme_location, $args = [] ) {
		if ( ! self::$initialized ) {
			Asset::register_script( 'bootstrapress-menu', '/js/edit-menu.js', [ 'jquery', 'wp-api-fetch', 'wp-i18n' ] );
			Asset::register_style( 'bootstrapress-menu', 'css/nav-menu.css' );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_script' ] );
			add_action( 'rest_api_init', [ $this, 'rest_api_init' ] );
			self::$initialized = true;
		}
		$this->location = $theme_location;
	}

	/**
	 * Enqueue page.
	 *
	 * @param string $page
	 */
	public function enqueue_script( $page ) {
		$screen = get_current_screen();
		switch ( $screen->base ) {
			case 'customize':
			case 'nav-menus':
				// Media library.
				wp_enqueue_media();
				// Helper scripts.
				wp_enqueue_script( 'bootstrapress-menu' );
				wp_localize_script( 'bootstrapress-menu', 'BootStrapMenu', apply_filters( 'bootstrapress_menu_image_labels', [
					'title'  => __( 'Images' ),
					'button' => __( 'Select' ),
					'open'   => __( 'Media Library' ),
					'label'  => __( 'Image for Menu' ),
				] ) );
				// CSS.
				wp_enqueue_style( 'bootstrapress-menu' );
				break;
		}
	}

	/**
	 * Register REST API.
	 */
	public function rest_api_init() {
		register_rest_route( 'bootstrapress/v1', 'menu/(?P<post_id>\d+)/image', [
			[
				'methods'             => [ 'GET', 'POST', 'DELETE' ],
				'args'                => [
					'post_id'       => [
						'required'          => true,
						'type'              => 'integer',
						'description'       => 'REST Endpoint for menu image.',
						'validate_callback' => function( $var ) {
							return is_numeric( $var ) && ( 'nav_menu_item' === get_post_type( $var ) );
						},
					],
					'attachment_id' => [
						'type'              => 'integer',
						'description'       => 'Attachment image to assign.',
						'validate_callback' => function( $var ) {
							return is_numeric( $var ) && wp_get_attachment_url( $var );
						},
					],
				],
				'permission_callback' => function( \WP_REST_Request $request ) {
					return current_user_can( 'edit_theme_options' );
				},
				'callback'            => function( \WP_REST_Request $request ) {
					try {
						$menu = get_post( $request->get_param( 'post_id' ) );
						switch ( $request->get_method() ) {
							case 'GET':
								return $this->handle_get( $menu );
							case 'DELETE':
								return $this->handle_delete( $menu );
							case 'POST':
								$attachment = get_post( $request->get_param( 'attachment_id' ) );
								if ( ! $attachment ) {
									throw new \Exception( 'No image specified.', 400 );
								}
								return $this->handle_post( $menu, $attachment );
						}
					} catch ( \Exception $e ) {
						return new \WP_Error( 'invalid_menu_image', $e->getMessage(), [
							'status' => $e->getCode(),
						] );
					}
				},
			],
		] );
	}

	/**
	 * Handle GET request.
	 *
	 * @param \WP_Post $post
	 *
	 * @return \WP_REST_Response
	 */
	protected function handle_get( $post ) {
		try {
			$attachment_id = get_post_meta( $post->ID, '_menu_image', true );
			if ( ! $attachment_id ) {
				throw new \Exception( 'Image not found.' );
			}
			$attachment = get_post( $attachment_id );
			if ( ! $attachment ) {
				throw new \Exception( 'Image not found.' );
			}
			return new \WP_REST_Response( [
				'success' => true,
				'id'      => $attachment_id,
				'src'     => wp_get_attachment_image_url( $attachment_id, 'thumbnail' ),
				'title'   => get_the_title( $attachment ),
			] );
		} catch ( \Exception $e ) {
			return new \WP_REST_Response( [
				'success' => false,
				'id'      => 0,
				'src'     => '',
				'title'   => '',
			] );
		}
	}

	/**
	 * Attach image to menu item.
	 *
	 * @param \WP_Post $post
	 * @param \WP_Post $attachment
	 * @return \WP_REST_Response
	 */
	protected function handle_post( $post, $attachment ) {
		update_post_meta( $post->ID, '_menu_image', $attachment->ID );
		return new \WP_REST_Response( [
			'success' => true,
			'id'      => $attachment->ID,
			'src'     => wp_get_attachment_image_url( $attachment->ID, 'thumbnail' ),
			'title'   => get_the_title( $attachment ),
		] );
	}

	/**
	 *
	 *
	 * @param \WP_Post $post
	 *
	 * @return \WP_REST_Response
	 */
	protected function handle_delete( $post ) {
		delete_post_meta( $post->ID, '_menu_image' );
		return new \WP_REST_Response( [
			'success' => true,
			'message' => 'Image was deleted.',
		] );
	}

	/**
	 * Detect if menu has image.
	 *
	 * @param int|\WP_Post $menu
	 * @return bool
	 */
	public static function has_image( $menu ) {
		return (bool) self::get_menu_image_id( $menu );
	}

	/**
	 * Get menu image ID.
	 *
	 * @param int|\WP_Post $menu
	 *
	 * @return int
	 */
	public static function get_menu_image_id( $menu ) {
		$post = get_post( $menu );
		if ( ! $post || 'nav_menu_item' !== $post->post_type ) {
			return 0;
		}
		return (int) get_post_meta( $post->ID, '_menu_image', true );
	}

	/**
	 * Get menu image URL.
	 *
	 * @param int|\WP_Post $menu
	 * @param string       $size Default 'thumbnail'
	 * @return string
	 */
	public static function get_menu_image_url( $menu, $size = 'thumbnail' ) {
		$id = self::get_menu_image_id( $menu );
		if ( ! $id ) {
			return '';
		}
		return wp_get_attachment_image_url( $id, $size );
	}
}
