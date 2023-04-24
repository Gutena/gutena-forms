<?php
/**
 * List table : forms
 * 
 *
 */

 defined( 'ABSPATH' ) || exit;

 /**
  * Abort if the class is already exists.
  */
 if ( ! class_exists( 'Gutena_Forms_Entries_Table' ) && class_exists( 'WP_List_Table' ) ) {
 
   
	class Gutena_Forms_Entries_Table extends WP_List_Table {
		//Table to store forms
		protected $table_gutenaforms = 'gutenaforms';

		//Table to store form enteries
		protected $table_gutenaforms_entries = 'gutenaforms_entries';

		//Table to store form field value
		protected $table_gutenaforms_field_value = 'gutenaforms_field_value';

		//Table to store data related to forms and form enteries table
		protected $table_gutenaforms_meta = 'gutenaforms_meta';

		protected $form_id = 1;

		protected $entry_columns = array();

		protected $entries_status = array();

		public function __construct(  ) {
			parent::__construct(
				array(
					'plural'   => 'entry',
					'singular' => 'entries',
					'ajax'     => false
				)
			);
			global $wpdb; 
			if ( ! empty( $_GET['formid'] ) && is_numeric( $_GET['formid'] ) ) {
				$this->form_id = sanitize_key( $_GET['formid'] );
			}
			$this->table_gutenaforms = $wpdb->prefix .''. $this->table_gutenaforms;
			$this->table_gutenaforms_entries = $wpdb->prefix .''. $this->table_gutenaforms_entries;
			$this->table_gutenaforms_field_value = $wpdb->prefix .''. $this->table_gutenaforms_field_value;
			$this->table_gutenaforms_meta = $wpdb->prefix .''. $this->table_gutenaforms_meta;
			
		}

		/**
		 * Convert a formatted string to array 
		 * @param string $data concat data of name{=}value pairs separated by {|}
		 * e.g. name1{=}value1{|}name2{=}value2
		 */
		public function explode_group_concat( $data ) {
			if ( empty( $data ) ) {
				return $data;
			}

			//break string into array 
			$data = explode( "{|}", $data );
			$new_data = array();
			foreach ( $data as $name_value ) {
				$name_value = explode( "{=}", $name_value );
				if ( 2 === count( $name_value ) ) {
					$new_data[ $name_value[0] ] = $name_value[1];
				}
			}

			return $new_data;
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 */
		public function prepare_items() {
			$this->process_bulk_action();
			$orderby = ( empty( $_GET['orderby'] ) || ! in_array( $orderby, array( 'entry_id', 'entry_date' ) ) )? 'entry_id' : sanitize_text_field( $_GET['orderby'] )  ;
			$orderby =  'entry_date' === $orderby ? 'modified_time': $orderby;
			$order = ( empty( $_GET['order'] ) || 'desc' === $_GET['order'] ) ? 'DESC' : 'ASC' ;

			$search_term = empty( $_POST['s'] ) ? '' : sanitize_text_field( $_POST['s'] );
			
			global $wpdb; 
			//get total rows count
			$total_rows = $wpdb->get_var(
				empty( $search_term ) ? $wpdb->prepare(
					"SELECT COUNT( entry_id ) FROM {$this->table_gutenaforms_entries} WHERE  form_id = %d AND entry_status NOT IN (0)",
					$this->form_id
				) : $wpdb->prepare(
					"SELECT COUNT( entry_id ) FROM {$this->table_gutenaforms_entries} WHERE entry_data LIKE '%d%' AND form_id = %d AND entry_status NOT IN (0)",
					'%'.$wpdb->esc_like( $search_term ).'%',
					$this->form_id
				)
			);

			//SELECT COUNT( DISTINCT e.entry_id ) FROM {$this->table_gutenaforms_entries} e LEFT JOIN {$this->table_gutenaforms_field_value} fv ON e.entry_id = fv.entry_id WHERE e.form_id=8 AND field_value LIKE '%d%' GROUP BY e.form_id
			
			//SELECT e.entry_id, e.form_id, ( SELECT metadata FROM wp_gutenaforms_meta md WHERE md.data_type='admin_form_entry' AND md.entry_id = e.entry_id ) AS admin_form_entry, GROUP_CONCAT(fv.field_name, ':', fv.field_value SEPARATOR "{|}") AS field_value FROM `wp_gutenaforms_entries` e LEFT JOIN wp_gutenaforms_meta m ON e.entry_id = m.entry_id LEFT JOIN wp_gutenaforms_field_value fv ON e.entry_id = fv.entry_id WHERE e.form_id = 8 GROUP BY e.entry_id;

			//per page 
			$per_page = absint( apply_filters( 'gutena_forms_tables_per_page', 20 ) ) ;
			//current page
			$current_page = $this->get_pagenum();
			$query_0 = "SELECT * FROM {$this->table_gutenaforms_entries} WHERE form_id = %d AND entry_status NOT IN (0) ";
			$query_1 = " ORDER BY ".$orderby." {$order} LIMIT %d OFFSET %d"; 
			//get form details
			$form_rows = $wpdb->get_results(
				empty( $search_term ) ? $wpdb->prepare(
					$query_0." ".$query_1,
					$this->form_id,
					$per_page,
					( $current_page - 1 ) * $per_page
				) : $wpdb->prepare(
					$query_0." AND entry_data LIKE %s ".$query_1,
					$this->form_id,
					'%'.$wpdb->esc_like( $search_term ).'%',
					$per_page,
					( $current_page - 1 ) * $per_page
				)
			);
			 
			$table_columns = array( );
			$primary_column = 'cb';
			//Convert group concat string into array 
			foreach ( $form_rows as $key => $value ) {
				if ( ! empty( $form_rows[ $key ]->entry_data ) ) {
					$form_rows[ $key ]->entry_data = maybe_unserialize( $form_rows[ $key ]->entry_data );
					if ( empty( $table_columns ) && is_array( $form_rows[ $key ]->entry_data ) ) {
						//checkbox column
						$table_columns['cb'] = '<input type="checkbox" />';
						$index = 0;
						//Dynamic column
						foreach ( $form_rows[ $key ]->entry_data as $name_attr => $form_entry) {
							if ( $index < 3 ) {
								if ( 0 === $index) {
									$primary_column = $name_attr;
								}
								$table_columns[$name_attr] = $form_entry['label'];
								$index++;
							}
						}
					}
				}
			}

			$this->set_pagination_args(
				array(
					'total_items' => $total_rows,
					'per_page'    => $per_page,
				)
			);

			$this->items = $form_rows;

			//$this->_column_headers = array( $columns, $hidden, $sortable, $primary );
			//Add $primary column name to make table responsive
			$this->_column_headers = array( 
				array_merge( $table_columns, $this->get_columns() ),
				$this->get_hidden_columns(),
				$this->get_sortable_columns(),
				$primary_column
			);

			$this->entries_status = get_option( 'gutena_form_entries_status', array(
				__( 'Trash', 'gutena-forms' ),
				__( 'Not seen', 'gutena-forms' ),
				__( 'Seen', 'gutena-forms' ),
			) );
		}

		public function get_form_list() {
			global $wpdb;
			return  empty( $wpdb ) ? '': $wpdb->get_results(
				"SELECT form_id, form_name FROM {$this->table_gutenaforms} WHERE published = 1"
			);
		}

		/**
		 * Gets a list of columns.
		 *
		 * The format is:
		 * - `'internal-name' => 'Title'`
		 *
		 *
		 * @return array
		 */
		public function get_columns() {
			return array(
				"entry_id" => __( 'ID', 'gutena-forms' ),
				"entry_date" => __( 'Date', 'gutena-forms' ),
				"entry_action" => __( 'Action', 'gutena-forms' ),
			);
		}

		/**
		 * Gets a list of hidden columns.
		 * 
		 * @return array
		 */
		public function get_hidden_columns() {
			return array(
				'entry_id'
			);
		}


		/**
		 * Gets a list of sortable columns.
		 *
		 * The format is:
		 * - `'internal-name' => 'orderby'`
		 * - `'internal-name' => array( 'orderby', 'asc' )` - The second element sets the initial sorting order.
		 * - `'internal-name' => array( 'orderby', true )`  - The second element makes the initial order descending.
		 *
		 *
		 * @return array
		 */
		public function get_sortable_columns() {
			return array( 
				'entry_id' => array( 'entry_id' ),
				'entry_date' => array( 'date' )
			);
		}

		/**
		 * Generates content for a single row of the table.
		 *
		 * @since 3.1.0
		 *
		 * @param object|array $item The current item
		 */
		public function single_row( $form_entry ) {
			$status = array('deleted','unread','read');
			echo '<tr class="'.esc_attr( $status[$form_entry->entry_status] ).'">';
			$this->single_row_columns( $form_entry );
			echo '</tr>';
		}

		public function column_cb( $form_entry ) {
			
			return sprintf(
				'<input type="checkbox" name="form_entry_id[]" value="%d" /> ',
				$form_entry->entry_id
			);
		}

		public function column_default( $form_entry, $column_name ) {
			$column_value = '';
			switch ( $column_name ) {
				case 'entry_id':
					$column_value = $form_entry->entry_id;
				break;
				case 'entry_date':
					$column_value = date_format( date_create( $form_entry->modified_time ),"M d, Y").' '.__( 'at', 'gutena-forms' ).' '.date_format( date_create( $form_entry->modified_time ),"g:i a");
				break;
				case 'entry_action':
					$column_value = $this->form_entry_view( $form_entry ).' | <a href="'. esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.esc_attr( $form_entry->form_id ).'&action=delete&gfnonce='.wp_create_nonce( 'gutena_Forms' ).'&form_entry_id='.$form_entry->entry_id ) ) .'" class="gf-delete" >'.__( 'Trash', 'gutena-forms' ).'</a>';
				break;
				default:
					if ( ! empty( $form_entry->entry_data[$column_name] ) &&  ! empty( $form_entry->entry_data[$column_name]['value'] ) ) {
						$column_value = substr( $form_entry->entry_data[$column_name]['value'] , 0, 20 );
					}
				break;
			}
			return $column_value;
		}

		public function form_entry_view( $form_entry ) {
			if ( empty( $form_entry->entry_data ) ) {
				return '';
			}
			$class_id = 'gutena-form-entry-'. $form_entry->entry_id;
			$html = '
			<div id="' . esc_attr( $class_id ) . '"  class="gutena-forms-modal" >
			<div class="gutena-forms-modal-content" >
			<span class="gf-close-btn">&times;</span>
			<div class="gf-header" > '.__( 'Form Entry', 'gutena-forms' ).' </div>
			<div class="gf-body" >
			';
			foreach ($form_entry->entry_data as $name_attr => $name_value) {
				$html .='<div class="gf-row" >
				<div class="gf-field-label">'.esc_html( $name_value['label'] ).'</div>
				<div  >:</div>
				<div class="gf-field-value">'.esc_html( $name_value['value'] ).'</div>
				</div>';
			}
			$html .= '</div><div class="gf-footer" ></div></div></div><a modalid="' . esc_attr( $class_id ) . '" href="#" class="gutena-forms-modal-btn quick-view-form-entry-'.esc_attr( $form_entry->entry_status ).'" entryid="'.esc_attr( $form_entry->entry_id ).'" status="'.esc_attr( $form_entry->entry_status ).'" > '.__( 'View', 'gutena-forms' ).'</a>';
			return $html;
		}

		public function get_bulk_actions() {
			return array(
				"read" => __( 'Mark Read', 'gutena-forms' ),
				"unread" => __( 'Mark Unread', 'gutena-forms' ),
				"delete" => __( 'Delete', 'gutena-forms' ),
			);
		}

		

		private function process_bulk_action() {
			//print_r($_POST);exit;
			if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['form_entry_id'] ) && function_exists( 'absint' ) ) {
				//check nonce
				check_ajax_referer( 'gutena_Forms', 'gfnonce' );
				
				$entry_status = array_search( $_REQUEST['action'], array('delete','unread','read'),true );
				
				//check for valid action
				if ( ! is_numeric( $entry_status ) ) {
					return;
				}
				
				$form_entry_id = wp_unslash( $_REQUEST['form_entry_id'] );
				$form_entry_ids = array();
				if ( is_array( $form_entry_id ) ) {
					foreach ( $form_entry_id as $id) {
						if ( ! empty( $id ) && is_numeric( $id ) ) {
							$form_entry_ids[] = absint( $id );
						}
					}
				} else if ( ! empty( $form_entry_id ) && is_numeric( $form_entry_id ) ) {
					$form_entry_ids[] = absint( $form_entry_id );
				}
				//comma separated string id1,id2,...
				$form_entry_ids = implode( ",", $form_entry_ids );
				
				global $wpdb;
				
				//Update status
				if ( ! empty( $wpdb ) && ! empty( $form_entry_ids ) ) {
					
					//Wpdb add single quotes for string 
					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$this->table_gutenaforms_entries} SET entry_status = %d WHERE entry_id IN ({$form_entry_ids})",
							$entry_status
						)
					);

				} 
				
				wp_redirect( esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.$this->form_id ) ) );
			}
		}

		
		
	}
}