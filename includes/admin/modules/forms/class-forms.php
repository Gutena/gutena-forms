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
		 * Get Instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Forms $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Get Instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Forms
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		public function __construct() {
			require_once plugin_dir_path( __FILE__ ) . 'class-forms-endpoints.php';
		}

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

			self::get_instance();
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

		public static function get_form_name_by_id( $form_id ) {
			return 'Placeholder name';
		}
	}

	Gutena_Forms_Forms::register_module();
endif;
