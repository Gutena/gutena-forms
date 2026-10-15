<?php
/**
 * AI model: communicates with the Gutena Forms AI middle server.
 *
 * Sends the user prompt (plus context such as the site URL, the user
 * identifier and whether Pro is active) to the middle server and
 * returns the serialized block markup it produces. Credit consumption
 * happens on the middle server when it returns a 200 response.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_AI_Model' ) ) :
	/**
	 * Handles requests to the AI middle server.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_AI_Model {

		/**
		 * Request timeout (seconds) for the middle server call.
		 *
		 * @since 2.2.0
		 * @var int
		 */
		const REQUEST_TIMEOUT = 60;

		/**
		 * Generate form block markup from a user prompt.
		 *
		 * @since 2.2.0
		 * @param string $prompt  Sanitized user prompt (max 350 chars).
		 * @param int    $user_id Current WordPress user id.
		 * @return string|\WP_Error Serialized block markup on success, WP_Error on failure.
		 */
		public static function create_from_ai( $prompt, $user_id ) {
			if ( ! class_exists( 'Gutena_Forms_AI_Settings' ) || ! Gutena_Forms_AI_Settings::is_enabled() ) {
				return new \WP_Error(
					'gutena_forms_ai_disabled',
					__( 'AI form creation is not configured.', 'gutena-forms' )
				);
			}

			$server_url = Gutena_Forms_AI_Settings::get_server_url();
			$api_key    = Gutena_Forms_AI_Settings::get_api_key();

			if ( '' === $server_url || '' === $api_key ) {
				return new \WP_Error(
					'gutena_forms_ai_not_configured',
					__( 'AI form creation is not configured.', 'gutena-forms' )
				);
			}

			$body = array(
				'prompt'   => $prompt,
				'user_id'  => absint( $user_id ),
				'site_url' => home_url(),
				'is_pro'   => function_exists( 'is_gutena_forms_pro' ) && is_gutena_forms_pro(),
			);

			$args = array(
				'method'      => 'POST',
				'timeout'     => self::REQUEST_TIMEOUT,
				'headers'     => array(
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'Authorization' => 'Bearer ' . $api_key,
				),
				'body'        => wp_json_encode( $body ),
				'data_format' => 'body',
			);

			/**
			 * Filter the middle server request arguments.
			 *
			 * @since 2.2.0
			 * @param array  $args       wp_remote_post arguments.
			 * @param string $server_url Target server URL.
			 * @param array  $body       Request body.
			 */
			$args = apply_filters( 'gutena_forms_ai_request_args', $args, $server_url, $body );

			$response = wp_remote_post( $server_url . 'generate', $args );

			if ( is_wp_error( $response ) ) {
				return new \WP_Error(
					'gutena_forms_ai_network_error',
					__( 'Could not reach the AI server. Please try again later.', 'gutena-forms' ),
					array( 'status' => 502 )
				);
			}

			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( 200 !== (int) $code ) {
				return self::build_error_from_response( $code, $data );
			}

			$markup = '';
			if ( is_array( $data ) ) {
				$markup = isset( $data['block_markup'] ) ? $data['block_markup'] : '';
			}

			if ( ! is_string( $markup ) || '' === trim( $markup ) ) {
				return new \WP_Error(
					'gutena_forms_ai_empty_response',
					__( 'The AI server did not return a form. Please try a different prompt.', 'gutena-forms' )
				);
			}

			return $markup;
		}

		/**
		 * Build a WP_Error from a non-200 middle server response.
		 *
		 * @since 2.2.0
		 * @param int        $code HTTP response code.
		 * @param array|null $data Decoded response body.
		 * @return \WP_Error
		 */
		private static function build_error_from_response( $code, $data ) {
			$error_code    = 'gutena_forms_ai_server_error';
			$error_message = __( 'The AI server returned an error. Please try again later.', 'gutena-forms' );

			if ( is_array( $data ) && ! empty( $data['code'] ) ) {
				switch ( $data['code'] ) {
					case 'limit_reached':
						$error_code    = 'gutena_forms_ai_limit_reached';
						$error_message = __( 'You exceeded your credits. To get more, subscribe to our Pro plan.', 'gutena-forms' );
						break;
					case 'invalid_prompt':
						$error_code    = 'gutena_forms_ai_invalid_prompt';
						$error_message = __( 'Invalid prompt.', 'gutena-forms' );
						break;
					default:
						if ( ! empty( $data['message'] ) ) {
							$error_message = $data['message'];
						}
						break;
				}
			}

			return new \WP_Error(
				$error_code,
				$error_message,
				array( 'status' => $code )
			);
		}
	}
endif;
