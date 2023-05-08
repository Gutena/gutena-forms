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

			/**
			 * activate-deactivate : Action on activation 
			 * store : common db functions 
			 * create-store extends store : create tables for storing form submission
			 * manage-store extends store : manage store : Read, Update and delete
			 */
			foreach ( array( "activate-deactivate", "store", "create-store", "manage-store" ) as $key => $filename) {
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
				//Action after register_admin_menu
				do_action( 'gutena_forms_register_admin_menu', $page_hook_suffix );
		   }
		}

		

		public function forms_listing_page() {
			if ( ! $this->is_gfadmin() || ! class_exists( 'Gutena_Forms_Store' ) ) {
				return;
			}
			$form_table = '';
			if ( empty( $_GET['formid'] ) || ! is_numeric( $_GET['formid'] ) ) {
				if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php' ) ) {
					require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php';
					if ( class_exists( 'Gutena_Forms_List_Table' ) ) {
						$form_table = new Gutena_Forms_List_Table();
					}
				}
			} else if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php';
				if ( class_exists( 'Gutena_Forms_Entries_Table' ) ) {
					$form_id = sanitize_key( $_GET['formid'] );
					$form_table = new Gutena_Forms_Entries_Table( );
				}
			}

			//render list
            if ( ! empty( $form_table ) ) {
                $form_table->render_list_table( new Gutena_Forms_Store() );
            }
			
		}

		public function forms_listing_styles() {
			wp_enqueue_style( 'gutena-forms-dashboard-style', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/css/entries-list.min.css', array( ), GUTENA_FORMS_VERSION, 'all' );
		}

		public function forms_listing_scripts() {
			wp_enqueue_script( 'gutena-forms-dashboard', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/js/entries-list.min.js', array(), GUTENA_FORMS_VERSION, true );
			//Provide data for form submission script
			wp_localize_script(
				'gutena-forms-dashboard',
				'gutenaFormsDashboard',
				array(
					'read_status_action'       => 'gutena_forms_entries_read',
					'ajax_url'            => admin_url( 'admin-ajax.php' ),
					'nonce'               => wp_create_nonce( 'gutena_Forms' ),
				)
			);
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