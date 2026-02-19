<?php
/**
 * User access and permissions admin settings module (pro).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_User_Access' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Settings module for user access and permissions (pro feature).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_User_Access extends Gutena_Forms_Forms_Settings {
		/**
		 * Register the user-access settings module with the gutena_forms__settings filter.
		 *
		 * @since 1.7.0
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
		 * Get settings definition for the user access template (pro).
		 *
		 * @since 1.7.0
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
		 * Save settings (no-op for user-access module).
		 *
		 * @since 1.7.0
		 * @param array $settings Settings to save. Unused.
		 */
		public function save_settings( $settings ) {
			// dummy function.
		}
	}

	Gutena_Forms_User_Access::register_module();
endif;
