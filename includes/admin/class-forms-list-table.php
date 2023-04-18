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
 if ( ! class_exists( 'Gutena_Forms_List_Table' ) && class_exists( 'WP_List_Table' ) ) {
 
   
	class Gutena_Forms_List_Table extends WP_List_Table {

		//Table to store forms
        protected $table_gutenaforms = 'gutenaforms';

		//Table to store form enteries
        protected $table_gutenaforms_entries = 'gutenaforms_entries';

		//Number of entries received in a time interval 
		protected $interval = 7;

		public function __construct() {
			parent::__construct(
				array(
					'plural'   => 'form',
					'singular' => 'forms',
					'ajax'     => false
				)
			);
			global $wpdb; 
			$this->table_gutenaforms = $wpdb->prefix .''. $this->table_gutenaforms;
			$this->table_gutenaforms_entries = $wpdb->prefix .''. $this->table_gutenaforms_entries;

			if ( ! empty( $_GET['interval'] ) && is_numeric( $_GET['interval'] ) ) {
				$this->interval = sanitize_text_field( wp_unslash( $_GET['interval'] ) ) ;
			}
			
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 */
		public function prepare_items() {

			$orderby = ( empty( $_GET['orderby'] ) || ! in_array( $orderby, array( 'form_name', 'date' ) ) )? 'form_id' : sanitize_text_field( $_GET['orderby'] )  ;
			$orderby =  'date' === $orderby ? 'modified_time': $orderby;
			$order = ( empty( $_GET['order'] ) || 'desc' === $_GET['order'] ) ? 'DESC' : 'ASC' ;
			
			global $wpdb; 
			
			//get total rows count
			$total_rows = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT( form_id ) FROM {$this->table_gutenaforms} WHERE published = 1"
				)
			);
			//per page 
			$per_page = absint( apply_filters( 'gutena_forms_tables_per_page', 20 ) ) ;
			//current page
			$current_page = $this->get_pagenum();
			 
			//get form details
			$form_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT f.*, COUNT(e.entry_id) AS total_entries, (SELECT COUNT(en.entry_id) FROM {$this->table_gutenaforms_entries} en WHERE en.form_id = f.form_id AND en.entry_status > 0 AND en.modified_time >= DATE(NOW() - INTERVAL %d DAY) ) AS entries_in_interval FROM {$this->table_gutenaforms} f LEFT JOIN {$this->table_gutenaforms_entries} e ON f.form_id = e.form_id AND f.published = 1 AND e.entry_status > 0 GROUP BY f.form_id ORDER BY f.".$orderby." {$order} LIMIT %d OFFSET %d",
					$this->interval,
					$per_page,
					( $current_page - 1 ) * $per_page
				)
			);

			$this->set_pagination_args(
				array(
					'total_items' => $total_rows,
					'per_page'    => $per_page,
				)
			);

			$this->items = $form_rows;

			$this->_column_headers = array( 
				$this->get_columns(),
				$this->get_hidden_columns(),
				$this->get_sortable_columns(),
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
				"form_id" => __( 'ID', 'gutena-forms' ),
				"form_name" => __( 'Form Name', 'gutena-forms' ),
				"total_entries" => __( 'All Time', 'gutena-forms' ),
				"entries_in_interval" => __( 'Last', 'gutena-forms' ) .' '. $this->interval .' '. __( 'Days', 'gutena-forms' ),
				"date" => __( 'Published', 'gutena-forms' ),
			);
		}

		/**
		 * Gets a list of hidden columns.
		 * 
		 * @return array
		 */
		public function get_hidden_columns() {
			return array(
				'form_id'
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
				'form_name' => array( 'form_name' ),
				'date' => array( 'date' )
			);
		}

		public function column_default( $form, $column_name ) {
			$column_value = $form->form_id;
			switch ( $column_name ) {
				case 'form_id':
					$column_value = $form->form_id;
				break;
				case 'form_name':
					$column_value = '<a href="'.esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.$form->form_id ) ).'">'.$form->form_name.'</a>';
				break;
				case 'total_entries':
					$column_value = $form->total_entries;
				break;
				case 'entries_in_interval':
					$column_value = $form->entries_in_interval;
				break;
				case 'date':
					$column_value = date_format( date_create( $form->modified_time ),"M d, Y").' '.__( 'at' ).' '.date_format( date_create( $form->modified_time ),"g:i a");
				break;
				default:
					$column_value = $form->form_id;
				break;
			}
			return $column_value;
		}

		
		
	}
}