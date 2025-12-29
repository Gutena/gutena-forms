<?php
/**
 * Class User Access
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_User_Access' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Gutena Forms User Access Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_User_Access extends Gutena_Forms_Forms_Settings {
		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['user-access'] = __CLASS__;
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
				'title'       => __( 'User Access Management', 'gutena-forms' ),
				'description' => __( 'Manage user access and permissions to control form data security and privacy.', 'gutena-forms' ),
				'is-pro'	  => true,
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'user-access',
					),
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings array Settings array.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_User_Access::register_module();
endif;
