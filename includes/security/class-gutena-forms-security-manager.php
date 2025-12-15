<?php
/**
 * Class Gutena_Forms_Security_Manager
 *
 * Manages all security implementations
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Security_Manager' ) ) :
	class Gutena_Forms_Security_Manager {

		/**
		 * Registered security implementations
		 *
		 * @since 1.6.0
		 * @var array
		 */
		private static $security_classes = array();

		/**
		 * Register a security implementation
		 *
		 * @since 1.6.0
		 * @param string $class_name Class name of the security implementation
		 * @return void
		 */
		public static function register( $class_name ) {
			if ( class_exists( $class_name ) && is_subclass_of( $class_name, 'Gutena_Forms_Security_Abstract' ) ) {
				self::$security_classes[] = $class_name;
			}
		}

		/**
		 * Get all registered security instances for a form
		 *
		 * @since 1.6.0
		 * @param array $form_schema Form schema
		 * @return Gutena_Forms_Security_Abstract[] Array of security instances
		 */
		public static function get_security_instances( $form_schema = array() ) {
			$instances = array();

			foreach ( self::$security_classes as $class_name ) {
				$instance = new $class_name( $form_schema );
				if ( $instance->is_enabled() ) {
					$instances[] = $instance;
				}
			}

			return $instances;
		}

		/**
		 * Verify all enabled security features
		 *
		 * @since 1.6.0
		 * @param array $form_schema Form schema
		 * @return array|true Returns true if all pass, or array with error info
		 */
		public static function verify_all( $form_schema = array() ) {
			$instances = self::get_security_instances( $form_schema );

			foreach ( $instances as $instance ) {
				if ( ! $instance->verify() ) {
					$error_response = array(
						'status'  => 'error',
						'message' => $instance->get_error_message(),
					);

					// Merge additional error data if available
					$error_data = $instance->get_error_data();
					if ( ! empty( $error_data ) ) {
						$error_response = array_merge( $error_response, $error_data );
					}

					return $error_response;
				}
			}

			return true;
		}

		/**
		 * Render all enabled security fields
		 *
		 * @since 1.6.0
		 * @param array $attributes Form attributes
		 * @param array $form_schema Form schema
		 * @return string Combined HTML output
		 */
		public static function render_all_fields( $attributes = array(), $form_schema = array() ) {
			$instances = self::get_security_instances( $form_schema );
			$html = '';

			foreach ( $instances as $instance ) {
				$html .= $instance->render_field( $attributes );
			}

			return $html;
		}
	}
endif;

