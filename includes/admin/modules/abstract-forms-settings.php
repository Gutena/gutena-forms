<?php
/**
 * Abstract Class for Forms Settings Modules
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Abstract Class for Forms Settings Modules
	 *
	 * @since 1.6.0
	 * @package Gutena Forms
	 */
	abstract class Gutena_Forms_Forms_Settings {
		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		abstract public static function register_module();

		/**
		 * Get Settings
		 *
		 * @since 1.6.0
		 * @return array
		 */
		abstract public function get_settings();


		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings Settings to save.
		 *
		 * @return bool
		 */
		abstract public function save_settings( $settings );
	}
endif;
