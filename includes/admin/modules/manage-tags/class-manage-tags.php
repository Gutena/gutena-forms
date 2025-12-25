<?php
/**
 * Class Manage Tags
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Manage_Tags' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) {
	/**
	 * Class to manage tags settings module.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Manage_Tags extends Gutena_Forms_Forms_Settings {
		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['manage-tags'] = __CLASS__;
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
				'title' 	  => __( 'Tags Management', 'gutena-forms' ),
				'description' => __( 'Categorize and sort form entries using tags for efficient organization and reporting.', 'gutena-forms' ),
				'fields'      => array(
					'type' => 'template',
					'name' => 'manage-tags',
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param $settings
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Manage_Tags::register_module();
}
