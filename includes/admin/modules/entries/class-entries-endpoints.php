<?php
/**
 * Gutena Forms Entries REST API Module
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries_Endpoints' ) ) :
	/**
	 * Gutena Forms Entries REST API Module
	 */
	class Gutena_Forms_Entries_Endpoints {
		/**
		 * Singleton instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Entries_Endpoints $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Entries Model instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Entries_Model $entries_model Entries Model instance.
		 */
		private $entries_model;

		/**
		 * Get singleton instance
		 *
		 * @return Gutena_Forms_Entries_Endpoints
		 * @since 1.6.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
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
			$this->entries_model = Gutena_Forms_Entries_Model::get_instance();

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * REST Routes
		 *
		 * @since 1.6.0
		 * @param array          $routes REST routes.
		 * @param WP_REST_Server $server REST server.
		 *
		 * @return array
		 */
		public function rest_routes( $routes, $server ) {

			$routes[] = array(
				'route'    => 'entries/get-all',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/details',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries_details' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entry/data',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entry_data' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entry/details',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entry_details' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/related',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_related_entries' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'forms/entry/search-options',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'entry_search_options' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/get-by-form-id',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'fetch_entries_by_form_id' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/headers',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries_header_by_form_id' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/data',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries_by_form_id' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Get Entries
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_entries( $request ) {
			$form_id = $request->get_param( 'form_id' );
			$entries = $this->entries_model->get_all( $form_id );

			return rest_ensure_response(
				array(
					'entries' => $entries,
					'status'  => 'success',
				)
			);
		}

		/**
		 * Get Entries Details
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_entries_details( $request ) {
			global $wpdb;
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$store   = new Gutena_Forms_Store();

			$sql = "SELECT e.form_id,
				  ( SELECT COUNT(entry_id) FROM %i WHERE form_id = e.form_id AND trash = 0 ) AS total_count,
				  ( SELECT entry_id FROM %i WHERE form_id = e.form_id AND trash = 0 AND entry_id < e.entry_id ORDER BY entry_id DESC LIMIT 1 ) AS previous_entry,
				  ( SELECT entry_id FROM %i WHERE form_id = e.form_id AND trash = 0 AND entry_id > e.entry_id ORDER BY entry_id ASC LIMIT 1 ) AS next_entry
				FROM %i AS e
				WHERE e.entry_id = %d AND e.trash = 0";

			$prepared = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms_entries,
				$store->table_gutenaforms_entries,
				$store->table_gutenaforms_entries,
				$store->table_gutenaforms_entries,
				$entry_id
			);

			$result = $wpdb->get_row( $prepared, ARRAY_A );

			return rest_ensure_response(
				array(
					'details' => $result,
					'status'  => 'success',
				)
			);
		}

		/**
		 * Get entry data.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_entry_data( $request ) {
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$value    = $this->entries_model->get_data( $entry_id );
			$value    = maybe_unserialize( $value );

			return rest_ensure_response(
				array(
					'entry_data' => $value,
					'status'     => 'success',
				)
			);
		}

		/**
		 * Get entry data.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_entry_details( $request ) {
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$result   = $this->entries_model->get_details( $entry_id );

			if ( ! empty( $result['user_id'] ) ) {
				$user_info          = get_userdata( absint( $result['user_id'] ) );
				$result['user_name'] = $user_info ? $user_info->user_login : __( 'Unknown User', 'gutena-forms' );
				unset( $result['user_id'] );
			}

			if ( ! empty( $result['form_id'] ) ) {
				$form_name = Gutena_Forms_Forms_Model::get_instance()->get_name_by_id( $result['form_id'] );

				$result['form_name'] = $form_name ? $form_name : __( 'Unknown Form', 'gutena-forms' );
				unset( $result['form_id'] );
			}

			if ( ! empty( $result['added_time'] ) ) {
				$result['added_time'] = gmdate( 'F j, Y H:i A', strtotime( $result['added_time'] ) );
			}

			return rest_ensure_response(
				array(
					'entry_details' => $result,
					'status'        => 'success',
				)
			);
		}

		/**
		 * Get related entries.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function get_related_entries( $request ) {
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$results  = $this->entries_model->get_related( $entry_id );

			$results = array_map(
				function ( $entry ) {

					if ( ! empty( $entry['added_time'] ) ) {
						$entry['added_time'] = gmdate( 'F j, Y H:i A', strtotime( $entry['added_time'] ) );
					}

					return $entry;
				},
				$results
			);

			return rest_ensure_response(
				array(
					'related_entries' => $results,
					'status'          => 'success',
				)
			);
		}

		/**
		 * Get entry search options.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function entry_search_options( $request ) {
			$data = Gutena_Forms_Forms_Model::get_instance()->get_name_and_block_id();

			return rest_ensure_response(
				array(
					'search_options' => $data,
					'status'         => 'success',
				)
			);
		}

		/**
		 * Fetch entries by form ID.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
		 *
		 * @return WP_REST_Response
		 */
		public function fetch_entries_by_form_id( $request ) {
			$form_id = sanitize_text_field( wp_unslash( $request->get_param( 'formId' ) ) );
			$entries = $this->entries_model->get_all( $form_id );

			return rest_ensure_response(
				array(
					'entries' => $entries,
					'status'  => 'success',
				)
			);
		}

		/**
		 * Get entries by form ID.
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request
		 *
		 * @return WP_REST_Response
		 */
		public function get_entries_header_by_form_id( $request ) {
			$form_id        = sanitize_text_field( wp_unslash( $request->get_param( 'form_id' ) ) );
			$entries_header = $this->entries_model->get_entries_header( $form_id );

			return rest_ensure_response(
				array(
					'headers' => $entries_header,
					'status'  => 'success',
				)
			);
		}

		public function get_entries_by_form_id( $request ) {
			$form_id = sanitize_text_field( wp_unslash( $request->get_param( 'form_id' ) ) );
			$entries = $this->entries_model->get_entry_data( $form_id );

			return rest_ensure_response(
				array(
					'data' => $entries,
					'status'       => 'success',
				)
			);
		}
	}

	Gutena_Forms_Entries_Endpoints::get_instance();
endif;

