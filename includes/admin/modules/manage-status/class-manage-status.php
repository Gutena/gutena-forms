<?php
/**
 * Manage Status admin settings module.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Manage_Status' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Settings module for entry status management (pro feature).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Manage_Status extends Gutena_Forms_Forms_Settings {
		/**
		 * Register the manage-status settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['manage-status'] = __CLASS__;
					return $settings;
				}
			);
		}

		/**
		 * Get settings definition for the status management template (pro).
		 *
		 * @since 1.7.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title'       => __( 'Status Management', 'gutena-forms' ),
				'description' => __( 'Organize and track form submissions with customizable entry status for streamlined workflow.', 'gutena-forms' ),
				'is-pro'      => true,
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'manage-status',
					)
				),
			);
		}

		/**
		 * Save settings (no-op for manage-status module).
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save. Unused.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Manage_Status::register_module();
endif;
