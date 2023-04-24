<?php
/**
* Form data tables
* common functions realted to datatables 
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

		public function __construct() {	
			global $wpdb; 
			$this->table_gutenaforms = $wpdb->prefix .''. $this->table_gutenaforms;
            $this->table_gutenaforms_entries = $wpdb->prefix .''. $this->table_gutenaforms_entries;
            $this->table_gutenaforms_field_value = $wpdb->prefix .''. $this->table_gutenaforms_field_value;
            $this->table_gutenaforms_meta = $wpdb->prefix .''. $this->table_gutenaforms_meta;
		}

		public function include_db_upgrade_file() {
			if ( file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				return true;
			}
			return false;
		}

		public function get_form_name( $form_schema ) {
			$form_schema = $this->maybe_unserialize( $form_schema );
			return ( empty( $form_schema ) || empty( $form_schema['form_attrs'] ) || empty( $form_schema['form_attrs']['formName'] ) ) ? __( 'Contact Form', 'gutena-forms' ) : sanitize_text_field( $form_schema['form_attrs']['formName'] );
		}

		/***
		 * Get form details 
		 * 
		 */
		protected function get_form_details( $block_form_id = '' ) {
			global $wpdb; 
			if ( empty( $wpdb ) || empty( $block_form_id ) ) {
				return false;
			}
			
			//form table 
			$table_forms = $this->table_gutenaforms;
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

		//save new form
		protected function save_new_form( $form_id, $form_schema ) {
			//return if form id or data not available
			global $wpdb; 
			if ( empty( $wpdb ) || empty( $form_id ) || empty( $form_schema ) || ! is_array( $form_schema ) || empty( $form_schema['form_attrs'] ) ) {
				return;
			}
			//$wpdb->insert( $table_name, $data, $data_format );
			//Insert query
			$wpdb->insert(
				$this->table_gutenaforms,
				array(
					'user_id' => $this->current_user_id(),
					'block_form_id' => sanitize_key( $form_id ),
					'form_name' => $this->get_form_name( $form_schema ),
					'form_schema' => $this->sanitize_serialize_data( $form_schema ) ,
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s'
				)
			);

			return $wpdb->insert_id;
		}
	}
 }
