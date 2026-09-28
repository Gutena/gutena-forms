<?php
/**
 * REST API endpoints for product tour preferences.
 *
 * @since 1.9.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Tour_Endpoints' ) ) :
	/**
	 * Registers tour preference REST routes.
	 *
	 * @since 1.9.0
	 */
	class Gutena_Forms_Tour_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Tour_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 1.9.0
		 * @return Gutena_Forms_Tour_Endpoints
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Register REST routes.
		 *
		 * @since 1.9.0
		 */
		private function __construct() {
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Add tour REST routes.
		 *
		 * @since 1.9.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'tour/preferences',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_preferences' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'tour/preferences',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'save_preferences' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * GET tour preferences for the current user.
		 *
		 * @since 1.9.0
		 * @return WP_REST_Response
		 */
		public function get_preferences() {
			return rest_ensure_response(
				array(
					'preferences' => Gutena_Forms_Tour_Preferences::get(),
					'status'      => 200,
					'success'     => true,
				)
			);
		}

		/**
		 * POST (merge) tour preferences for the current user.
		 *
		 * @since 1.9.0
		 * @param WP_REST_Request $request Request object.
		 * @return WP_REST_Response
		 */
		public function save_preferences( $request ) {
			$patch = $request->get_param( 'preferences' );

			if ( ! is_array( $patch ) ) {
				$patch = array();
			}

			$saved = Gutena_Forms_Tour_Preferences::save( $patch );

			return rest_ensure_response(
				array(
					'preferences' => $saved,
					'status'      => 200,
					'success'     => true,
					'message'     => __( 'Tour preferences saved.', 'gutena-forms' ),
				)
			);
		}
	}
endif;
