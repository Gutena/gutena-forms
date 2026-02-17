<?php
/**
 * Active Campaign Integration Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Active_Campaign' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Active_Campaign extends Gutena_Forms_Forms_Settings {
		public static function register_module() {
		}

		public function get_settings() {
		}

		public function save_settings( $settings ) {
		}
	}

	Gutena_Forms_Active_Campaign::register_module();
endif;
