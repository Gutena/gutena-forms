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

		/**
		 * REST API namespace
		 *
		 * @since 1.6.0
		 * @var string $namespace REST API namespace.
		 */
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
				'/left-navigation-menus',
				array(
					'permission_callback' => array( self::class, 'permission_callback' ),
					'methods'             => 'GET',
					'callback'			  => array( $this, 'get_left_navigation_menus' ),
				)
			);

			register_rest_route(
				self::$namespace,
				'/settings',
				array(
					'permission_callback' => array( self::class, 'permission_callback' ),
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
				),
			);

			register_rest_route(
				self::$namespace,
				'/save-settings',
				array(
					'permission_callback' => array( self::class, 'permission_callback' ),
					'methods'             => 'POST',
					'callback'            => array( $this, 'save_settings' ),
				),
			);

			/**
			 * Filter to add additional REST routes
			 *
			 * @since 1.6.0
			 * @param array $rest_routes Array of REST routes to register.
			 * @param WP_REST_Server $server The REST server.
			 *
			 * @return array Modified array of REST routes.
			 */
			$rest_routes = apply_filters( 'gutena_forms__rest_routs', array(), $server );

			foreach ( $rest_routes as $rest_route ) {

				if ( isset( $rest_route['auth'] ) && $rest_route['auth'] ) {
					if ( is_callable( $rest_route['auth'] ) ) {
						$permission = $rest_route['auth'];
					} else {
						$permission = array( self::class, 'permission_callback' );
					}
				} else {
					$permission = '__return_true';
				}

				$args = array(
					'permission_callback' => $permission,
					'methods'             => $rest_route['methods'],
					'callback'            => $rest_route['callback'],
				);
				register_rest_route(
					self::$namespace,
					$rest_route['route'],
					$args
				);
			}
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
					'slug'  => '/settings/manage-status',
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

		/**
		 * Get left navigation menus callback
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_left_navigation_menus( $request ) {
			$menus = array(
				array(
					'title' => __( 'General Settings', 'gutena-forms' ),
					'icon'  => 'Gear',
					'menus' => array(
						array(
							'title' => __( 'Manage Status', 'gutena-forms' ),
							'slug'  => 'manage-status',
						),
						array(
							'title' => __( 'Manage Tags', 'gutena-forms' ),
							'slug'  => 'manage-tags',
						),
						array(
							'title' => __( 'User Access', 'gutena-forms' ),
							'slug'  => 'user-access',
						),
						array(
								'title' => __( 'Weekly Summary', 'gutena-forms' ),
								'slug'  => 'weekly-summary',
							),
						)
				),
				array(
					'title' => __( 'Spam Protection', 'gutena-forms' ),
					'icon'  => 'Shield',
					'menus' => array(
						array(
							'title' => __( 'Honeypot', 'gutena-forms' ),
							'slug'  => 'honeypot',
						),
					),
				)
			);

			if ( is_array( $menus ) && ! empty( $menus ) ) {
				return rest_ensure_response(
					array(
						'menus'   => $menus,
						'status'  => 200,
						'message' => __( 'Left navigation menus fetched successfully.', 'gutena-forms' ),
					)
				);
			}

			return rest_ensure_response(
				array(
					'menus'   => array(),
					'status'  => 404,
					'message' => __( 'No left navigation menus found.', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Get settings callback
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_settings( $request ) {
			$settings_id = sanitize_text_field( wp_unslash( $request->get_param( 'settings_id' ) ) );

			$gutena_forms_settings = apply_filters( 'gutena_forms__settings', array() );

			if ( is_array( $gutena_forms_settings ) ) {
				if ( isset( $gutena_forms_settings[ $settings_id ] ) ) {
					if ( class_exists( $gutena_forms_settings[ $settings_id ] ) ) {
						$settings = new $gutena_forms_settings[ $settings_id ]();
						if ( $settings instanceof Gutena_Forms_Forms_Settings ) {
							return rest_ensure_response(
								array(
									'settings' => $settings->get_settings(),
									'status'   => 200,
									'message'  => __( 'Settings fetched successfully.', 'gutena-forms' ),
								)
							);
						}
					}
				}
			}

			return rest_ensure_response(
				array(
					'settings' => array(),
					'status'   => 404,
					'message'  => __( 'Settings not found.', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Save settings callback
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request The REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function save_settings( $request ) {
			$settings_id  = sanitize_text_field( wp_unslash( $request->get_param( 'settings_id' ) ) );
			$settings_data = $request->get_param( 'settings_data' );

			$gutena_forms_settings = apply_filters( 'gutena_forms__settings', array() );
			if ( is_array( $gutena_forms_settings ) && isset( $gutena_forms_settings[ $settings_id ] ) && class_exists( $gutena_forms_settings[ $settings_id ] ) ) {
				$settings = new $gutena_forms_settings[ $settings_id ]();
				if ( $settings instanceof Gutena_Forms_Forms_Settings ) {
					$save_result = $settings->save_settings( $settings_data );
					if ( $save_result ) {
						return rest_ensure_response(
							array(
								'status'  => 200,
								'message' => __( 'Settings saved successfully.', 'gutena-forms' ),
								'success' => true,
							)
						);
					}
				}
			}

			return rest_ensure_response(
				array(
					'status'  => 500,
					'message' => __( 'Failed to save settings.', 'gutena-forms' ),
				)
			);
		}
	}

	Gutena_Forms_Rest_API_Controller::get_instance();
endif;
