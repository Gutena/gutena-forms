<?php
/**
 * Form Templates module bootstrap.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Templates' ) ) :
	/**
	 * Bootstraps the form template library module.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Templates {
		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Form_Templates|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Form_Templates
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register the module and load dependencies.
		 *
		 * @since 2.2.0
		 */
		public static function register_module() {
			self::load_dependencies();
			self::get_instance();
		}

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		private function __construct() {
			Gutena_Forms_Form_Template_Registry::get_instance();
		}

		/**
		 * Load template library class files.
		 *
		 * @since 2.2.0
		 */
		private static function load_dependencies() {
			$base_path = plugin_dir_path( __FILE__ );

			require_once $base_path . 'abstract-class-form-template.php';
			require_once $base_path . 'class-form-template-fields.php';
			require_once $base_path . 'class-form-template-registry.php';
			require_once $base_path . 'class-form-template-builder.php';
			require_once $base_path . 'class-form-template-service.php';
			require_once $base_path . 'class-form-templates-endpoints.php';

			$template_files = array(
				'templates/class-volunteer-application.php',
				'templates/class-service-booking-request.php',
				'templates/class-event-rsvp.php',
				'templates/class-online-event-registration.php',
				'templates/class-lead-capture.php',
				'templates/class-request-a-quote.php',
				'templates/class-newsletter-signup.php',
				'templates/class-affiliate-signup.php',
				'templates/class-demo-request.php',
				'templates/class-maintenance-request.php',
				'templates/class-beta-access-request.php',
				'templates/class-customer-satisfaction-survey.php',
				'templates/class-product-feedback.php',
			);

			foreach ( $template_files as $template_file ) {
				$file_path = $base_path . $template_file;

				if ( file_exists( $file_path ) ) {
					require_once $file_path;
				}
			}
		}
	}

	Gutena_Forms_Form_Templates::register_module();
endif;
