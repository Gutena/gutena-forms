<?php
/**
 * Class Gutena_Forms_Email_Manager
 *
 * Manages all email implementations
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Manager' ) ) :
	class Gutena_Forms_Email_Manager {

		/**
		 * Registered email implementations
		 *
		 * @since 1.6.0
		 * @var array
		 */
		private static $email_classes = array();

		/**
		 * Register an email implementation
		 *
		 * @since 1.6.0
		 * @param string $email_type Email type identifier
		 * @param string $class_name Class name of the email implementation
		 * @return void
		 */
		public static function register( $email_type, $class_name ) {
			if ( class_exists( $class_name ) && is_subclass_of( $class_name, 'Gutena_Forms_Email_Abstract' ) ) {
				self::$email_classes[ $email_type ] = $class_name;
			}
		}

		/**
		 * Get email instance by type
		 *
		 * @since 1.6.0
		 * @param string $email_type Email type identifier
		 * @param array $email_data Email context data
		 * @return Gutena_Forms_Email_Abstract|null Email instance or null if not found
		 */
		public static function get_email_instance( $email_type, $email_data = array() ) {
			if ( ! isset( self::$email_classes[ $email_type ] ) ) {
				return null;
			}

			$class_name = self::$email_classes[ $email_type ];
			return new $class_name( $email_data );
		}

		/**
		 * Send admin notification email
		 *
		 * @since 1.6.0
		 * @param array $email_data Email context data (should include form_schema, form_submit_data, body)
		 * @return bool|WP_Error True on success, WP_Error on failure
		 */
		public static function send_admin_notification( $email_data = array() ) {
			$email = self::get_email_instance( 'admin_notification', $email_data );
			
			if ( ! $email ) {
				return new WP_Error( 'email_class_not_found', __( 'Admin notification email class not found.', 'gutena-forms' ) );
			}
			
			return $email->send();
		}

		/**
		 * Send user auto-responder email
		 *
		 * @since 1.6.0
		 * @param array $email_data Email context data (should include form_schema, form_submit_data)
		 * @return bool|WP_Error True on success, WP_Error on failure
		 */
		public static function send_user_autoresponder( $email_data = array() ) {
			$email = self::get_email_instance( 'user_autoresponder', $email_data );
			
			if ( ! $email ) {
				return new WP_Error( 'email_class_not_found', __( 'User auto-responder email class not found.', 'gutena-forms' ) );
			}
			
			return $email->send();
		}

		/**
		 * Send weekly report email
		 *
		 * @since 1.6.0
		 * @param array $email_data Email context data (should include report_content)
		 * @return bool|WP_Error True on success, WP_Error on failure
		 */
		public static function send_weekly_report( $email_data = array() ) {
			$email = self::get_email_instance( 'weekly_report', $email_data );
			
			if ( ! $email ) {
				return new WP_Error( 'email_class_not_found', __( 'Weekly report email class not found.', 'gutena-forms' ) );
			}
			
			return $email->send();
		}
	}
endif;

