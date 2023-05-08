<?php
/**
 * List table : forms entries or submission
 * 
 *
 */

 defined( 'ABSPATH' ) || exit;

 /**
  * Abort if the class is already exists.
  */
 if ( ! class_exists( 'Gutena_Forms_Entries_Table' ) && class_exists( 'WP_List_Table' ) ) {
 
   
	class Gutena_Forms_Entries_Table extends WP_List_Table {

		protected $form_id = 0;

		protected $entry_columns = array();

		protected $store;

		public function __construct(  ) {
			parent::__construct(
				array(
					'plural'   => 'entry',
					'singular' => 'entries',
					'ajax'     => false
				)
			);
			
			if ( ! empty( $_GET['formid'] ) && is_numeric( $_GET['formid'] ) ) {
				$this->form_id = sanitize_key( $_GET['formid'] );
			}
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 */
		public function prepare_items() {
			if ( empty( $this->form_id ) ) {
				return;
			}
			
			$this->process_bulk_action();
			$table_data = apply_filters( 'gutena_forms_entries_table_list_prepare', array(
				'current_page' => $this->get_pagenum()
			) );
			if ( ! isset( $table_data['total_rows'] ) && ! isset( $table_data['form_rows'] ) ) {
				global $wpdb;
				$table_data['orderby'] = ( empty( $_GET['orderby'] ) || ! in_array( $_GET['orderby'], array( 'entry_id', 'modified_time' ) ) )? 'entry_id' : sanitize_text_field( $_GET['orderby'] )  ;
				$table_data['order'] = ( empty( $_GET['order'] ) || 'desc' === $_GET['order'] ) ? 'DESC' : 'ASC' ;
				//get total rows count
				$table_data['total_rows'] = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT( entry_id ) FROM {$this->store->table_gutenaforms_entries} WHERE  form_id = %d AND trash = 0",
						$this->form_id
					)
				);

				//per page 
				$table_data['per_page'] = absint( apply_filters( 'gutena_forms_tables_per_page', 20 ) ) ;
				//current page
				$table_data['current_page'] = $this->get_pagenum();
				$query_0 = "SELECT * FROM {$this->store->table_gutenaforms_entries} WHERE form_id = %d AND trash = 0 ";
				$query_1 = " ORDER BY ".$table_data['orderby']." {$table_data['order']} LIMIT %d OFFSET %d"; 
				//get form details
				$table_data['form_rows'] = $wpdb->get_results(
					$wpdb->prepare(
						$query_0." ".$query_1,
						$this->form_id,
						$table_data['per_page'],
						( $table_data['current_page'] - 1 ) * $table_data['per_page']
					) 
				);
				
				$table_data['table_columns'] = array( );
				$table_data['primary_column'] = 'cb';
				//Convert group concat string into array 
				foreach ( $table_data['form_rows'] as $key => $value ) {
					if ( ! empty( $table_data['form_rows'][ $key ]->entry_data ) ) {
						$table_data['form_rows'][ $key ]->entry_data = maybe_unserialize( $table_data['form_rows'][ $key ]->entry_data );
						if ( empty( $table_data['table_columns'] ) && is_array( $table_data['form_rows'][ $key ]->entry_data ) ) {
							//checkbox column
							$table_data['table_columns']['cb'] = '<input type="checkbox" />';
							$index = 0;
							//Dynamic column
							foreach ( $table_data['form_rows'][ $key ]->entry_data as $name_attr => $form_entry) {
								if ( $index < 3 ) {
									if ( 0 === $index) {
										$table_data['primary_column'] = $name_attr;
									}
									$table_data['table_columns'][$name_attr] = $form_entry['label'];
									$index++;
								}
							}
						}
					}
				}
			}
			
			if ( isset( $table_data['total_rows'] ) && isset( $table_data['form_rows'] ) && ! empty( $table_data['per_page'] ) && ! empty( $table_data['table_columns'] ) && ! empty( $table_data['primary_column'] ) ) {
				$this->set_pagination_args(
					array(
						'total_items' => $table_data['total_rows'],
						'per_page'    => $table_data['per_page'],
					)
				);
	
				$this->items = $table_data['form_rows'];
	
				//$this->_column_headers = array( $columns, $hidden, $sortable, $primary );
				//Add $primary column name to make table responsive
				$this->_column_headers = array( 
					array_merge( $table_data['table_columns'], $this->get_columns() ),
					$this->get_hidden_columns(),
					$this->get_sortable_columns(),
					$table_data['primary_column']
				);
			}
		}

		public function get_form_list() {
			global $wpdb;
			return  empty( $wpdb ) ? '': $wpdb->get_results(
				"SELECT form_id, form_name FROM {$this->store->table_gutenaforms} WHERE published = 1"
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
			return apply_filters( 'gutena_forms_entries_table_list_get_columns', array(
				"entry_id" => __( 'ID', 'gutena-forms' ),
				"modified_time" => __( 'Date', 'gutena-forms' ),
				"entry_action" => __( 'Action', 'gutena-forms' ),
			) );
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
				'modified_time' => array( 'date' )
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
			echo '<tr class="'.esc_attr( $form_entry->entry_status ).'" currentstatus="'.esc_attr( $form_entry->entry_status ).'" >';
			$this->single_row_columns( $form_entry );
			echo '</tr>';
		}

		public function column_cb( $form_entry ) {
			return apply_filters( 'gutena_forms_entries_table_list_column_cb', sprintf(
				'<input type="checkbox" name="form_entry_id[]" value="%d" /> ',
				$form_entry->entry_id
			), $form_entry );
		}

		public function column_default( $form_entry, $column_name ) {
			$column_value = '';
			switch ( $column_name ) {
				case 'entry_id':
					$column_value = $form_entry->entry_id;
				break;
				case 'modified_time':
					$column_value = date_format( date_create( $form_entry->modified_time ),"M d, Y");
					//.' '.__( 'at', 'gutena-forms' ).' '.date_format( date_create( $form_entry->modified_time ),"g:i a");
				break;
				case 'entry_action':
					$add_action_link = apply_filters( 'gutena_forms_entries_table_list_add_action_link', '', $form_entry );
					$column_value = $this->form_entry_view( $form_entry ).' | '. wp_kses_post( $add_action_link ) .' | <a href="'. esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.esc_attr( $form_entry->form_id ).'&action=trash&gfnonce='.wp_create_nonce( 'gutena_Forms' ).'&form_entry_id='.$form_entry->entry_id ) ) .'" class="gf-delete" >'.__( 'Trash', 'gutena-forms' ).'</a>';
				break;
				default:
					if ( ! empty( $form_entry->entry_data[$column_name] ) &&  ! empty( $form_entry->entry_data[$column_name]['value'] ) ) {
						$column_value = substr( $form_entry->entry_data[$column_name]['value'] , 0, 20 );
					}
				break;
			}

			return apply_filters( 'gutena_forms_entries_table_list_column_default', $column_value, $form_entry, $column_name );
		}

		public function form_entry_view( $form_entry ) {
			if ( empty( $form_entry->entry_data ) ) {
				return '';
			}
			//Action Html
			$actionHtml = '<img src="'.GUTENA_FORMS_PLUGIN_URL . 'assets/img/view-actions.png'.'" alt="'.__( 'Print, Export, Resend Notifications', 'gutena-forms' ).'" />';
			$actionHtml = apply_filters( 'gutena_forms_entries_quick_view_actions_html', $actionHtml, $form_entry );

			$class_id = 'gutena-form-entry-'. $form_entry->entry_id;
			$html = '
			<div id="' . esc_attr( $class_id ) . '"  class="gutena-forms-modal" >
			<div class="gutena-forms-modal-content" >
			<span class="gf-close-btn">&times;</span>
			<div class="gf-header" > 
				<div class="gf-title" >'.__( 'Form Entry', 'gutena-forms' ).'</div> 
				<div class="gf-action" > 
					<div class="gf-action-btn" >'.__( 'Actions', 'gutena-forms' ).' </div>
					<div class="gf-action-content" style="display:none" >
					'.$actionHtml.'
					</div>
				</div>
			</div>
			
			<div class="gf-body" >
			';
			foreach ($form_entry->entry_data as $name_attr => $name_value) {
				$html .='<div class="gf-row" >
				<div class="gf-field-label">'.esc_html( $name_value['label'] ).'</div>
				<div  >:</div>
				<div class="gf-field-value">'.esc_html( $name_value['value'] ).'</div>
				</div>';
			}
			$html .= '</div><div class="gf-footer" ></div></div></div><a modalid="' . esc_attr( $class_id ) . '" href="#" class="gutena-forms-modal-btn quick-view-form-entry-'.esc_attr( $form_entry->entry_status ).'" entryid="'.esc_attr( $form_entry->entry_id ).'" > '.__( 'Quick View', 'gutena-forms' ).'</a>';
			return $html;
		}

		public function get_bulk_actions() {
			return apply_filters( 'gutena_forms_entries_table_list_bulk_actions', array(
				"read" => __( 'Mark Read', 'gutena-forms' ),
				"unread" => __( 'Mark Unread', 'gutena-forms' ),
				"trash" => __( 'Delete', 'gutena-forms' ),
			) );
		}

		

		private function process_bulk_action() {
			//print_r($_POST);exit;
			if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['form_entry_id'] ) && function_exists( 'absint' ) ) {
				//check nonce
				check_ajax_referer( 'gutena_Forms', 'gfnonce' );
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
				
				global $wpdb;
				$action = sanitize_text_field( wp_unslash( $_REQUEST['action'] ) );
				
				//check for valid action
				if ( ! empty( $action ) && ! empty( $wpdb ) && ! empty( $form_entry_ids ) ) {
					//Admin Action 
					do_action( 'gutena_forms_entries_admin_action', $this->form_id, $action, $form_entry_ids );
					//comma separated string id1,id2,...
					$form_entry_ids = implode( ",", $form_entry_ids );
					//Update status
					//Wpdb add single quotes for string 
					$action_query = '';
					
					switch ( $action ) {
						case 'read':
							$action_query = "UPDATE {$this->store->table_gutenaforms_entries} SET entry_status = 'read' WHERE entry_id IN ({$form_entry_ids})";
						break;
						case 'unread':
							$action_query = "UPDATE {$this->store->table_gutenaforms_entries} SET entry_status = 'unread' WHERE entry_id IN ({$form_entry_ids})";
						break;
						case 'trash':
							$action_query = "UPDATE {$this->store->table_gutenaforms_entries} SET trash = 1 WHERE entry_id IN ({$form_entry_ids})";
						break;
						default:
						break;
					}

					if ( ! empty( $action_query ) && 25 < strlen( $action_query ) ) {
						$wpdb->query( $action_query );
					}
					
				}
				
				wp_redirect( esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.$this->form_id ) ) );
			}
		}

		public function render_list_table( $store = '' ) {
			if ( empty( $store ) && function_exists('wp_kses_post') ) {
				return;
			}
			$this->store = $store;
			//header
			echo '<div class="gutena-forms-dashboard entries">';
			echo wp_kses_post(  $this->store->get_dashboard_header() );
			//body
			
			$this->prepare_items();
			$form_list = $this->get_form_list();
			//form list
			$header = '';
			if ( ! empty( $form_list ) ) {
				$header .= '<div class="gf-sub-header"> 
				<div class="gf-select-form-wrapper"> 
				<label>'.__( 'Select Form', 'gutena-forms' ).'</label>
				<select  class="gf-heading select-change-url" url="' . esc_url( admin_url( 'admin.php?page=gutena-forms&formid=' ) ) . '"  >';
				foreach ($form_list as $form) {
					$header .='<option value="' . esc_attr( $form->form_id ) . '" '.( ( ! empty( $_GET['formid'] ) && $form->form_id === $_GET['formid'] ) ? 'selected':''  ).' >'.esc_attr( $form->form_name ).'</option>';
				}
				$header .= '</select></div></div>';
			}
			echo $header;
			echo '<div class="gf-body">';
			echo "<form method='post' name='search_form' action='" . esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.$this->form_id ) ) . "'>";

			echo apply_filters( 'gutena_forms_entries_table_list_render_filter', "<h3>Gutena Form Entries</h3>" );
			echo '<input type="hidden" name="gutena_forms_formid" value="'.esc_attr( $this->form_id ).'" />
			<input type="hidden" name="gfnonce" value="'.wp_create_nonce( 'gutena_Forms' ).'" />';
			$this->display();
			echo "</form>";
			echo '</div>';
			echo '</div>';
		}
		
	}
}