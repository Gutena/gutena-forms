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

			$routes[] = array(
				'route'    => 'integrations/get-all',
				'methods'  => $server::READABLE,
				'auth'     => true,
				'callback' => array( $this, 'get_all_integrations' ),
			);

			return $routes;
		}

		public function get_all_integrations() {
			$integrations = apply_filters( 'gutena_forms__integrations', array() );

			foreach ( $integrations as $slug => $integration ) {
				$object = new $integration();
				if ( $object instanceof Gutena_Forms_Integration_Settings ) {
					$integrations[ $slug ] = array(
						'title'   => $object->title,
						'desc'    => $object->description,
						'name'    => $object->id,
						'enabled' => $object->is_enabled,
						'icon'    => $object->id,
					);
				} else {
					unset( $integrations[ $slug ] );
				}

			}

			return rest_ensure_response(
				array(
					'integrations' => $integrations,
				)
			);
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
