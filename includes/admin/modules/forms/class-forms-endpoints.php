<?php
/**
 * REST API endpoints for forms (get all, delete).
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Forms_Endpoints' ) ) :
	/**
	 * Handles REST API endpoints for forms (get all, delete).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Forms_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Forms_Endpoints $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Forms model instance.
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Forms_Model $forms_model Forms model instance.
		 */
		private $forms_model;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.7.0
		 * @return Gutena_Forms_Forms_Endpoints
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Set forms model and register REST routes with gutena_forms__rest_routs filter.
		 *
		 * @since 1.7.0
		 */
		private function __construct() {
			$this->forms_model = Gutena_Forms_Forms_Model::get_instance();

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Add forms REST routes (get-all, delete).
		 *
		 * @since 1.7.0
		 * @param array          $routes Existing REST routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array Modified routes.
		 */
		public function rest_routes( $routes, $server ) {

			$routes[] = array(
				'route' => 'forms/get-all',
				'methods' => $server::READABLE,
				'callback' => array( $this, 'get_forms' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route' => 'forms/delete',
				'methods' => $server::READABLE,
				'callback' => array( $this, 'delete_form' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Get all forms (id, datetime, title, status, entries count, author, permalink).
		 *
		 * @since 1.7.0
		 * @return WP_REST_Response
		 */
		public function get_forms() {
			$forms = $this->forms_model->get_all();

			return rest_ensure_response(
				array(
					'forms'  => $forms,
					'status' => 'success',
				)
			);
		}

		/**
		 * Delete one or more forms by form_id (post ID).
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'form_id' (int or array of ints).
		 * @return WP_REST_Response
		 */
		public function delete_form( $request ) {
			$form_id = wp_unslash( $request->get_param( 'form_id' ) );
			if ( is_array( $form_id ) ) {
				foreach ( $form_id as $id ) {
					$id = intval( $id );
					$this->forms_model->delete( $id );
				}
			} else {
				$form_id = intval( $form_id );
				$this->forms_model->delete( $form_id );
			}

			return rest_ensure_response(
				array(
					'message' => __( 'Form deleted successfully.', 'gutena-forms' ),
					'status'  => 'success',
				)
			);
		}
	}

	Gutena_Forms_Forms_Endpoints::get_instance();
endif;
