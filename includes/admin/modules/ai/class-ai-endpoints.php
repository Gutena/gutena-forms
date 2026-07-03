<?php
/**
 * REST API endpoints for AI form generation.
 *
 * @since 1.9.2
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Ai_Endpoints' ) ) :
	/**
	 * Handles REST API endpoints for AI form generation.
	 */
	class Gutena_Forms_Ai_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Ai_Endpoints|null
		 */
		private static $instance;

		/**
		 * Get singleton instance.
		 *
		 * @return Gutena_Forms_Ai_Endpoints
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Register REST routes.
		 */
		private function __construct() {
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Add AI REST routes.
		 *
		 * @param array          $routes Existing REST routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'ai/generate-form',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'generate_form' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Generate a form via AI and create a gutena_forms post.
		 *
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response
		 */
		public function generate_form( $request ) {
			if ( ! Gutena_Forms_Ai_Middleware_Client::mw_base_ok() ) {
				return $this->error_response(
					__( 'AI middleware base URL is not set.', 'gutena-forms' ),
					'gutena_forms_ai_no_base',
					400
				);
			}

			$prompt   = $request->get_param( 'prompt' );
			$category = $request->get_param( 'category' );

			$result = Gutena_Forms_Ai_Form_Service::generate_and_create( $prompt, $category );

			if ( is_wp_error( $result ) ) {
				$code    = $result->get_error_code();
				$message = $result->get_error_message();
				$data    = $result->get_error_data();
				$status  = 500;

				if ( is_array( $data ) && ! empty( $data['status'] ) ) {
					$status = (int) $data['status'];
				} elseif ( 'no_middleware_credits' === $code ) {
					$status = 402;
				} elseif ( in_array( $code, array( 'gutena_forms_ai_empty_prompt', 'gutena_forms_ai_invalid_markup' ), true ) ) {
					$status = 400;
				} elseif ( 'gutena_forms_ai_forbidden' === $code ) {
					$status = 403;
				}

				$payload = array(
					'status'  => 'error',
					'code'    => $code,
					'message' => $message,
				);

				if ( 'no_middleware_credits' === $code ) {
					$payload['upgrade_url'] = apply_filters(
						'gutena_forms_ai_upgrade_url',
						'https://gutenaforms.com/pricing/'
					);
				}

				$response = rest_ensure_response( $payload );
				$response->set_status( $status );

				return $response;
			}

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'post_id'  => $result['post_id'],
					'form_id'  => $result['form_id'],
					'edit_url' => $result['edit_url'],
				)
			);
		}

		/**
		 * Build a standardized error REST response.
		 *
		 * @param string $message Error message.
		 * @param string $code    Error code.
		 * @param int    $status  HTTP status.
		 * @return WP_REST_Response
		 */
		private function error_response( $message, $code, $status ) {
			$response = rest_ensure_response(
				array(
					'status'  => 'error',
					'code'    => $code,
					'message' => $message,
				)
			);
			$response->set_status( $status );

			return $response;
		}
	}

	Gutena_Forms_Ai_Endpoints::get_instance();
endif;
