<?php
/**
 * Class Gutena_Forms_Email_Admin
 *
 * Handles admin notification emails
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Admin' ) ) :
	/**
	 * Admin Notification Email Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Email_Admin extends Gutena_Forms_Email_Abstract {
		
		/**
		 * Form schema
		 * 
		 * @since 1.6.0
		 * @var array
		 */
		private $form_schema;
		
		/**
		 * Form submit data
		 * 
		 * @since 1.6.0
		 * @var array
		 */
		private $form_submit_data;
		
		/**
		 * Constructor
		 * 
		 * @since 1.6.0
		 * @param array $email_data Email context data
		 */
		public function __construct( $email_data = array() ) {
			parent::__construct( $email_data );
			
			$this->email_type = 'admin_notification';
			$this->email_name = __( 'Admin Notification', 'gutena-forms' );
			
			$this->form_schema     = ! empty( $email_data['form_schema'] ) ? $email_data['form_schema'] : array();
			$this->form_submit_data = ! empty( $email_data['form_submit_data'] ) ? $email_data['form_submit_data'] : array();
		}
		
		/**
		 * Check if admin notification should be sent
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function should_send() {
			if ( empty( $this->form_schema['form_attrs'] ) ) {
				return false;
			}
			
			// Check if admin email notification is enabled
			if ( isset( $this->form_schema['form_attrs']['emailNotifyAdmin'] ) && 
				 ( '' === $this->form_schema['form_attrs']['emailNotifyAdmin'] || 
				   false === $this->form_schema['form_attrs']['emailNotifyAdmin'] || 
				   '0' == $this->form_schema['form_attrs']['emailNotifyAdmin'] ) ) {
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
			// Data is already prepared in constructor
			return $this->email_data;
		}
		
		/**
		 * Get recipient email(s)
		 * 
		 * @since 1.6.0
		 * @return string|array Recipient email address(es)
		 */
		public function get_recipients() {
			if ( empty( $this->form_schema['form_attrs'] ) ) {
				return $this->default_from_email;
			}
			
			$admin_email = $this->default_from_email;
			$to = empty( $this->form_schema['form_attrs']['adminEmails'] ) ? $admin_email : $this->form_schema['form_attrs']['adminEmails'];
			
			return $to;
		}
		
		/**
		 * Get email subject
		 * 
		 * @since 1.6.0
		 * @return string Email subject
		 */
		public function get_subject() {
			if ( empty( $this->form_schema['form_attrs'] ) ) {
				return __( 'Form received', 'gutena-forms' ) . ' - ' . $this->default_from_name;
			}
			
			$blog_title = $this->default_from_name;
			$subject = sanitize_text_field( 
				empty( $this->form_schema['form_attrs']['adminEmailSubject'] ) 
					? __( 'Form received', 'gutena-forms' ) . ' - ' . $blog_title 
					: $this->form_schema['form_attrs']['adminEmailSubject'] 
			);
			
			return $subject;
		}
		
		/**
		 * Get email body content
		 * 
		 * @since 1.6.0
		 * @return string Email body
		 */
		public function get_body() {
			// Get body from email_data if provided
			$body = ! empty( $this->email_data['body'] ) ? $this->email_data['body'] : '';
			
			// Apply filter for admin email notification
			$body = apply_filters( 'gutena_forms_submit_admin_notification', $body, $this->form_submit_data );
			
			// Add pro upgrade message if not pro
			if ( ! Gutena_Forms_Helper::has_pro() ) {
				/**
				 * https://stackoverflow.com/questions/17602400/html-email-in-gmail-css-style-attribute-removed
				 */
				$body .= '<div style="background-color: #fffbeb; width: fit-content; margin-top: 50px; padding: 14px 15px 12px 15px; border-radius: 10px;" > <span style="font-size: 13px; line-height: 1; display: flex;" > <span style="margin-right: 5px;" > </span> <span style="margin-right: 3px;" ><strong>' . __( 'Exciting News!', 'gutena-forms' ) . ' </strong></span> '. __( 'Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="'.esc_url( admin_url( 'admin.php?page=gutena-forms' ) ).'" style="color: #E35D3F; margin-left: 1rem;" target="_blank" > ' . __( 'See all Entries', 'gutena-forms' ) . ' </a></strong></span></div>';
			}
			
			return $body;
		}
		
		/**
		 * Get from name
		 * 
		 * @since 1.6.0
		 * @return string From name
		 */
		public function get_from_name() {
			if ( empty( $this->form_schema['form_attrs'] ) ) {
				return parent::get_from_name();
			}
			
			$from_name = empty( $this->form_schema['form_attrs']['emailFromName'] ) 
				? $this->default_from_name 
				: $this->form_schema['form_attrs']['emailFromName'];
			
			return sanitize_text_field( $from_name );
		}
		
		/**
		 * Get reply-to header
		 * 
		 * @since 1.6.0
		 * @return string Reply-to header value
		 */
		public function get_reply_to() {
			if ( empty( $this->form_submit_data ) ) {
				return '';
			}
			
			$reply_to_email = ! empty( $this->form_submit_data['replyToEmail'] ) ? $this->form_submit_data['replyToEmail'] : '';
			
			if ( empty( $reply_to_email ) ) {
				return '';
			}
			
			$reply_to_name = '';
			if ( ! empty( $this->form_submit_data['replyToFname'] ) ) {
				$reply_to_name = $this->form_submit_data['replyToFname'];
				if ( ! empty( $this->form_submit_data['replyToLname'] ) ) {
					$reply_to_name .= ' ' . $this->form_submit_data['replyToLname'];
				}
			}
			
			if ( empty( $reply_to_name ) ) {
				$reply_to_name = $reply_to_email;
			}
			
			return esc_html( $reply_to_name ) . ' <' . sanitize_email( $reply_to_email ) . '>';
		}
	}
endif;

