<?php
/**
 * Integrations settings endpoints
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Integrations_Endpoints' ) ) :
	class Gutena_Forms_Integrations_Endpoints {
		private static $instance;

		private function __construct() {
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}


		/**
		 * @param array          $routes
		 * @param WP_REST_Server $server
		 *
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			return $routes;
		}

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}

	Gutena_Forms_Integrations_Endpoints::get_instance();
endif;
