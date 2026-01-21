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
			);

			$routes[] = array(
				'route'    => 'entries/details',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries_details' ),
			);

			$routes[] = array(
				'route'    => 'entry/data',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entry_data' ),
			);

			$routes[] = array(
				'route'    => 'entry/details',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entry_details' ),
			);

			$routes[] = array(
				'route'    => 'entries/related',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_related_entries' ),
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
			global $wpdb;

			// Get Store instance for table names
			$store = new Gutena_Forms_Store();

			// Get optional form_id parameter
			$form_id = $request->get_param( 'form_id' );

			// Build base query
			$query = "SELECT e.entry_id, e.form_id, f.form_name, e.added_time, e.entry_data
					  FROM {$store->table_gutenaforms_entries} e
					  LEFT JOIN {$store->table_gutenaforms} f ON e.form_id = f.form_id
					  WHERE e.trash = %d";

			$query_params = array( 0 );

			// Add form_id filter if provided
			if ( ! empty( $form_id ) && is_numeric( $form_id ) ) {
				$query .= " AND e.form_id = %d";
				$query_params[] = absint( $form_id );
			}

			$query .= " ORDER BY e.entry_id DESC";

			// Execute query
			$entries = $wpdb->get_results(
				$wpdb->prepare( $query, $query_params ),
				ARRAY_A
			);

			// Format entries to match required structure
			$formatted_entries = array_map(
				function ( $entry ) {
					$value = array();
					if ( is_serialized( $entry['entry_data'] ) ) {
						$value = maybe_unserialize( $entry['entry_data'] );
					}

					return array(
						'entry_id'  => absint( $entry['entry_id'] ),
						'form_id'   => absint( $entry['form_id'] ),
						'form_name' => ! empty( $entry['form_name'] ) ? $entry['form_name'] : __( 'Unknown Form', 'gutena-forms' ),
						'datetime'  => ! empty( $entry['added_time'] ) ? gmdate( 'Y-m-d h:i:s A', strtotime( $entry['added_time'] ) ) : '',
						'value' => $value,
					);
				},
				$entries
			);

			return rest_ensure_response(
				array(
					'entries' => $formatted_entries,
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
			global $wpdb;
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$store    = new Gutena_Forms_Store();
			$sql       = 'SELECT entry_data FROM %i WHERE entry_id = %d AND trash = 0';
			$sql       = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms_entries,
				$entry_id
			);
			$result    = $wpdb->get_var( $sql );
			$value     = array();

			if ( is_serialized( $result ) ) {
				$value = maybe_unserialize( $result );
			}

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
			global $wpdb;
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$store    = new Gutena_Forms_Store();
			$sql       = 'SELECT entry_id, form_id, user_id, added_time, entry_status FROM %i WHERE entry_id = %d AND trash = 0';
			$sql       = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms_entries,
				$entry_id
			);
			$result    = $wpdb->get_row( $sql, ARRAY_A );

			if ( ! empty( $result['user_id'] ) ) {
				$user_info          = get_userdata( absint( $result['user_id'] ) );
				$result['user_name'] = $user_info ? $user_info->user_login : __( 'Unknown User', 'gutena-forms' );
				unset( $result['user_id'] );
			}

			if ( ! empty( $result['form_id'] ) ) {
				$form_name = Gutena_Forms_Forms::get_form_name_by_id( $result['form_id'] );
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
			global $wpdb;
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'id' ) ) );
			$store    = new Gutena_Forms_Store();

			$sql     = 'SELECT related.entry_id, related.added_time FROM %i main LEFT JOIN %i related ON main.user_id = related.user_id AND main.entry_id != related.entry_id WHERE main.entry_id = %d';
			$sql     = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms_entries,
				$store->table_gutenaforms_entries,
				$entry_id
			);
			$results = $wpdb->get_results( $sql, ARRAY_A );

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
	}

	Gutena_Forms_Entries_Endpoints::get_instance();
endif;

