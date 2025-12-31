<?php
/**
 * Class Forms
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Class Forms
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Forms extends Gutena_Forms_Forms_Settings {
		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['forms'] = __CLASS__;
					return $settings;
				}
			);
		}

		/**
		 * Get Settings
		 *
		 * @since 1.6.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'forms',
					)
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings Settings array.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Forms::register_module();
endif;
