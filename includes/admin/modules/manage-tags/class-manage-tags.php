<?php
/**
 * Manage Tags admin settings module.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Manage_Tags' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Settings module for entry tags management (pro feature).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Manage_Tags extends Gutena_Forms_Forms_Settings {
		/**
		 * Register the manage-tags settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
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
		 * Get settings definition for the tags management template (pro).
		 *
		 * @since 1.7.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title' 	  => __( 'Tags Management', 'gutena-forms' ),
				'description' => __( 'Categorize and sort form entries using tags for efficient organization and reporting.', 'gutena-forms' ),
				'is-pro'	  => true,
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'manage-tags',
					)
				),
			);
		}

		/**
		 * Save settings (no-op for manage-tags module).
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save. Unused.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Manage_Tags::register_module();
endif;
