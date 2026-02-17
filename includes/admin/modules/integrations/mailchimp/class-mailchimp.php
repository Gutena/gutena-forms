<?php
/**
 * Mailchimp Integration Class
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Mailchimp' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Mailchimp extends Gutena_Forms_Forms_Settings {

		public static function register_module() {
		}

		public function get_settings() {
		}

		public function save_settings( $settings ) {
		}
	}

	Gutena_Forms_Mailchimp::register_module();
endif;
