<?php
/**
 * REST endpoints for importing forms.
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Import_Endpoints' ) ) :
	/**
	 * Import REST endpoints.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Import_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @since 2.1.0
		 * @var Gutena_Forms_Import_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.1.0
		 * @return Gutena_Forms_Import_Endpoints
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor.
		 *
		 * @since 2.1.0
		 */
		private function __construct() {
			include_once plugin_dir_path( __FILE__ ) . 'class-import-forms.php';
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Register import REST routes.
		 *
		 * @since 2.1.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'forms/import',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'import_forms' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Import forms from an export JSON payload.
		 *
		 * Body: { payload: object } OR { file: base64-json-string }
		 *
		 * @since 2.1.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function import_forms( $request ) {
			$payload = $request->get_param( 'payload' );

			if ( empty( $payload ) ) {
				$file = $request->get_param( 'file' );
				if ( ! empty( $file ) && is_string( $file ) ) {
					$decoded = base64_decode( $file, true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
					if ( false === $decoded ) {
						return new WP_Error(
							'invalid_file',
							__( 'Unable to import the file. Please upload a valid Gutena Forms export (.json) file.', 'gutena-forms' ),
							array( 'status' => 400 )
						);
					}
					$payload = json_decode( $decoded, true );
				}
			}

			if ( is_string( $payload ) ) {
				$payload = json_decode( $payload, true );
			}

			if ( empty( $payload ) || ! is_array( $payload ) ) {
				return new WP_Error(
					'invalid_payload',
					__( 'Unable to import the file. Please upload a valid Gutena Forms export (.json) file.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			$importer = new Gutena_Forms_Import_Forms();
			$result   = $importer->import( $payload );

			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'status' => 400 ) );
				return $result;
			}

			$message = sprintf(
				/* translators: %d: number of imported forms */
				_n(
					'%d form imported successfully.',
					'%d forms imported successfully.',
					$result['count'],
					'gutena-forms'
				),
				$result['count']
			);

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'imported' => $result['imported'],
					'count'    => $result['count'],
					'errors'   => $result['errors'],
					'message'  => $message,
				)
			);
		}
	}

	Gutena_Forms_Import_Endpoints::get_instance();
endif;
