<?php
/**
 * Gutena Forms Forms REST API Class
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Endpoints' ) ) :
	/**
	 * Gutena Forms Forms REST API Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Forms_Endpoints {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Forms_Endpoints $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Get Instance
		 *
		 * @return Gutena_Forms_Forms_Endpoints
		 * @since 1.6.0
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		private function __construct() {
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * REST Routes
		 *
		 * @since 1.6.0
		 * @param array          $routes Rest routes.
		 * @param WP_REST_Server $server REST server.
		 *
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {

			$routes[] = array(
				'route' => 'forms/get-all',
				'methods' => $server::READABLE,
				'callback' => array( $this, 'get_forms' ),
			);

			$routes[] = array(
				'route' => 'forms/delete',
				'methods' => $server::READABLE,
				'callback' => array( $this, 'delete_form' ),
			);

			return $routes;
		}

		/**
		 * Get Forms
		 *
		 * @since 1.6.0
		 * @return WP_REST_Response
		 */
		public function get_forms() {
			$forms = get_posts(
				array(
					'post_type'      => 'gutena_forms',
					'post_status'    => array( 'publish', 'draft' ),
					'posts_per_page' => -1,
				)
			);

			$forms = array_map(
				function ( $form ) {
					return array(
						'id' => $form->ID,
						'datetime'  => gmdate( 'Y-m-d h:i A', strtotime( $form->post_date ) ),
						'title'     => $form->post_title,
						'status'    => $form->post_status,
						'entries'   => Gutena_Forms_Entries::get_instance()->get_entries_count_by_form_id( $form->ID ),
						'author'    => get_the_author_meta( 'display_name', $form->post_author ),
						'permalink' => get_permalink( $form->ID ),
					);
				},
				$forms
			);

			return rest_ensure_response(
				array(
					'forms'  => $forms,
					'status' => 'success',
				)
			);
		}

		/**
		 * Delete Form
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 */
		public function delete_form( $request ) {
			$form_id = wp_unslash( $request->get_param( 'form_id' ) );
			if ( is_array( $form_id ) ) {
				foreach ( $form_id as $id ) {
					$id = intval( $id );
					$this->delete_form_helper( $id );
				}
			} else {
				$form_id = intval( $form_id );
				$this->delete_form_helper( $form_id );
			}

			return rest_ensure_response(
				array(
					'message' => __( 'Form deleted successfully.', 'gutena-forms' ),
					'status'  => 'success',
				)
			);
		}

		/**
		 * Delete Form Helper
		 *
		 * @since 1.6.0
		 * @param int $form_id Form ID.
		 *
		 * @return array|false|WP_Post
		 */
		private function delete_form_helper( $form_id ) {
			if ( empty( $form_id ) ) {
				return false;
			}

			if ( 'gutena_forms' !== get_post_type( $form_id ) ) {
				return false;
			}

			return wp_delete_post( $form_id, true );
		}
	}

	Gutena_Forms_Forms_Endpoints::get_instance();
endif;
