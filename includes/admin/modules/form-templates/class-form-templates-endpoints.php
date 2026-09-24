<?php
/**
 * REST API endpoints for the Gutena Forms template library.
 *
 * @since 2.2.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Templates_Endpoints' ) ) :
	/**
	 * Registers and handles template library REST routes.
	 *
	 * @since 2.2.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Form_Templates_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Form_Templates_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Template service instance.
		 *
		 * @since 2.2.0
		 * @var Gutena_Forms_Form_Template_Service
		 */
		private $service;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.2.0
		 * @return Gutena_Forms_Form_Templates_Endpoints
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
			$this->service = new Gutena_Forms_Form_Template_Service();

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Register template library REST routes.
		 *
		 * @since 2.2.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'templates/get-all',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_templates' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'templates/categories',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_categories' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'templates/create-from-template',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'create_from_template' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'templates/(?P<template_id>[a-z0-9-]+)',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_template' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * List templates with search, category filter, and pagination.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_templates( $request ) {
			$result = $this->service->get_templates(
				array(
					'category' => $request->get_param( 'category' ),
					'search'   => $request->get_param( 'search' ),
					'page'     => $request->get_param( 'page' ),
					'per_page' => $request->get_param( 'per_page' ),
				)
			);

			return rest_ensure_response(
				array(
					'status'          => 'success',
					'templates'       => $result['templates'],
					'category_counts' => $result['category_counts'],
					'pagination'      => $result['pagination'],
				)
			);
		}

		/**
		 * Get template categories with counts.
		 *
		 * @since 2.2.0
		 * @return WP_REST_Response
		 */
		public function get_categories() {
			return rest_ensure_response(
				array(
					'status'     => 'success',
					'categories' => $this->service->get_categories(),
				)
			);
		}

		/**
		 * Get a single template by ID.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function get_template( $request ) {
			$template_id = sanitize_key( $request->get_param( 'template_id' ) );
			$result      = $this->service->get_template_detail( $template_id );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'template' => $result,
				)
			);
		}

		/**
		 * Create a form from a registered template ID.
		 *
		 * @since 2.2.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function create_from_template( $request ) {
			$json_params = $request->get_json_params();
			$template_id = '';
			$form_name   = '';

			if ( is_array( $json_params ) ) {
				if ( isset( $json_params['template_id'] ) ) {
					$template_id = sanitize_key( wp_unslash( (string) $json_params['template_id'] ) );
				}

				if ( isset( $json_params['form_name'] ) && is_string( $json_params['form_name'] ) ) {
					$form_name = $json_params['form_name'];
				}
			}

			if ( '' === $template_id ) {
				$template_id = sanitize_key( wp_unslash( (string) $request->get_param( 'template_id' ) ) );
			}

			if ( '' === $form_name ) {
				$param_form_name = $request->get_param( 'form_name' );
				$form_name       = is_string( $param_form_name ) ? $param_form_name : '';
			}

			if ( '' === $template_id ) {
				return new WP_Error(
					'gutena_forms_template_invalid_id',
					__( 'A valid template ID is required.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			$result = $this->service->create_form_from_template( $template_id, $form_name );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Form created successfully.', 'gutena-forms' ),
					'form'    => $result['form'],
				)
			);
		}
	}

	Gutena_Forms_Form_Templates_Endpoints::get_instance();
endif;
