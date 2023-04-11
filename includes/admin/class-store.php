<?php
/**
* Manage form data tables
* create, read, update and delete
* https://developer.wordpress.org/reference/classes/wpdb/
*/

 defined( 'ABSPATH' ) || exit;

 /**
 * Abort if the class is already exists.
 */
 if ( ! class_exists( 'Gutena_Forms_Store' ) && class_exists( 'Gutena_Forms_Admin' ) ) {

	class Gutena_Forms_Store extends Gutena_Forms_Admin {

		//Table to store forms
        protected $table_gutenaforms = 'gutenaforms';

        //Table to store form enteries
        protected $table_gutenaforms_entries = 'gutenaforms_entries';

        //Table to store form field value
        protected $table_gutenaforms_field_value = 'gutenaforms_field_value';

        //Table to store data related to forms and form enteries table
        protected $table_gutenaforms_meta = 'gutenaforms_meta';

		// The instance of this class
		private static $instance = null;

		// Returns the instance of this class.
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {	
			//Filter gutena forms render form
			add_filter('gutena_forms_save_form_schema', array( $this, 'save_form_schema' ), 10, 3 );
			//save submiited gutena forms
			add_action( 'gutena_forms_submitted_data', array( $this, 'save_form_entry' ), 10, 3 );
			
		}

		public function include_db_upgrade_file() {
			if ( file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				return true;
			}
			return false;
		}

		

		//save new form
		protected function save_new_form( $form_id, $form_schema ) {
			//return if form id or data not available
			if ( empty( $form_id ) || empty( $form_schema ) ) {
				return;
			}

			global $wpdb;
			//$wpdb->insert( $table_name, $data, $data_format );
			//Insert query
			$wpdb->insert(
				$wpdb->prefix .''. $this->table_gutenaforms,
				array(
					'user_id' => $this->current_user_id(),
					'block_form_id' => sanitize_key( $form_id ),
					'form_schema' => $this->sanitize_serialize_data( $form_schema ) ,
				),
				array(
					'%d',
					'%s',
					'%s',
				)
			);

			return $wpdb->insert_id;
		}

	
		/**
		 * add new meta for form or entries
		 * Required at least : 'form_id', 'data_type', 'metadata'
		 * 
		 */
		protected function add_form_or_enries_meta( $meta = array() ) {
			//return if form id or data not available
			if ( $this->is_empty( $meta, array( 'form_id', 'data_type', 'metadata'  ) ) ) {
				return;
			}
			global $wpdb;
			//$wpdb->insert( $table_name, $data, $data_format );
			//Insert query
			$wpdb->insert(
				$wpdb->prefix .''. $this->table_gutenaforms_meta,
				array(
					'form_id' => sanitize_key( $meta['form_id'] ),
					'entry_id' => empty( $meta['entry_id'] ) ? 0 : $meta['entry_id'],
					'user_id' => empty( $meta['user_id'] ) ? $this->current_user_id() : $meta['user_id'] ,
					'data_type' => sanitize_key( $meta['data_type'] ),
					'metadata' => $this->sanitize_serialize_data( $meta['metadata'] ),
				),
				array(
					'%d',
					'%d',
					'%d',
					'%s',
					'%s'
				)
			);
		}

		/***
		 * Get form details 
		 * 
		 */
		protected function get_form_details( $block_form_id = '' ) {
			if ( empty( $block_form_id ) ) {
				return false;
			}
			//form table 
			$table_forms = $wpdb->prefix .''. $this->table_gutenaforms;
			//get form details
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
			} else if ( ! empty( $fom_schema_row ) &&  ! empty( $fom_schema_row[0]->form_id ) &&  ! empty( $fom_schema_row[0]->form_schema ) ) {
				return $fom_schema_row[0];
			}
		}

		/**
		 * Add or Update form schema in gutena forms table
		 * Add or update row
		 * 
		 * @param array $form_schema form details in array format
		 * @param string $form_id
		 * @param array $gutena_form_ids array of existing forms in tables
		 * 
		 */
		public function save_form_schema( $form_schema, $block_form_id, $gutena_form_ids ) {
			global $wpdb; 
			if ( empty( $wpdb ) || empty( $block_form_id ) ) {
				return $form_schema;
			}
			$block_form_id = sanitize_key( $block_form_id );
			$error = '';
			//form table
			$table_forms = $wpdb->prefix .''. $this->table_gutenaforms;
			if ( empty( $gutena_form_ids ) || ! in_array( $block_form_id, $gutena_form_ids ) ) {
				$this->save_new_form( $block_form_id, $form_schema );
			} else {
				/**
				 * Update table
				 * step1: Get existing form schema from database
				 * step2: Backup of existing schema in gutenaforms_meta table
				 * step3: Update gutenaforms table row
				 */
				//step1:getting existing schema 
				$fom_schema_row = $this->get_form_details( $block_form_id );
				if ( ! empty( $fom_schema_row ) ) {
					//step2:Creating backup of existing schema 
					$form_schema_serialize = $this->sanitize_serialize_data( $form_schema );
					//take backup if form schema is different
					if ( $form_schema_serialize !==  $fom_schema_row->form_schema ) {
						$this->add_form_or_enries_meta( array(
							'form_id' => $fom_schema_row->form_id,
							'user_id' => $fom_schema_row->user_id,
							'data_type' => 'form_schema_backup',
							'metadata' => $fom_schema_row->form_schema,
						) );
					}
					
					//step3: Update gutenaforms table row
					//$wpdb->update( $table_name, $data, $where, $data_format, $where_format );
					$wpdb->update(
						$table_forms,
						array(
							'user_id' => $this->current_user_id(),
							'form_schema' => $form_schema_serialize
						),
						array(
							'form_id' => $form_schema['form_id']
						),
						array( '%d', '%s' ), 
						array( '%d' )
					);
				}
			}
			return $form_schema;
		}

		/**
		 * Save form submitted data
		 * 
		 * @param array $form_data submitted data
		 * @param array $fieldSchema form schema
		 * 
		 */
		public function save_form_entry( $form_data, $block_form_id, $fieldSchema ) {
			if ( empty( $form_data ) || ! is_array( $form_data ) || ! empty( $block_form_id ) || ! empty( $fieldSchema ) ) {
				return false;
			}
			/**
			 * Step1: check if schema existing
			 * Step2: Create form entry in table_gutenaforms_entries
			 * Step3: Create meta entry for admin editing in table_gutenaforms_meta
			 * Step4: Create field name values entries for each form fields
			 */
			//step1: check if schema existing 
			$fom_schema_row = $this->get_form_details( $block_form_id );
			if ( ! empty( $fom_schema_row ) ) {
				global $wpdb;
				$fieldSchema['form_id'] = sanitize_key( $fom_schema_row->form_id );
				$form_data = $this->sanitize_serialize_data( $form_data );
				$fieldSchema['user_id'] = $this->current_user_id();
				//Step2: Create form entry in table_gutenaforms_entries
				$wpdb->insert(
					$wpdb->prefix .''. $this->table_gutenaforms_entries,
					array(
						'form_id' => $fieldSchema['form_id'],
						'user_id' => $fieldSchema['user_id'],
						'entry_data' => $form_data,
					),
					array(
						'%d',
						'%d',
						'%s'
					)
				);
				//Get inserted id
				$fieldSchema['entry_id'] = $wpdb->insert_id;
				$fieldSchema['entry_id'] = sanitize_key( $fieldSchema['entry_id'] );
				//return if id not exist
				if ( empty( $fieldSchema['entry_id'] )  ) {
					return;
				}
				
				//Step3: Create meta entry for admin editing in table_gutenaforms_meta
				$this->add_form_or_enries_meta( array(
					'form_id' => $fieldSchema['form_id'],
					'entry_id' => $fieldSchema['entry_id'],
					'user_id' => $fieldSchema['user_id'],
					'data_type' => 'admin_form_entry',
					'metadata' => $form_data,
				) );

				//Step4: Create field name values entries for each form fields
				foreach ( $form_data as $name_attr => $data ) {
					$name_attr   = sanitize_key( wp_unslash( $name_attr ) );
					if ( empty( $fieldSchema[ $name_attr ] ) ) {
						continue;
					}
					$field_value = $data['raw_value'];
					if ( is_array( $field_value ) ) {
						$field_value =	$this->sanitize_array( wp_unslash( $field_value ), true );
						$field_value = implode(", ", $field_value );
					} else {
						$field_value = sanitize_textarea_field( wp_unslash( $field_value ) );
					}
					//Insert query
					$wpdb->insert(
						$wpdb->prefix .''. $this->table_gutenaforms_field_value,
						array(
							'entry_id' => $fieldSchema['entry_id'],
							'field_name' => $name_attr,
							'field_value' => $field_value,
						),
						array(
							'%d',
							'%s',
							'%s'
						)
					);
				}

				return true;
			} 

			return false;
		}
	}

	Gutena_Forms_Store::get_instance();
 }
