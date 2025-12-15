<?php
/**
 * Class Gutena_Forms_Email_Weekly_Report
 *
 * Handles weekly report emails
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Weekly_Report' ) ) :
	/**
	 * Weekly Report Email Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Email_Weekly_Report extends Gutena_Forms_Email_Abstract {
		
		/**
		 * Report content
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		private $report_content;
		
		/**
		 * Constructor
		 * 
		 * @since 1.6.0
		 * @param array $email_data Email context data
		 */
		public function __construct( $email_data = array() ) {
			parent::__construct( $email_data );
			
			$this->email_type = 'weekly_report';
			$this->email_name = __( 'Weekly Report', 'gutena-forms' );
			
			$this->report_content = ! empty( $email_data['report_content'] ) ? $email_data['report_content'] : '';
		}
		
		/**
		 * Check if weekly report should be sent
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function should_send() {
			$settings = get_option( 'gutena_forms_weekly_report' );
			
			if ( ! is_array( $settings ) || empty( $settings['enabled'] ) ) {
				return false;
			}
			
			$email = is_array( $settings ) && ! empty( $settings['recipient_email'] ) ? $settings['recipient_email'] : '';
			
			if ( empty( $email ) || ! is_email( $email ) ) {
				return false;
			}
			
			return true;
		}
		
		/**
		 * Prepare email-specific data
		 * 
		 * @since 1.6.0
		 * @return array Prepared email data
		 */
		public function prepare_email_data() {
			// If report_content is not provided, generate it
			if ( empty( $this->report_content ) ) {
				$this->report_content = $this->get_email_content();
			}
			
			return $this->email_data;
		}
		
		/**
		 * Get recipient email(s)
		 * 
		 * @since 1.6.0
		 * @return string|array Recipient email address(es)
		 */
		public function get_recipients() {
			$settings = get_option( 'gutena_forms_weekly_report' );
			$email    = is_array( $settings ) && ! empty( $settings['recipient_email'] ) ? $settings['recipient_email'] : '';
			
			return $email;
		}
		
		/**
		 * Get email subject
		 * 
		 * @since 1.6.0
		 * @return string Email subject
		 */
		public function get_subject() {
			return __( 'Your Weekly Gutena Form Report', 'gutena-forms' );
		}
		
		/**
		 * Get email body content
		 * 
		 * @since 1.6.0
		 * @return string Email body
		 */
		public function get_body() {
			return $this->report_content;
		}
		
		/**
		 * Get email content from template
		 * 
		 * @since 1.6.0
		 * @return string Email content
		 */
		private function get_email_content() {
			ob_start();
			require GUTENA_FORMS_DIR_PATH . 'includes/email-report/templates/email-report-template.php';
			return ob_get_clean();
		}
	}
endif;

