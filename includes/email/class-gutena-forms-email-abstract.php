<?php
/**
 * Abstract Class Gutena_Forms_Email_Abstract
 *
 * Base class for all email implementations
 * 
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Abstract' ) ) :
	/**
	 * Abstract Email Class
	 *
	 * @since 1.6.0
	 */
	abstract class Gutena_Forms_Email_Abstract {
		
		/**
		 * Email type identifier (unique key)
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $email_type;
		
		/**
		 * Email type name (human-readable)
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $email_name;
		
		/**
		 * Email context data
		 * 
		 * @since 1.6.0
		 * @var array
		 */
		protected $email_data;
		
		/**
		 * Default from name
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $default_from_name;
		
		/**
		 * Default from email
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $default_from_email;
		
		/**
		 * Constructor
		 * 
		 * @since 1.6.0
		 * @param array $email_data Email context data
		 */
		public function __construct( $email_data = array() ) {
			$this->email_data = $email_data;
			$this->default_from_name  = get_bloginfo( 'name' );
			$this->default_from_email = sanitize_email( get_option( 'admin_email' ) );
		}
		
		/**
		 * Check if this email should be sent
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		abstract public function should_send();
		
		/**
		 * Prepare email-specific data
		 * 
		 * @since 1.6.0
		 * @return array Prepared email data
		 */
		abstract public function prepare_email_data();
		
		/**
		 * Get recipient email(s)
		 * 
		 * @since 1.6.0
		 * @return string|array Recipient email address(es)
		 */
		abstract public function get_recipients();
		
		/**
		 * Get email subject
		 * 
		 * @since 1.6.0
		 * @return string Email subject
		 */
		abstract public function get_subject();
		
		/**
		 * Get email body content
		 * 
		 * @since 1.6.0
		 * @return string Email body
		 */
		abstract public function get_body();
		
		/**
		 * Get email headers
		 * 
		 * @since 1.6.0
		 * @return array Email headers
		 */
		public function get_headers() {
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
			);
			
			$from_name  = $this->get_from_name();
			$from_email = $this->get_from_email();
			
			if ( ! empty( $from_name ) && ! empty( $from_email ) ) {
				$headers[] = 'From: ' . esc_html( $from_name ) . ' <' . $from_email . '>';
			}
			
			$reply_to = $this->get_reply_to();
			if ( ! empty( $reply_to ) ) {
				$headers[] = 'Reply-To: ' . $reply_to;
			}
			
			return $headers;
		}
		
		/**
		 * Get from name
		 * 
		 * @since 1.6.0
		 * @return string From name
		 */
		public function get_from_name() {
			return $this->default_from_name;
		}
		
		/**
		 * Get from email
		 * 
		 * @since 1.6.0
		 * @return string From email
		 */
		public function get_from_email() {
			return $this->default_from_email;
		}
		
		/**
		 * Get reply-to header
		 * 
		 * @since 1.6.0
		 * @return string Reply-to header value
		 */
		public function get_reply_to() {
			return '';
		}
		
		/**
		 * Main method to send email
		 * 
		 * @since 1.6.0
		 * @return bool|WP_Error True on success, WP_Error on failure
		 */
		public function send() {
			if ( ! $this->should_send() ) {
				return new WP_Error( 'email_not_enabled', __( 'Email sending is not enabled for this type.', 'gutena-forms' ) );
			}
			
			// Prepare email data
			$this->prepare_email_data();
			
			// Get email components
			$recipients = $this->get_recipients();
			$subject    = $this->get_subject();
			$body       = $this->get_body();
			$headers    = $this->get_headers();
			
			// Validate recipients
			$recipients = $this->sanitize_recipients( $recipients );
			if ( empty( $recipients ) ) {
				return new WP_Error( 'invalid_recipients', __( 'No valid recipients found.', 'gutena-forms' ) );
			}
			
			// Apply filters before sending
			$recipients = apply_filters( 'gutena_forms_email_recipients', $recipients, $this->email_type, $this->email_data );
			$subject    = apply_filters( 'gutena_forms_email_subject', $subject, $this->email_type, $this->email_data );
			$body       = apply_filters( 'gutena_forms_email_body', $body, $this->email_type, $this->email_data );
			$headers    = apply_filters( 'gutena_forms_email_headers', $headers, $this->email_type, $this->email_data );
			
			// Prepare HTML body
			$body = $this->prepare_html_body( $body, $subject );
			
			// Send email
			$result = wp_mail( $recipients, esc_html( $subject ), $body, $headers );
			
			if ( ! $result ) {
				return new WP_Error( 'email_send_failed', __( 'Failed to send email.', 'gutena-forms' ) );
			}
			
			return true;
		}
		
		/**
		 * Sanitize and validate recipient email addresses
		 * 
		 * @since 1.6.0
		 * @param string|array $recipients Recipient email(s)
		 * @return array Array of valid email addresses
		 */
		protected function sanitize_recipients( $recipients ) {
			if ( empty( $recipients ) ) {
				return array();
			}
			
			if ( ! is_array( $recipients ) ) {
				$recipients = explode( ',', $recipients );
			}
			
			$valid_recipients = array();
			
			foreach ( $recipients as $recipient ) {
				$recipient = trim( $recipient );
				$recipient = sanitize_email( $recipient );
				
				if ( is_email( $recipient ) ) {
					$valid_recipients[] = $recipient;
				}
			}
			
			return $valid_recipients;
		}
		
		/**
		 * Prepare HTML email body structure
		 * 
		 * @since 1.6.0
		 * @param string $body Email body content
		 * @param string $subject Email subject
		 * @return string HTML email body
		 */
		protected function prepare_html_body( $body, $subject ) {
			$body = wpautop( $body, true );
			
			$lang = function_exists( 'get_language_attributes' ) ? get_language_attributes( 'html' ) : 'lang="en"';
			
			return '
			<!DOCTYPE html>
			<html ' . $lang . '>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>' . esc_html( $subject ) . '</title>
				</head>
				<body style="margin:0;padding:0;background:#ffffff;">
				' . $body . '
				</body>
			</html>
			';
		}
		
		/**
		 * Replace placeholders in text
		 * 
		 * @since 1.6.0
		 * @param string $text Text with placeholders
		 * @param array $placeholders Placeholder array (key => value)
		 * @return string Text with replaced placeholders
		 */
		protected function replace_placeholders( $text, $placeholders = array() ) {
			if ( empty( $placeholders ) || empty( $text ) ) {
				return $text;
			}
			
			foreach ( $placeholders as $key => $value ) {
				$text = str_replace( '{' . $key . '}', $value, $text );
			}
			
			return $text;
		}
		
		/**
		 * Get email type
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_email_type() {
			return $this->email_type;
		}
		
		/**
		 * Get email name
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_email_name() {
			return $this->email_name;
		}
	}
endif;

