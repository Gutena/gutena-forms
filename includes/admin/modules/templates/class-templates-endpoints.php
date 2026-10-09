<?php
/**
 * REST API endpoints for form templates.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Templates_Endpoints' ) ) :
	/**
	 * Handles template library REST routes.
	 *
	 * @since 2.2.0
	 */
	class Gutena_Forms_Templates_Endpoints {

		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Template registry.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Registry
		 */
		private $registry;

		/**
		 * Templates model.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Templates_Model
		 */
		private $model;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Templates_Endpoints
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 2.2.0
		 */
		private function __construct() {
			$this->registry = Gutena_Forms_Templates_Registry::get_instance();
			$this->model    = Gutena_Forms_Templates_Model::get_instance();

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Register REST routes.
		 *
		 * @since 2.2.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'templates/list',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'list_templates' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'templates/get',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_template' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'forms/create-from-template',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'create_from_template' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'forms/create-blank',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'create_blank' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * List templates with optional category and search filters.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response
		 */
		public function list_templates( $request ) {
			$category = sanitize_text_field( (string) $request->get_param( 'category' ) );
			$search   = sanitize_text_field( (string) $request->get_param( 'search' ) );

			if ( empty( $category ) ) {
				$category = 'all';
			}

			$templates = $this->registry->filter( $category, $search );
			$public    = array_map(
				function ( $template ) {
					return $this->registry->to_public( $template, false );
				},
				$templates
			);

			return rest_ensure_response(
				array(
					'status'     => 'success',
					'categories' => $this->registry->get_sidebar_items(),
					'templates'  => $public,
					'total'      => count( $public ),
				)
			);
		}

		/**
		 * Get a single template for preview.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_template( $request ) {
			$template_id = sanitize_text_field( (string) $request->get_param( 'id' ) );
			$template    = $this->registry->get_by_id( $template_id );

			if ( empty( $template ) ) {
				return new WP_Error(
					'gutena_forms_template_not_found',
					__( 'Template not found.', 'gutena-forms' ),
					array( 'status' => 404 )
				);
			}

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'template' => $this->registry->to_public( $template, false ),
				)
			);
		}

		/**
		 * Create a form from a template.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function create_from_template( $request ) {
			$template_id = sanitize_text_field( (string) $request->get_param( 'template_id' ) );
			$result      = $this->model->create_from_template( $template_id );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Form created successfully.', 'gutena-forms' ),
					'data'    => $result,
				)
			);
		}

		/**
		 * Create a blank form.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function create_blank( $request ) {
			$title  = sanitize_text_field( (string) $request->get_param( 'title' ) );
			$result = $this->model->create_blank( $title );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Form created successfully.', 'gutena-forms' ),
					'data'    => $result,
				)
			);
		}
	}

	Gutena_Forms_Templates_Endpoints::get_instance();
endif;
