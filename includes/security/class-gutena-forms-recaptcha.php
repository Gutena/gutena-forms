<?php
/**
 * Class Gutena_Forms_Recaptcha
 *
 * Google reCAPTCHA security implementation
 * 
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Recaptcha' ) ) :
	class Gutena_Forms_Recaptcha extends Gutena_Forms_Security_Abstract {
		
		protected $security_id = 'recaptcha';
		protected $security_name = 'Google reCAPTCHA';
		
		/**
		 * Check if reCAPTCHA is enabled
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function is_enabled() {
			$config = $this->get_config();
			return ! empty( $config['enable'] ) && 
				   ! empty( $config['site_key'] ) && 
				   ! empty( $config['secret_key'] ) &&
				   ! empty( $config['type'] );
		}
		
		/**
		 * Render reCAPTCHA field
		 * 
		 * @since 1.6.0
		 * @param array $attributes Form attributes
		 * @return string
		 */
		public function render_field( $attributes = array() ) {
			if ( ! $this->is_enabled() ) {
				return '';
			}
			
			// Use attributes if provided, otherwise use form_attrs
			$config = ! empty( $attributes[ $this->security_id ] ) ? $attributes[ $this->security_id ] : $this->get_config();
			
			if ( empty( $config['site_key'] ) || empty( $config['type'] ) ) {
				return '';
			}
			
			$this->enqueue_assets();
			
			$html = '';
			
			// v2 shows checkbox, v3 is invisible
			if ( 'v2' === $config['type'] ) {
				$html = '<div class="g-recaptcha" data-sitekey="' . esc_attr( $config['site_key'] ) . '"></div><br>';
			}
			
			$html .= '<input type="hidden" name="recaptcha_enable" value="' . esc_attr( $config['enable'] ) . '" />';
			
			return $html;
		}
		
		/**
		 * Enqueue reCAPTCHA scripts
		 * 
		 * @since 1.6.0
		 * @return void
		 */
		public function enqueue_assets() {
			static $recaptcha_enqueued = false;
			
			if ( ! $recaptcha_enqueued ) {
				$config = $this->get_config();
				$grecaptcha = get_option( 'gutena_forms_grecaptcha', false );
				
				if ( ! empty( $grecaptcha ) && ! empty( $grecaptcha['site_key'] ) && ! empty( $grecaptcha['type'] ) ) {
					add_action( 'wp_enqueue_scripts', function() use ( $grecaptcha ) {
						$url = 'https://www.google.com/recaptcha/api.js';
						
						if ( 'v3' === $grecaptcha['type'] ) {
							$url .= '?render=' . esc_attr( $grecaptcha['site_key'] );
						}
						
						wp_enqueue_script(
							'google-recaptcha',
							esc_url( $url ),
							array(),
							GUTENA_FORMS_VERSION,
							false
						);
					} );
					
					$recaptcha_enqueued = true;
				}
			}
		}
		
		/**
		 * Verify reCAPTCHA
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function verify() {
			if ( ! $this->is_enabled() ) {
				return true;
			}
			
			// Check if reCAPTCHA not embedded in the form
			if ( empty( $_POST['recaptcha_enable'] ) && empty( $_POST['g-recaptcha-response'] ) ) {
				return true;
			}
			
			// Default recaptcha failed is considered as spam
			$_POST['recaptcha_error'] = 'spam';
			
			if ( empty( $_POST['g-recaptcha-response'] ) ) {
				$_POST['recaptcha_error'] = 'Recaptcha input missing';
				return false;
			}
			
			$config = $this->get_config();
			$recaptcha_settings = get_option( 'gutena_forms_grecaptcha', false );
			
			if ( empty( $recaptcha_settings ) ) {
				return false;
			}
			
			// Verify with Google API
			$response = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'body' => array(
						'secret'   => $recaptcha_settings['secret_key'],
						'response' => sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) ),
					),
				)
			);
			
			if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
				$_POST['recaptcha_error'] = 'No response from api';
				return false;
			}
			
			$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
			
			if ( empty( $api_response ) || empty( $api_response['success'] ) ) {
				return false;
			}
			
			$threshold_score = apply_filters(
				'gutena_forms_recaptcha_threshold_score',
				( empty( $recaptcha_settings['thresholdScore'] ) || $recaptcha_settings['thresholdScore'] < 0.5 ) 
					? 0.5 
					: $recaptcha_settings['thresholdScore']
			);
			
			// Check the hostname of the site where the reCAPTCHA was solved
			if ( ! empty( $api_response['hostname'] ) && function_exists( 'get_site_url' ) ) {
				$site_url = explode( '?', get_site_url() );
				if ( 5 < strlen( $site_url[0] ) && false === stripos( $site_url[0], $api_response['hostname'] ) ) {
					$_POST['recaptcha_error'] = 'different hostname';
					return false;
				}
			}
			
			if ( 'v2' === $recaptcha_settings['type'] ) {
				return true; // For v2
			} else if ( isset( $api_response['score'] ) && $api_response['score'] > $threshold_score ) {
				return apply_filters( 'gutena_forms_recaptcha_verify', true, $response );
			} else {
				return false; // Spam
			}
		}
		
		/**
		 * Get error message
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_error_message() {
			return __( 'Invalid reCAPTCHA', 'gutena-forms' );
		}
		
		/**
		 * Get additional error data
		 * 
		 * @since 1.6.0
		 * @return array
		 */
		public function get_error_data() {
			return array(
				'recaptcha_error' => isset( $_POST['recaptcha_error'] ) 
					? sanitize_text_field( $_POST['recaptcha_error'] ) 
					: '',
			);
		}
	}
endif;

