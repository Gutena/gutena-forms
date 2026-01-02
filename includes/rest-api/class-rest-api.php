<?php
/**
 * Class Rest Api
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Rest_Api' ) ) :
	/**
	 * Gutena Forms Rest Api Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Rest_Api {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Rest_Api $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Constructor.
		 *
		 * @since 1.6.0
		 */
		private function __construct() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		/**
		 * Register REST API routes.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Server $server The REST server.
		 */
		public function rest_api_init( $server ) {
			register_rest_route(
				'gutena-forms/v1',
				'/forms/get-ids',
				array(
					'permission_callback' => array( self::class, 'authenticate' ),
					'methods'             => $server::READABLE,
					'callback'            => array( $this, 'get_form_ids' ),
				)
			);

			register_rest_route(
				'gutena-forms/v1',
				'/forms/get',
				array(
					'permission_callback' => array( self::class, 'authenticate' ),
					'methods'             => $server::READABLE,
					'callback'            => array( $this, 'get_form' ),
				)
			);
		}

		/**
		 * Get form IDs.
		 *
		 * @since 1.6.0
		 * @return WP_REST_Response
		 */
		public function get_form_ids() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => 'publish',
					'posts_per_page' => -1,
				)
			);

			$forms = array_map(
				function ( $form ) {
					return array(
						'id'    => $form->ID,
						'title' => get_the_title( $form->ID ) . ' (ID: ' . $form->ID . ')',
					);
				},
				$forms
			);

			return rest_ensure_response(
				array(
					'forms' => $forms,
				)
			);
		}

		/**
		 * Get form data.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_form( $request ) {
			$id = $request->get_param( 'id' );
			if ( ! $id ) {
				return rest_ensure_response(
					array(
						'error' => 'Form ID is required.',
					)
				);
			}

			if ( 'false' === $id ) {
				return rest_ensure_response(
					array(
						'form' => null,
					)
				);
			}

			$post = get_post( $id );
			if ( ! $post || 'gutena_forms' !== $post->post_type ) {
				return rest_ensure_response(
					array(
						'error' => 'Form not found.',
					)
				);
			}

			return rest_ensure_response(
				array(
					'form' => array(
						'id'      => $post->ID,
						'title'   => get_the_title( $post->ID ),
						'content' => do_blocks( wp_unslash( $post->post_content ) ),
					),
				)
			);
		}

		public static function authenticate() {
			return current_user_can( 'manage_options' );
		}

		/**
		 * Get the single instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Rest_Api
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}
	}

	Gutena_Forms_Rest_Api::get_instance();
endif;
