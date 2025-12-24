<?php
/**
 * Class Rest API Controller
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Rest_API_Controller' ) ) :
	/**
	 * Gutena Forms Rest API Controller class.
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Rest_API_Controller {
		/**
		 * Singleton instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Rest_API_Controller $instance Singleton instance of the class.
		 */
		private static $instance;

		public static $namespace = 'gutena-forms/v1';

		/**
		 * Get singleton instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Rest_API_Controller
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Permission callback for REST API endpoints
		 *
		 * @since 1.6.0
		 * @return bool
		 */
		public static function permission_callback() {
			return is_user_logged_in() && current_user_can( 'manage_options' );
		}

		/**
		 * Constructor
		 *
		 * @since 1.6.0
		 */
		private function __construct() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		/**
		 * Register REST API routes
		 *
		 * @since 1.6.0
		 * @param WP_REST_Server $server The REST server.
		 */
		public function rest_api_init( $server ) {
			register_rest_route(
				self::$namespace,
				'/get-menus',
				array(
					'permission_callback' => array( self::class, 'permission_callback' ),
					'methods'             => 'GET',
					'callback'			  => array( $this, 'get_menus' ),
				)
			);

			register_rest_route(
				self::$namespace,
				'/forms-list',
				array(
					'permission_callback' => array( self::class, 'permission_callback' ),
					'methods'             => 'GET',
					'callback'			  => array( $this, 'get_forms_list' ),
				)
			);
		}

		/**
		 * Get menus callback
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_menus( $request ) {
			$menus = array(
				array(
					'title' => __( 'Dashboard', 'gutena-forms' ),
					'slug'  => '/dashboard',
				),
				array(
					'title' => __( 'Forms', 'gutena-forms' ),
					'slug'  => '/forms',
				),
				array(
					'title' => __( 'Entries', 'gutena-forms' ),
					'slug'  => '/entries',
				),
				array(
					'title' => __( 'Settings', 'gutena-forms' ),
					'slug'  => '/settings',
				),
				array(
					'title'    => __( 'Feature Request', 'gutena-forms' ),
					'slug'     => 'https://gutenaforms.com/roadmap/?utm_source=plugin&utm_medium=tab&utm_campaign=feature_requests',
					'external' => true,
				),
				array(
					'title' => __( 'Knowledge Base', 'gutena-forms' ),
					'slug'  => '/knowledge-base',
				),
			);

			if ( is_array( $menus ) && ! empty( $menus ) ) {
				return rest_ensure_response(
					array(
						'menus'   => $menus,
						'status'  => 200,
						'message' => __( 'Menus fetched successfully.', 'gutena-forms' ),
					)
				);
			}

			return rest_ensure_response(
				array(
					'menus'   => array(),
					'status'  => 404,
					'message' => __( 'No menus found.', 'gutena-forms' ),
				)
			);
		}
	}

	Gutena_Forms_Rest_API_Controller::get_instance();
endif;
