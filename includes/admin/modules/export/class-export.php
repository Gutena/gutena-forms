<?php
/**
 * Export forms settings module (UI).
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Export' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Admin settings module for exporting Gutena Forms and entries.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Export extends Gutena_Forms_Forms_Settings {
		/**
		 * Register the export module with the gutena_forms__settings filter.
		 *
		 * @since 2.1.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['export'] = __CLASS__;
					return $settings;
				}
			);

			include_once plugin_dir_path( __FILE__ ) . 'class-export-endpoints.php';
		}

		/**
		 * Get settings definition for the export screen template.
		 *
		 * @since 2.1.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title'       => __( 'Export Gutena Forms & Entries', 'gutena-forms' ),
				'description' => __( 'Export your Gutena Forms as JSON files for migration or backup, or download entries from selected forms for reporting and analysis.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'export',
					),
				),
			);
		}

		/**
		 * Save settings (no-op for export module).
		 *
		 * @since 2.1.0
		 * @param array $settings Settings array. Unused.
		 * @return bool
		 */
		public function save_settings( $settings ) {
			return true;
		}
	}

	Gutena_Forms_Export::register_module();
endif;
