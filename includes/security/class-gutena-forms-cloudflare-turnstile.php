<?php
/**
 * Class Gutena_Forms_Cloudflare_Turnstile
 *
 * Cloudflare Turnstile security implementation
 * 
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Cloudflare_Turnstile' ) ) :
	class Gutena_Forms_Cloudflare_Turnstile extends Gutena_Forms_Security_Abstract {
		
		protected $security_id = 'cloudflareTurnstile';
		protected $security_name = 'Cloudflare Turnstile';
		
		/**
		 * Check if Cloudflare Turnstile is enabled
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function is_enabled() {
			$config = $this->get_config();
			return ! empty( $config['enable'] ) && 
				   ! empty( $config['site_key'] ) && 
				   ! empty( $config['secret_key'] );
		}
		
		/**
		 * Render Cloudflare Turnstile field
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
			
			if ( empty( $config['site_key'] ) ) {
				return '';
			}
			
			$this->enqueue_assets();
			
			$html = '<div
				class="cf-turnstile"
				data-sitekey="' . esc_attr( $config['site_key'] ) . '"
				data-theme="light"
				data-size="normal"
				data-callback="onSuccess"
			></div><br />';
			
			$html .= '<input type="hidden" name="turnstile_enable" value="' . esc_attr( $config['enable'] ) . '" />';
			
			return $html;
		}
		
		/**
		 * Enqueue Cloudflare Turnstile scripts
		 * 
		 * @since 1.6.0
		 * @return void
		 */
		public function enqueue_assets() {
			static $turnstile_enqueued = false;
			
			if ( ! $turnstile_enqueued ) {
				$config = $this->get_config();
				$cloudflare_turnstile = get_option( 'gutena_forms_cloudflare_turnstile', false );
				
				if ( ! empty( $cloudflare_turnstile ) && ! empty( $cloudflare_turnstile['site_key'] ) ) {
					add_action( 'wp_enqueue_scripts', function() {
						wp_enqueue_script(
							'cloudflare-turnstile',
							esc_url( 'https://challenges.cloudflare.com/turnstile/v0/api.js' ),
							array(),
							null,
							array(
								'defer' => true,
								'async' => true,
							)
						);
					} );
					
					$turnstile_enqueued = true;
				}
			}
		}
		
		/**
		 * Verify Cloudflare Turnstile response
		 * 
		 * @since 1.6.0
		 * @return bool
		 */
		public function verify() {
			if ( ! $this->is_enabled() ) {
				return true;
			}
			
			if ( isset( $_POST['cf-turnstile-response'] ) && ! empty( $_POST['cf-turnstile-response'] ) ) {
				$token = sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) );
				$cloudflare_turnstile = get_option( 'gutena_forms_cloudflare_turnstile', false );
				
				if ( empty( $cloudflare_turnstile ) ) {
					return false;
				}
				
				$response = wp_remote_post(
					'https://challenges.cloudflare.com/turnstile/v0/siteverify',
					array(
						'body' => array(
							'secret'   => $cloudflare_turnstile['secret_key'],
							'response' => $token,
						),
					)
				);
				
				if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
					return false;
				}
				
				$api_response = json_decode( wp_remote_retrieve_body( $response ), true );
				
				if ( ! empty( $api_response ) && $api_response['success'] ) {
					return true;
				}
			}
			
			return false;
		}
		
		/**
		 * Get error message
		 * 
		 * @since 1.6.0
		 * @return string
		 */
		public function get_error_message() {
			return __( 'Invalid Cloudflare Turnstile', 'gutena-forms' );
		}
	}
endif;

