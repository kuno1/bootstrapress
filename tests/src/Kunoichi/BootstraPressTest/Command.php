<?php

namespace Kunoichi\BootstraPressTest;


class Command extends \WP_CLI_Command {
	
	/**
	 * Install unit test data.
	 *
	 * @param array $args
	 * @param array $assoc
	 * @synopsis [--locale=<locale>]
	 */
	public function unit_test( $args, $assoc ) {
		$locale = isset( $assoc['locale'] ) ? $assoc['locale'] : 'en_US';
		switch ( $locale ) {
		
		}
	}
	
}
