<?php
/**
 * Entries admin module: form submissions list and entry management.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Admin settings module for viewing and managing form entries.
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Entries extends Gutena_Forms_Forms_Settings {
		/**
		 * Singleton instance
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Entries $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Register the entries and entry settings modules with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['entries'] = __CLASS__;
					$settings['entry']   = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance.
		 *
		 * @since 1.7.0
		 * @return Gutena_Forms_Entries
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Load entries model and REST endpoints.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			require_once plugin_dir_path( __FILE__ ) . 'class-entries-model.php';
			require_once plugin_dir_path( __FILE__ ) . 'class-entries-endpoints.php';
		}

		/**
		 * Get settings definition for the entries list template.
		 *
		 * @since 1.7.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'entries',
					)
				),
			);
		}

		/**
		 * Save settings (no-op for entries module).
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save. Unused.
		 */
		public function save_settings( $settings ) {
			// placeholder function.
		}
	}

	Gutena_Forms_Entries::register_module();
endif;
