<?php
/**
 * Gutena Forms Entries REST API Module
 *
 * @since 1.7.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries_Endpoints' ) ) :
	/**
	 * Handles REST API endpoints for form entries (list, get, delete, search, etc.).
	 *
	 * @since 1.7.0
	 * @package Gutena Forms
	 */
	class Gutena_Forms_Entries_Endpoints {
		/**
		 * Singleton instance
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Entries_Endpoints $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Entries Model instance
		 *
		 * @since 1.7.0
		 * @var Gutena_Forms_Entries_Model $entries_model Entries Model instance.
		 */
		private $entries_model;

		/**
		 * Get singleton instance
		 *
		 * @return Gutena_Forms_Entries_Endpoints
		 * @since 1.7.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Set entries model and register REST routes with gutena_forms__rest_routs filter.
		 *
		 * @since 1.7.0
		 */
		private function __construct() {
			$this->entries_model = Gutena_Forms_Entries_Model::get_instance();

			add_filter( 'gutena_forms__rest_routs', array( $this, 'rest_routes' ), 10, 2 );
		}

		/**
		 * Add entries REST routes (get-all, entry/data, entry/details, related, search, delete, etc.).
		 *
		 * @since 1.7.0
		 * @param array          $routes Existing REST routes.
		 * @param WP_REST_Server $server REST server.
		 * @return array Modified routes.
		 */
		public function rest_routes( $routes, $server ) {

			$routes[] = array(
				'route'    => 'entries/get-all',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entries' ),
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

			$routes[] = array(
				'route'    => 'entries/next-prev-current',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'fetch_current_prev_next_id' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entries/delete',
				'methods'  => $server::CREATABLE,
				'callback' => array( $this, 'delete_entries_callback' ),
				'auth'     => true,
			);

			$routes[] = array(
				'route'    => 'entry/status',
				'methods'  => $server::READABLE,
				'callback' => array( $this, 'get_entry_status' ),
				'auth'     => true,
			);

			return $routes;
		}

		/**
		 * Get all entries, optionally filtered by form_id.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Optional 'form_id' param.
		 * @return WP_REST_Response
		 */
		public function get_entries( $request ) {
			$form_id = $request->get_param( 'form_id' );
			$entries = $this->entries_model->get_all( $form_id );

			return rest_ensure_response(
				array(
					'entries'          => $entries,
					'status'           => 'success',
					'current_user_can_manage' => apply_filters(
						'gutena_forms__current_user_can',
						array( 'view', 'delete' ),
						'get-all'
					),
				)
			);
		}

		/**
		 * Get raw entry data (submitted field values) by entry ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'id' (entry ID).
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
		 * Get full entry details (metadata and formatted fields) by entry ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'id' (entry ID).
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
		 * Get entries from the same user as the given entry.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'id' (entry ID).
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
		 * Get form list (title and block id) for entry search dropdown.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request (unused).
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
		 * Fetch all entries for a form by form (block) ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'formId'.
		 * @return WP_REST_Response
		 */
		public function fetch_entries_by_form_id( $request ) {
			$form_id = sanitize_text_field( wp_unslash( $request->get_param( 'formId' ) ) );
			$entries = $this->entries_model->get_all( $form_id );

			return rest_ensure_response(
				array(
					'entries' => $entries,
					'status'  => 'success',
					'current_user_can_manage' => apply_filters(
						'gutena_forms__current_user_can',
						array( 'view', 'delete' ),
						'get-by-form-id'
					),
				)
			);
		}

		/**
		 * Get entries table headers for a form by form ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'form_id'.
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

		/**
		 * Get entries data (rows) for a form by form ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'form_id'.
		 * @return WP_REST_Response
		 */
		public function get_entries_by_form_id( $request ) {
			$form_id = sanitize_text_field( wp_unslash( $request->get_param( 'form_id' ) ) );
			$entries = $this->entries_model->get_entry_data( $form_id );

			return rest_ensure_response(
				array(
					'data'         => $entries,
					'status'       => 'success',
					'current_user_can_manage' => apply_filters(
						'gutena_forms__current_user_can',
						array( 'view', 'delete' ),
						'get-by-form-id'
					),
				)
			);
		}

		/**
		 * Fetch current entry context: total count, previous/next entry IDs, serial number.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'id' (entry ID).
		 * @return WP_REST_Response
		 */
		public function fetch_current_prev_next_id( $request ) {
			$entry_id = $request->get_param( 'id' );

			$entry_data = $this->entries_model->fetch_current_prev_details( $entry_id, 0 );

			return rest_ensure_response(
				array(
					'details' => $entry_data,
				),
				200
			);
		}

		/**
		 * Delete entries (move to trash) by entry ID(s).
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'entry_id' (int) or 'entry_ids' (array).
		 * @return WP_REST_Response Success or error response with appropriate status code.
		 */
		public function delete_entries_callback( $request ) {
			$entry_ids = $request->get_param( 'entry_ids' );
			if ( ! empty( $entry_ids ) && is_array( $entry_ids ) ) {
				$entry_ids = array_map( 'absint', array_filter( $entry_ids, 'is_numeric' ) );
			} else {
				$entry_id = $request->get_param( 'entry_id' );
				if ( ! empty( $entry_id ) && is_numeric( $entry_id ) ) {
					$entry_ids = array( absint( $entry_id ) );
				} else {
					$response = rest_ensure_response(
						array(
							'message' => __( 'Invalid entry ID.', 'gutena-forms' ),
							'status'  => 'error',
						)
					);
					$response->set_status( 400 );
					return $response;
				}
			}

			if ( empty( $entry_ids ) ) {
				$response = rest_ensure_response(
					array(
						'message' => __( 'No valid entry IDs provided.', 'gutena-forms' ),
						'status'  => 'error',
					)
				);
				$response->set_status( 400 );
				return $response;
			}

			$result = $this->entries_model->delete_entries( $entry_ids );

			if ( $result ) {
				$message = count( $entry_ids ) > 1
					? __( 'Entries deleted successfully.', 'gutena-forms' )
					: __( 'Entry deleted successfully.', 'gutena-forms' );
				return rest_ensure_response(
					array(
						'message' => $message,
						'status'  => 'success',
					)
				);
			}

			$response = rest_ensure_response(
				array(
					'message' => __( 'Failed to delete entry or entries.', 'gutena-forms' ),
					'status'  => 'error',
				)
			);
			$response->set_status( 500 );
			return $response;
		}

		/**
		 * Get entry status (e.g., read/unread) by entry ID.
		 *
		 * @since 1.7.0
		 * @param WP_REST_Request $request REST request. Expects 'entryId' (entry ID).
		 * @return WP_REST_Response REST response with entry status.
		 */
		public function get_entry_status( $request ) {
			$entry_id = sanitize_text_field( wp_unslash( $request->get_param( 'entryId' ) ) );

			$status = $this->entries_model->get_status_by_id( $entry_id );

			return rest_ensure_response(
				array(
					'status' => $status,
				)
			);
		}
	}

	Gutena_Forms_Entries_Endpoints::get_instance();
endif;

