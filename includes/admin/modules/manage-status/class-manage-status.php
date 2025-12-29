<?php
/**
 * Class Manage Status
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Manage_Status' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Class to manage status settings module.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Manage_Status extends Gutena_Forms_Forms_Settings {
		/**
		 * Register Module
		 *
		 * @since 1.6.0
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
		 * Get Settings
		 *
		 * @since 1.6.0
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
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param $settings
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Manage_Status::register_module();
endif;
