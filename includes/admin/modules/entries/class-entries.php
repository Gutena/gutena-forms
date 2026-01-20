<?php
/**
 * Gutena Forms Entries Admin Module
 *
 * @since 1.6.0
 * @package GutenaForms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Entries' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Gutena Forms Entries Admin Module
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Entries extends Gutena_Forms_Forms_Settings {
		/**
		 * Singleton instance
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Entries $instance Singleton instance of the class.
		 */
		private static $instance;

		/**
		 * Register Module
		 *
		 * @since 1.6.0
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['entries'] = __CLASS__;
					$settings['entry']   = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Entries
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
		public function __construct() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		/**
		 * REST API Init
		 *
		 * @since 1.6.0
		 * @param WP_REST_Server $server REST server.
		 */
		public function rest_api_init( $server ) {
			register_rest_route(
				Gutena_Forms_Rest_API_Controller::$namespace,
				'entries/get-all',
				array(
					'permission_callback' => array( 'Gutena_Forms_Rest_API_Controller', 'permission_callback' ),
					'methods'             => $server::READABLE,
					'callback'            => array( $this, 'get_entries' ),
				)
			);
		}

		/**
		 * Get Entries
		 *
		 * @since 1.6.0
		 * @param WP_REST_Request $request REST request.
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
		 * Get Settings
		 *
		 * @since 1.6.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'fields' => array(
					array(
						'type' => 'template',
						'name' => 'entries',
					)
				),
			);
		}

		/**
		 * Save Settings
		 *
		 * @since 1.6.0
		 * @param array $settings Settings to save.
		 */
		public function save_settings( $settings ) {
			// placeholder function.
		}

		public function get_entries_count_by_form_id( $form_id ) {
			global $wpdb;
			$store         = new Gutena_Forms_Store();
			$block_form_id = get_post_meta( $form_id, 'gutena_form_id', true );
			$sql           = 'SELECT COUNT( gutenaFormsEntries.entry_id ) FROM %i gutenaForms LEFT JOIN %i gutenaFormsEntries ON gutenaForms.form_id = gutenaFormsEntries.form_id WHERE gutenaForms.block_form_id = %s';
			$sql           = $wpdb->prepare(
				$sql,
				$store->table_gutenaforms,
				$store->table_gutenaforms_entries,
				$block_form_id
			);

			return intval( $wpdb->get_var( $sql ) );
		}
	}

	Gutena_Forms_Entries::register_module();
endif;
