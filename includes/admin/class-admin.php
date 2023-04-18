<?php
/**
 * Admin Area
 * like: file upload
 *
 */

 defined( 'ABSPATH' ) || exit;

 /**
  * Abort if the class is already exists.
  */
 if ( ! class_exists( 'Gutena_Forms_Admin' ) && class_exists( 'Gutena_Forms' ) ) {
 
   
	class Gutena_Forms_Admin extends Gutena_Forms {

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
			$this->load_classes();
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'load_admin_classes' ) );
		}

		/**
		 * Load classes
		 */
		private function load_classes() {

			//return if class already loaded
			if ( class_exists( 'Gutena_Forms_Activate_Deactivate' ) ) {
				return false;
			}

			foreach ( array( "activate-deactivate", "store", "create-store" ) as $key => $filename) {
				if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-' . $filename .'.php' ) ) {
					require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-' . $filename .'.php';
				}
			}
		}

		/**
		 * Load admin classes 
		 */
		public function load_admin_classes() {
			//load list table core wp class
			if ( ! class_exists( 'WP_List_Table' ) ) {
				if ( file_exists( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ) ) {
					require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
				}
			}
		}

		//Check admin capabilities
		public function is_gfadmin( $check_permission = 'manage_options' ) {
			if ( ! function_exists( 'wp_get_current_user' ) && file_exists( ABSPATH . "wp-includes/pluggable.php" ) ) { 
				require_once( ABSPATH . "wp-includes/pluggable.php" ); 
			}
			if ( ! function_exists( 'current_user_can' ) && file_exists( ABSPATH . "wp-includes/capabilities.php" ) ) { 
				require_once( ABSPATH . "wp-includes/capabilities.php" ); 
			}
			return ( function_exists( 'is_admin' ) && is_admin() && function_exists( 'current_user_can' ) && current_user_can( $check_permission ) );
		}


		public function register_admin_menu() {
			$page_hook_suffix = add_menu_page(
				__( 'Gutena Forms', 'gutena-forms' ),
				__( 'GutenaForms', 'gutena-forms' ),
				'manage_options',
				'gutena-forms',
				array( $this, 'forms_listing_page' ),
				'dashicons-tagcloud',
				6
			);
			if ( ! empty( $page_hook_suffix ) ) {
				add_action( 'admin_print_styles-' . $page_hook_suffix, array( $this, 'forms_listing_styles' ) );
				add_action( 'admin_print_scripts-' . $page_hook_suffix, array( $this, 'forms_listing_scripts' ) );
		   }
		}

		/**
		 * Admin dashboard header
		 * @param array $form_list list of form name with respective id
		 * 
		 * @return HTML 
		 */
		public function get_dashboard_header( $form_list = array() ) {
			//logo title
			$header = '<div class="gf-header">
			<div class="gf-logo-title">
			<img src="'.GUTENA_FORMS_PLUGIN_URL . 'assets/img/logo.png'.'" />
			<h2 class="gf-heading" >'.__( 'Gutena Forms', 'gutena-forms' ).' </h2>
			</div></div>';
		
			//form list
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
			return $header;
		}

		public function forms_listing_page() {
			if ( ! $this->is_gfadmin() ) {
				return;
			}
			if ( empty( $_GET['formid'] ) || ! is_numeric( $_GET['formid'] ) ) {
				if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php' ) ) {
					require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php';
					if ( class_exists( 'Gutena_Forms_List_Table' ) ) {
						add_thickbox();
						$form_table = new Gutena_Forms_List_Table();
						$form_table->prepare_items();
						echo '<div class="gutena-forms-dashboard">';
						echo $this->get_dashboard_header();
						echo '<div class="gf-body">';
						
						echo '<div class="gf-list-flex-space-bw">
						<div> <h3 class="gf-heading">'.__( 'Entries by Forms', 'gutena-forms' ).'</h3> </div>
						<div class="time-interval-dropdown">
						<select class="select-change-url" url="' . esc_url( admin_url( 'admin.php?page=gutena-forms&interval=' ) ) . '" >
							<option value="7"  >'. __( 'Last 7 Days', 'gutena-forms' ).'</option>
							<option value="30" '.( ( ! empty( $_GET['interval'] ) && '30' === $_GET['interval'] ) ? 'selected':'' ).' >'. __( 'Last 30 Days', 'gutena-forms' ).'</option>
						</select>
						</div>
						</div>';
						$form_table->display();
						echo '</div>';
						echo '</div>';
					}
				}
			} else if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php';
				if ( class_exists( 'Gutena_Forms_Entries_Table' ) ) {
					$form_id = sanitize_key( $_GET['formid'] );
					add_thickbox();
					$entries_table = new Gutena_Forms_Entries_Table();
					$entries_table->prepare_items();
					
					echo '<div class="gutena-forms-dashboard">';
					echo $this->get_dashboard_header( $entries_table->get_form_list() );
					echo '<div class="gf-body">';
						
					echo "<h3>Gutena Form Entries</h3>";
					echo "<form method='post' name='search_form' action='" . esc_url( admin_url( 'admin.php?page=gutena-forms&formid='.$form_id ) ) . "'>";
					echo '<input type="hidden" name="gfnonce" value="'.wp_create_nonce( 'gutena_Forms' ).'" />';
					//$entries_table->search_box("Search", "search_post_id");
					$entries_table->display();
					echo "</form>";
					echo '</div>';
					echo '</div>';
				}
			}
			
		}

		public function forms_listing_styles() {
			wp_enqueue_style( 'gutena-forms-dashboard-style', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/css/entries-list.min.css', array( ), GUTENA_FORMS_VERSION, 'all' );
		}

		public function forms_listing_scripts() {
			wp_enqueue_script( 'gutena-forms-dashboard', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/js/entries-list.min.js', array(), GUTENA_FORMS_VERSION, true );
		}

		/**
		 * Check if given keys in an array is empty or not 
		 * 
		 * @param array $check_array array to check
		 * @param array $keys keys to check
		 * @param boolean $logic_or apply conditional logic OR if true otherwise AND
		 * 
		 * @return boolean 
		 */
		public function is_empty( $check_array , $keys , $logic_or = true ) {
			//return if values not provided
			if ( empty( $check_array ) || empty( $keys ) ) {
				return true;
			}

			foreach ( $keys as $key ) {
				if ( $logic_or ) {
					if ( empty( $check_array[ $key ] ) ) {
						return true;
					}
				} else if ( ! empty( $check_array[ $key ] ) ) {
					return false;
				}
			}
			/** 
			 * At the end for 
			 * OR condition no one is empty  so return false 
			 * AND all are empty so return true
			 * */
			return $logic_or ? false : true;
		}

		// sanitize and seralize data
		public function sanitize_serialize_data( $data ) {
			
			if ( empty( $data ) ) {
				return $data;
			}

			if ( is_object( $data ) ) {
				$data = clone $data;
			} else if ( is_array( $data ) ) {
				$data = $this->sanitize_array( $data, true );
			} else {
				$data = sanitize_textarea_field( $data );
			}

			$data = sanitize_option( 'gutena_forms', $data );

			if( ! is_serialized( $data ) ) {
				$data = maybe_serialize($data);
			}

			return $data;
		}

		//unserialize data
		public function maybe_unserialize( $data ) {
			return ( function_exists( 'maybe_unserialize' ) && ! empty( $data )  ) ? maybe_unserialize( $data ) : $data;
		}

		//get current user id
		public function current_user_id() {
			//current user id
			return intval( function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0 );
		}
	}

	Gutena_Forms_Admin::get_instance();
}