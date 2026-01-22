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
		 * Forms Model Instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Forms_Model $forms_model Forms Model Instance.
		 */
		private $forms_model;

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
			$this->forms_model = Gutena_Forms_Forms_Model::get_instance();

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
		 * Get Forms
		 *
		 * @since 1.6.0
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
