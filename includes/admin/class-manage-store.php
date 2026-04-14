<?php
/**
 * Manage form data tables
 * create, read, update and delete rows
 * https://developer.wordpress.org/reference/classes/wpdb/
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms_Manage_Store' ) && class_exists( 'Gutena_Forms_Store' ) ) :
	/**
	 * Manage store class
	 */
	class Gutena_Forms_Manage_Store extends Gutena_Forms_Store {
		/**
		 * Gutena_Forms_Manage_Store instance
		 *
		 * @var Gutena_Forms_Manage_Store $instance Gutena forms store.
		 */
		private static $instance = null;

		/**
		 * Gettings the instance of this class
		 *
		 * @return Gutena_Forms_Manage_Store
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Plugin constructor
		 */
		public function __construct() {
			parent::__construct();

			add_filter( 'gutena_forms_save_form_schema', array( $this, 'save_form_schema' ), 10, 3 );
			add_action( 'gutena_forms_submitted_data', array( $this, 'save_form_entry' ), 10, 3 );
		}


		/**
		 * Add new meta for form or entries.
		 * Required at least : 'form_id', 'data_type', 'metadata'.
		 *
		 * @param array $meta Metadata for entries.
		 */
		protected function add_form_or_enries_meta( $meta = array() ) {
			// return if form id or data not available.
			global $wpdb;
			if ( empty( $wpdb ) || $this->is_empty( $meta, array( 'form_id', 'data_type', 'metadata' ) ) ) {
				return;
			}

			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$this->table_gutenaforms_meta,
				array(
					'form_id'   => sanitize_key( $meta['form_id'] ),
					'entry_id'  => empty( $meta['entry_id'] ) ? 0 : $meta['entry_id'],
					'user_id'   => empty( $meta['user_id'] ) ? $this->current_user_id() : $meta['user_id'],
					'data_type' => sanitize_key( $meta['data_type'] ),
					'metadata'  => $this->sanitize_serialize_data( $meta['metadata'] ),
				),
				array(
					'%d',
					'%d',
					'%d',
					'%s',
					'%s',
				)
			);
		}

		/**
		 * Add or Update form schema in gutena forms table
		 * Add or update row
		 *
		 * @param array  $form_schema form details in array format.
		 * @param string $block_form_id block form id.
		 * @param array  $gutena_form_ids array of existing forms in tables.
		 */
		public function save_form_schema( $form_schema, $block_form_id, $gutena_form_ids ) {
			global $wpdb;
			if ( empty( $wpdb ) || empty( $block_form_id ) || ! $this->is_forms_store_exists() ) {
				return $form_schema;
			}
			$block_form_id = sanitize_key( $block_form_id );
			$error         = '';
			// getting existing schema.
			$fom_schema_row = $this->get_form_details( $block_form_id );

			$table_forms = $this->table_gutenaforms;
			if ( empty( $fom_schema_row ) ) {
				$this->save_new_form( $block_form_id, $form_schema );
			} elseif ( ! empty( $fom_schema_row->form_id ) ) {
				/**
				 * Update table
				 * step1: Backup of existing schema in gutenaforms_meta table
				 * step2: Update gutenaforms table row
				 */
				// step1:Creating backup of existing schema.
				$form_schema_serialize = $this->sanitize_serialize_data( $form_schema );
				// take backup if form schema is different.
				if ( $form_schema_serialize !== $fom_schema_row->form_schema ) {
					$this->add_form_or_enries_meta(
						array(
							'form_id'   => $fom_schema_row->form_id,
							'user_id'   => $fom_schema_row->user_id,
							'data_type' => 'form_schema_backup',
							'metadata'  => $fom_schema_row->form_schema,
						)
					);
				}

				// step2: Update gutenaforms table row.
				$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
					$table_forms,
					array(
						'user_id'     => $this->current_user_id(),
						'form_name'   => $this->get_form_name( $form_schema ),
						'form_schema' => $form_schema_serialize,
					),
					array(
						'form_id' => $fom_schema_row->form_id,
					),
					array( '%d', '%s', '%s' ),
					array( '%d' )
				);
			}

			return $form_schema;
		}

		/**
		 * Save form submitted data
		 *
		 * @param array  $form_data submitted data.
		 * @param string $block_form_id block form id.
		 * @param array  $field_schema form schema.
		 *
		 * @return bool
		 */
		public function save_form_entry( $form_data, $block_form_id, $field_schema ) {
			global $wpdb;
			if ( empty( $wpdb ) || empty( $form_data ) || ! is_array( $form_data ) || empty( $block_form_id ) || empty( $field_schema ) || ! $this->is_forms_store_exists() ) {
				return false;
			}

			/**
			 * Step1: check if schema existing
			 * Step2: Create form entry in table_gutenaforms_entries
			 * Step3: Step3: Create meta entry for submiited data for record in table_gutenaforms_meta
			 * Step4: Create field name values entries for each form fields
			 */
			// step1: check if schema existing.
			$fom_schema_row = $this->get_form_details( $block_form_id );

			if ( ! empty( $fom_schema_row ) ) {

				$field_schema['form_id'] = sanitize_key( $fom_schema_row->form_id );
				$field_schema['user_id'] = $this->current_user_id();

				// Step2: Create form entry in table_gutenaforms_entries.
				$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$this->table_gutenaforms_entries,
					array(
						'form_id'     => $field_schema['form_id'],
						'user_id'     => $field_schema['user_id'],
						'modified_by' => $field_schema['user_id'],
						'entry_data'  => $this->sanitize_serialize_data( $form_data ),
					),
					array(
						'%d',
						'%d',
						'%d',
						'%s',
					)
				);
				// Get inserted id.
				$field_schema['entry_id'] = $wpdb->insert_id;
				$field_schema['entry_id'] = sanitize_key( $field_schema['entry_id'] );
				// return if id not exist.
				if ( empty( $field_schema['entry_id'] ) ) {
					return false;
				}

				// Step3: Create meta entry for submiited data for record in table_gutenaforms_meta.
				$this->add_form_or_enries_meta(
					array(
						'form_id'   => $field_schema['form_id'],
						'entry_id'  => $field_schema['entry_id'],
						'user_id'   => $field_schema['user_id'],
						'data_type' => 'submit_entry_data',
						'metadata'  => $this->sanitize_serialize_data( $form_data ),
					)
				);

				// Step4: Create field name values entries for each form fields.
				foreach ( $form_data as $name_attr => $data ) {
					$name_attr = sanitize_key( wp_unslash( $name_attr ) );
					if ( empty( $field_schema[ $name_attr ] ) ) {
						continue;
					}
					$field_value = $data['raw_value'];
					if ( is_array( $field_value ) ) {
						$field_value = $this->sanitize_array( wp_unslash( $field_value ), true );
						$field_value = implode( ', ', $field_value );
					} else {
						$field_value = sanitize_textarea_field( wp_unslash( $field_value ) );
					}

					// Insert query.
					$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
						$this->table_gutenaforms_field_value,
						array(
							'entry_id'    => $field_schema['entry_id'],
							'field_name'  => $name_attr,
							'field_value' => $field_value,
						),
						array(
							'%d',
							'%s',
							'%s',
						)
					);
				}

				return true;
			}

			return false;
		}
	}

	Gutena_Forms_Manage_Store::get_instance();
endif;
