<?php
/**
 * Class Gutena_Forms_Email_User
 *
 * Handles user auto-responder emails
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_User' ) ) :
	/**
	 * User Auto-responder Email Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Email_User extends Gutena_Forms_Email_Abstract {
		
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
			
			$this->email_type = 'user_autoresponder';
			$this->email_name = __( 'User Auto-responder', 'gutena-forms' );
			
			$this->form_schema     = ! empty( $email_data['form_schema'] ) ? $email_data['form_schema'] : array();
			$this->form_submit_data = ! empty( $email_data['form_submit_data'] ) ? $email_data['form_submit_data'] : array();
		}
		
		/**
		 * Check if user auto-responder should be sent
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function should_send() {
			if ( empty( $this->form_schema['form_attrs'] ) ) {
				return false;
			}
			
			// Check if user email notification is enabled
			if ( empty( $this->form_schema['form_attrs']['emailNotifyUser'] ) || 
				 false === $this->form_schema['form_attrs']['emailNotifyUser'] || 
				 '0' == $this->form_schema['form_attrs']['emailNotifyUser'] ) {
				return false;
			}
			
			// Check if subject and template are set
			if ( empty( $this->form_schema['form_attrs']['userEmailSubject'] ) || 
				 empty( $this->form_schema['form_attrs']['userEmailTemplate'] ) ) {
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
			// Get user email from form submission data
			// Try to get from replyToEmail first (if set)
			$user_email = ! empty( $this->form_submit_data['replyToEmail'] ) 
				? $this->form_submit_data['replyToEmail'] 
				: '';
			
			// If not found, try to find email field in submit_data
			if ( empty( $user_email ) && ! empty( $this->form_submit_data['submit_data'] ) ) {
				foreach ( $this->form_submit_data['submit_data'] as $field_name => $field_value ) {
					if ( is_email( $field_value ) ) {
						$user_email = $field_value;
						break;
					}
				}
			}
			
			return $user_email;
		}
		
		/**
		 * Get email subject
		 * 
		 * @since 1.6.0
		 * @return string Email subject
		 */
		public function get_subject() {
			if ( empty( $this->form_schema['form_attrs']['userEmailSubject'] ) ) {
				return '';
			}
			
			$subject = $this->form_schema['form_attrs']['userEmailSubject'];
			$placeholders = $this->get_placeholders();
			
			return $this->replace_placeholders( $subject, $placeholders );
		}
		
		/**
		 * Get email body content
		 * 
		 * @since 1.6.0
		 * @return string Email body
		 */
		public function get_body() {
			if ( empty( $this->form_schema['form_attrs']['userEmailTemplate'] ) ) {
				return '';
			}
			
			$body = $this->form_schema['form_attrs']['userEmailTemplate'];
			$placeholders = $this->get_placeholders();
			
			return $this->replace_placeholders( $body, $placeholders );
		}
		
		/**
		 * Get placeholder values for replacement
		 * 
		 * @since 1.6.0
		 * @return array Placeholder array (key => value)
		 */
		private function get_placeholders() {
			$placeholders = array();
			
			// Get user name
			$user_name = '';
			if ( ! empty( $this->form_submit_data['replyToFname'] ) ) {
				$user_name = $this->form_submit_data['replyToFname'];
				if ( ! empty( $this->form_submit_data['replyToLname'] ) ) {
					$user_name .= ' ' . $this->form_submit_data['replyToLname'];
				}
			}
			
			// Get user email
			$user_email = ! empty( $this->form_submit_data['replyToEmail'] ) 
				? $this->form_submit_data['replyToEmail'] 
				: '';
			
			// Get form name
			$form_name = ! empty( $this->form_submit_data['formName'] ) 
				? $this->form_submit_data['formName'] 
				: '';
			
			// Get site name
			$site_name = get_bloginfo( 'name' );
			
			// Get site URL
			$site_url = home_url();
			
			$placeholders['name']      = esc_html( $user_name );
			$placeholders['email']      = esc_html( $user_email );
			$placeholders['form_name']  = esc_html( $form_name );
			$placeholders['site_name']  = esc_html( $site_name );
			$placeholders['site_url']   = esc_url( $site_url );
			
			/**
			 * Filter user email placeholders
			 * 
			 * @since 1.6.0
			 * @param array $placeholders Placeholder array
			 * @param array $form_schema Form schema
			 * @param array $form_submit_data Form submit data
			 */
			return apply_filters( 'gutena_forms_user_email_placeholders', $placeholders, $this->form_schema, $this->form_submit_data );
		}
	}
endif;

