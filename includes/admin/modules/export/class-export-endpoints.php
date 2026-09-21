<?php
/**
 * Class export endpoints
 *
 * @since 2.1.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Export_Endpoints' ) ) :
	/**
	 * REST endpoints for export tools.
	 *
	 * @since 2.1.0
	 */
	class Gutena_Forms_Export_Endpoints {
		/**
		 * Singleton instance.
		 *
		 * @since 2.1.0
		 * @var Gutena_Forms_Export_Endpoints|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @since 2.1.0
		 * @return Gutena_Forms_Export_Endpoints
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
			include_once plugin_dir_path( __FILE__ ) . 'class-export-entries.php';
			include_once plugin_dir_path( __FILE__ ) . 'class-export-forms.php';
			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Register export REST routes.
		 *
		 * @since 2.1.0
		 * @param array          $routes Existing routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {
			$routes[] = array(
				'route'    => 'forms/fields',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_form_fields' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/export',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'export_entries' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'forms/export',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'export_forms' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Get form fields for the Export Entries UI.
		 *
		 * Expects CPT post ID in `form_id`.
		 *
		 * @since 2.1.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response
		 */
		public function get_form_fields( $request ) {
			$form_id = absint( $request->get_param( 'form_id' ) );

			if ( empty( $form_id ) ) {
				return rest_ensure_response(
					array(
						'fields'  => array(),
						'status'  => 'error',
						'message' => __( 'Missing form ID.', 'gutena-forms' ),
					)
				);
			}

			$fields = Gutena_Forms_Forms_Model::get_instance()->get_fields_by_post_id( $form_id );

			return rest_ensure_response(
				array(
					'fields'  => $fields,
					'status'  => 'success',
					'message' => __( 'Form fields fetched successfully.', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Export entries for selected form/fields/format.
		 *
		 * Body: { form_id, fields: string[], format: csv|xlsx|pdf }
		 *
		 * @since 2.1.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function export_entries( $request ) {
			$form_id = absint( $request->get_param( 'form_id' ) );
			$fields  = $request->get_param( 'fields' );
			$format  = sanitize_key( (string) $request->get_param( 'format' ) );

			if ( empty( $form_id ) ) {
				return new WP_Error(
					'missing_form',
					__( 'Missing form ID.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			if ( empty( $fields ) || ! is_array( $fields ) ) {
				return new WP_Error(
					'missing_fields',
					__( 'Please select at least one field.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			$exporter = new Gutena_Forms_Export_Entries();
			$result = $exporter->export( $form_id, $fields, $format );

			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'status' => 400 ) );
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'file'     => $result['file'],
					'filename' => $result['filename'],
					'mime'     => $result['mime'],
					'message'  => __( 'Entries exported successfully.', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Export selected forms (block trees) as JSON.
		 *
		 * Body: { form_ids: number[] }
		 *
		 * @since 2.1.0
		 * @param WP_REST_Request $request REST request.
		 * @return WP_REST_Response|WP_Error
		 */
		public function export_forms( $request ) {
			$form_ids = $request->get_param( 'form_ids' );

			if ( empty( $form_ids ) || ! is_array( $form_ids ) ) {
				return new WP_Error(
					'missing_forms',
					__( 'Please select at least one form to export.', 'gutena-forms' ),
					array( 'status' => 400 )
				);
			}

			$exporter = new Gutena_Forms_Export_Forms();
			$result   = $exporter->export( $form_ids );

			if ( is_wp_error( $result ) ) {
				$result->add_data( array( 'status' => 400 ) );
				return $result;
			}

			return rest_ensure_response(
				array(
					'status'   => 'success',
					'file'     => $result['file'],
					'filename' => $result['filename'],
					'mime'     => $result['mime'],
					'message'  => __( 'Forms exported successfully.', 'gutena-forms' ),
				)
			);
		}
	}

	Gutena_Forms_Export_Endpoints::get_instance();
endif;
