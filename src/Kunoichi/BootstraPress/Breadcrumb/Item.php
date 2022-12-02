<?php

namespace Kunoichi\BootstraPress\Breadcrumb;

/**
 * Breadcrumb page item.
 *
 * @package bootstrapress
 * @property-read string $label
 * @property-read string $link
 * @property-read string $rel
 * @property-read bool   $current
 */
class Item {


	private $_label = '';

	private $_link = '';

	private $args = [];

	/**
	 * Constructor
	 *
	 * @param string $label
	 * @param string $link
	 * @param array $args
	 */
	public function __construct( $label, $link = '', $args = [] ) {
		$this->_label = $label;
		$this->_link  = $link;
		$this->args   = $args;
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'label':
				return $this->_label;
			case 'link':
				return $this->_link;
			default:
				if ( isset( $this->args[ $name ] ) ) {
					return $this->args[ $name ];
				} else {
					return null;
				}
		}
	}
}
