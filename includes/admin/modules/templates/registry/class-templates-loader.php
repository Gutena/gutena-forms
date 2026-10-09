<?php
/**
 * Template class loader and manifest.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Loader' ) ) :
	/**
	 * Loads template class files and returns the manifest.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Loader {

		/**
		 * Relative template class files.
		 *
		 * @since 2.2.0
		 * @var string[]
		 */
		private static $template_files = array(
			'class-template-job-application.php',
			'class-template-volunteer-application.php',
			'class-template-appointment-request.php',
			'class-template-service-booking-request.php',
			'class-template-event-rsvp.php',
			'class-template-online-event-registration.php',
			'class-template-lead-capture.php',
			'class-template-quote-request.php',
			'class-template-newsletter-signup.php',
			'class-template-affiliate-signup.php',
			'class-template-demo-request.php',
			'class-template-maintenance-request.php',
			'class-template-beta-access-request.php',
			'class-template-customer-satisfaction-survey.php',
			'class-template-product-feedback.php',
		);

		/**
		 * Require all template class files.
		 *
		 * @since 2.2.0
		 */
		public static function load_files() {
			$base = GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/templates/templates/';

			foreach ( self::$template_files as $file ) {
				$path = $base . $file;
				if ( file_exists( $path ) ) {
					require_once $path;
				}
			}
		}

		/**
		 * Get registered template class names.
		 *
		 * @since 2.2.0
		 * @return string[]
		 */
		public static function get_classes() {
			self::load_files();

			$classes = array(
				'Gutena_Forms_Template_Job_Application',
				'Gutena_Forms_Template_Volunteer_Application',
				'Gutena_Forms_Template_Appointment_Request',
				'Gutena_Forms_Template_Service_Booking_Request',
				'Gutena_Forms_Template_Event_Rsvp',
				'Gutena_Forms_Template_Online_Event_Registration',
				'Gutena_Forms_Template_Lead_Capture',
				'Gutena_Forms_Template_Quote_Request',
				'Gutena_Forms_Template_Newsletter_Signup',
				'Gutena_Forms_Template_Affiliate_Signup',
				'Gutena_Forms_Template_Demo_Request',
				'Gutena_Forms_Template_Maintenance_Request',
				'Gutena_Forms_Template_Beta_Access_Request',
				'Gutena_Forms_Template_Customer_Satisfaction_Survey',
				'Gutena_Forms_Template_Product_Feedback',
			);

			/**
			 * Filter template class manifest.
			 *
			 * @since 2.2.0
			 * @param string[] $classes Template class names.
			 */
			return apply_filters( 'gutena_forms_template_classes', $classes );
		}

		/**
		 * Instantiate all registered template objects.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Abstract_Form_Template[]
		 */
		public static function get_instances() {
			$instances = array();

			foreach ( self::get_classes() as $class_name ) {
				if ( ! class_exists( $class_name ) ) {
					continue;
				}

				$instance = new $class_name();

				if ( $instance instanceof Gutena_Forms_Abstract_Form_Template ) {
					$instances[] = $instance;
				}
			}

			return $instances;
		}
	}
endif;
