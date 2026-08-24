<?php
/**
 * Payments list dashboard page module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Payments_List' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Payments_List extends Gutena_Forms_Forms_Settings {

		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['payments'] = __CLASS__;
					return $settings;
				}
			);
		}

		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'payments',
					),
				),
			);
		}

		public function save_settings( $settings ) {
			return true;
		}
	}

	Gutena_Forms_Payments_List::register_module();
endif;
