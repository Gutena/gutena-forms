<?php
/**
 * Form data tables
 * common functions realted to datatables
 * https://developer.wordpress.org/reference/classes/wpdb/
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms_Store' ) && class_exists( 'Gutena_Forms_Admin' ) ) {

	class Gutena_Forms_Store extends Gutena_Forms_Admin {
		/**
		 * Current Form Id.
		 *
		 * @since 1.5.0
		 * @var string $form_id Current form id.
		 */
		public $form_id = '';

		// Table to store forms
		public $table_gutenaforms = 'gutenaforms';

		// Table users
		public $table_users = 'users';

		// Table to store form enteries
		public $table_gutenaforms_entries = 'gutenaforms_entries';

		// Table to store form field value
		public $table_gutenaforms_field_value = 'gutenaforms_field_value';

		// Table to store data related to forms and form enteries table
		public $table_gutenaforms_meta = 'gutenaforms_meta';

		// Table to store payment records linked to entries
		public $table_gutenaforms_payments = 'gutenaforms_payments';

		/**
		 * @var self|null
		 */
		private static $store_instance = null;

		public function __construct() {
			global $wpdb;
			$this->table_gutenaforms             = $wpdb->prefix . '' . $this->table_gutenaforms;
			$this->table_users                   = $wpdb->prefix . '' . $this->table_users;
			$this->table_gutenaforms_entries     = $wpdb->prefix . '' . $this->table_gutenaforms_entries;
			$this->table_gutenaforms_field_value = $wpdb->prefix . '' . $this->table_gutenaforms_field_value;
			$this->table_gutenaforms_meta        = $wpdb->prefix . '' . $this->table_gutenaforms_meta;
			$this->table_gutenaforms_payments   = $wpdb->prefix . '' . $this->table_gutenaforms_payments;
		}

		/**
		 * @return self
		 */
		public static function get_instance() {
			if ( null === self::$store_instance ) {
				self::$store_instance = new self();
			}

			return self::$store_instance;
		}

		public function include_db_upgrade_file() {
			if ( file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				return true;
			}
			return false;
		}

		public function get_form_name( $form_schema ) {
			$form_schema = $this->maybe_unserialize( $form_schema );
			return ( empty( $form_schema ) || empty( $form_schema['form_attrs'] ) || empty( $form_schema['form_attrs']['formName'] ) ) ? __( 'Contact Form', 'gutena-forms' ) : sanitize_text_field( $form_schema['form_attrs']['formName'] );
		}

		/**
		 * Update entry status
		 *
		 * @param string       $action read, unread, trash
		 *
		 * @param number|array $form_entry_ids entry id
		 */
		public function update_entries_status( $action, $form_entry_id ) {
			// check for valid action
			global $wpdb;
			if ( ! empty( $action ) && ! empty( $wpdb ) && ! empty( $form_entry_id ) ) {
				$form_entry_ids = array();
				if ( is_array( $form_entry_id ) ) {
					foreach ( $form_entry_id as $id ) {
						if ( ! empty( $id ) && is_numeric( $id ) ) {
							$form_entry_ids[] = absint( $id );
						}
					}
				} elseif ( ! empty( $form_entry_id ) && is_numeric( $form_entry_id ) ) {
					$form_entry_ids[] = absint( $form_entry_id );
				}
				// comma separated string id1,id2,...
				$form_entry_ids = implode( ',', $form_entry_ids );
				// Update status
				// Wpdb add single quotes for string
				$action_query = '';

				switch ( $action ) {
					case 'read':
						$action_query = "UPDATE {$this->table_gutenaforms_entries} SET entry_status = 'read' WHERE entry_id IN ({$form_entry_ids})";
						break;
					case 'unread':
						$action_query = "UPDATE {$this->table_gutenaforms_entries} SET entry_status = 'unread' WHERE entry_id IN ({$form_entry_ids})";
						break;
					case 'trash':
						$action_query = "UPDATE {$this->table_gutenaforms_entries} SET trash = 1 WHERE entry_id IN ({$form_entry_ids})";
						break;
					default:
						break;
				}

				if ( ! empty( $action_query ) && 25 < strlen( $action_query ) ) {
					$wpdb->query( $action_query );
					return true;
				}
			}

			return false;
		}

		/***
		 * Get form details
		 */
		protected function get_form_details( $block_form_id = '' ) {
			global $wpdb;
			if ( empty( $wpdb ) || empty( $block_form_id ) ) {
				return false;
			}

			// form table
			$table_forms = $this->table_gutenaforms;
			// get form details
			$fom_schema_row = $wpdb->get_results(
				$wpdb->prepare(
					"
					SELECT * FROM {$table_forms}
					WHERE block_form_id = %s
					AND published = %d
					",
					sanitize_key( $block_form_id ),
					1
				)
			);
			if ( $wpdb->last_error ) {
				return false;
			} elseif ( ! empty( $fom_schema_row ) && ! empty( $fom_schema_row[0]->form_id ) && ! empty( $fom_schema_row[0]->form_schema ) ) {
				return $fom_schema_row[0];
			}
		}

		// save new form
		protected function save_new_form( $form_id, $form_schema ) {
			// return if form id or data not available
			global $wpdb;
			if ( empty( $wpdb ) || empty( $form_id ) || empty( $form_schema ) || ! is_array( $form_schema ) || empty( $form_schema['form_attrs'] ) ) {
				return;
			}
			// $wpdb->insert( $table_name, $data, $data_format );
			// Insert query
			$wpdb->insert(
				$this->table_gutenaforms,
				array(
					'user_id'       => $this->current_user_id(),
					'block_form_id' => sanitize_key( $form_id ),
					'form_name'     => $this->get_form_name( $form_schema ),
					'form_schema'   => Gutena_Forms_Admin_Helper::sanitize_serialize_data( $form_schema ),
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
				)
			);

			return $wpdb->insert_id;
		}
	}
}
