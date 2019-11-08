<?php

namespace Kunoichi\BootstraPress\Customizer;


/**
 * Color ColorController
 *
 * @package bootstrapress
 */
class ColorController {
	
	/**
	 * @var array Setting.
	 */
	protected $args = [];
	
	protected $colors = [];
	
	/**
	 * Constructor
	 *
	 * @param array  $colors Should be array of color.
	 *   $colors = [
	 *       'primary' => [
	 *           'label' => __( 'Primary', 'domain' )
	 *           'color' => '#0066cc',
	 *       ],
	 *       'secondary' => [
	 *           'label' => __( 'Secondary', 'domain' )
	 *           'color' => '#cccccc',
	 *       ],
	 *   ];
	 * @param array $args
	 */
	public function __construct( $colors, $args = [] ) {
		$this->colors = $colors;
		$this->args = wp_parse_args( $args, [
			'section'        => 'colors',
			'create_section' => false,
			'section_label'  => __( 'Colors' ),
			'priority'       => 30,
			'prefix'         => 'color_'
		] );
		// Register settings.
		add_action( 'customize_register', [ $this, 'register' ], 11 );
	}
	
	
	/**
	 * Register Customizer.
	 *
	 * @param \WP_Customize_Manager $wp_customize
	 */
	public function register( $wp_customize ) {
		// Register section if specified.
		if ( $this->args['create_section'] ) {
			$wp_customize->add_section( $this->args['section'], [
				'title'    => $this->args['section_label'],
				'priority' => $this->args['priority'],
			] );
		}
		// Register color controls.
		foreach ( $this->colors as $name => $color ) {
			$id = $this->args['prefix'] . $name;
			// Add setting.
			$wp_customize->add_setting( $id, [
				'default' => $color['color'],
			] );
			// Add controller
			$wp_customize->add_control( new \WP_Customize_Color_Control( $wp_customize, $id, [
				'label'   => $color['label'],
				'section' => $this->args['section'],
			] ) );
		}
	}
}
