<?php
/**
 * Forms list and management admin settings module.
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Forms list and management admin settings module.
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Forms extends Gutena_Forms_Forms_Settings {
		/**
		 * Singleton instance.
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Forms $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.7.0
		 * @return Gutena_Forms_Forms
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Load forms model and REST endpoints.
		 *
		 * @since 1.7.0
		 */
		public function __construct() {
			require_once plugin_dir_path( __FILE__ ) . 'class-forms-model.php';
			require_once plugin_dir_path( __FILE__ ) . 'class-forms-endpoints.php';
		}

		/**
		 * Register the forms settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['forms'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get settings definition for the forms list template.
		 *
		 * @since 1.7.0
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
		 * Save settings (no-op for forms module).
		 *
		 * @since 1.7.0
		 * @param array $settings Settings array. Unused.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_Forms::register_module();
endif;
