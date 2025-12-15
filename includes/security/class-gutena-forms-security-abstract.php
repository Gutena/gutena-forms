<?php
/**
 * Abstract Class Gutena_Forms_Security_Abstract
 *
 * Base class for all security/captcha implementations
 * 
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Security_Abstract' ) ) :
	/**
	 * Abstract Security Class
	 *
	 * @since 1.6.0
	 */
	abstract class Gutena_Forms_Security_Abstract {
		
		/**
		 * Security feature identifier (unique key)
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $security_id;
		
		/**
		 * Security feature name (human-readable)
		 * 
		 * @since 1.6.0
		 * @var string
		 */
		protected $security_name;
		
		/**
		 * Form schema
		 * 
		 * @since 1.6.0
		 * @var array
		 */
		protected $form_schema;
		
		/**
		 * Form attributes
		 * 
		 * @since 1.6.0
		 * @var array
		 */
		protected $form_attrs;
		
		/**
		 * Constructor
		 * 
		 * @since 1.6.0
		 * @param array $form_schema Form schema data
		 */
		public function __construct( $form_schema = array() ) {
			$this->form_schema = $form_schema;
			$this->form_attrs  = ! empty( $form_schema['form_attrs'] ) ? $form_schema['form_attrs'] : array();
		}
		
		/**
		 * Check if this security feature is enabled for the form
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		abstract public function is_enabled();
		
		/**
		 * Render the security field HTML (frontend)
		 * This is called when rendering the form
		 * 
		 * @since 1.6.0
		 * @param array $attributes Form block attributes
		 * @return string HTML output
		 */
		abstract public function render_field( $attributes = array() );
		
		/**
		 * Enqueue required scripts and styles
		 * This is called when the security feature is enabled
		 * 
		 * @since 1.6.0
		 * @return void
		 */
		abstract public function enqueue_assets();
		
		/**
		 * Verify the security challenge on form submission
		 * 
		 * @since 1.6.0
		 * @return bool True if valid, false otherwise
		 */
		abstract public function verify();
		
		/**
		 * Get error message if verification fails
		 * 
		 * @since 1.6.0
		 * @return string Error message
		 */
		abstract public function get_error_message();
		
		/**
		 * Get additional error data (optional)
		 * Can be used to pass extra information about the error
		 * 
		 * @since 1.6.0
		 * @return array Additional error data
		 */
		public function get_error_data() {
			return array();
		}
		
		/**
		 * Get security feature configuration from form attributes
		 * 
		 * @since 1.6.0
		 * @return array Configuration array
		 */
		protected function get_config() {
			$config_key = $this->get_config_key();
			return ! empty( $this->form_attrs[ $config_key ] ) ? $this->form_attrs[ $config_key ] : array();
		}
		
		/**
		 * Get the configuration key in form_attrs
		 * Override this if your config key differs from security_id
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		protected function get_config_key() {
			return $this->security_id;
		}
		
		/**
		 * Get security ID
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_security_id() {
			return $this->security_id;
		}
		
		/**
		 * Get security name
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_security_name() {
			return $this->security_name;
		}
	}
endif;

