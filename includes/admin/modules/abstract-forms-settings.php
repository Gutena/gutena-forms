<?php
/**
 * Abstract Class for Forms Settings Modules
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Abstract Class for Forms Settings Modules
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	abstract class Gutena_Forms_Forms_Settings {
		/**
		 * Register this settings module (e.g. add to gutena_forms__settings filter).
		 *
		 * @since 1.7.0
		 */
		abstract public static function register_module();

		/**
		 * Get the settings definition (title, description, fields) for the admin UI.
		 *
		 * @since 1.7.0
		 * @return array Settings array for the module.
		 */
		abstract public function get_settings();


		/**
		 * Save settings to storage.
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save.
		 * @return bool True on success, false on failure.
		 */
		abstract public function save_settings( $settings );
	}
endif;
