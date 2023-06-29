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


		/**
		 * Admin Menu
		 */
		public function register_admin_menu() {
			//check for proper permission
			if ( ( ! has_filter( 'gutena_forms_check_user_access' ) && ! $this->is_gfadmin() )  || ! class_exists( 'Gutena_Forms_Store' ) || ! apply_filters( 'gutena_forms_check_user_access', true, 'view_entries' ) ) {
				return;
			}
			
			//register menu
			$page_hook_suffix = add_menu_page(
				__( 'Gutena Forms', 'gutena-forms' ),
				__( 'Gutena Forms', 'gutena-forms' ),
				'delete_posts',
				'gutena-forms',
				array( $this, 'forms_dashboard' ),
				'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuMzUyMTEgMTEuNDE1NUgwLjE1NzYyNUwxMS40MTcgMC4xNTU1NzJWNC4zMTc1TDQuMzUyMTEgMTEuNDE1NVoiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xOS44MDYxIDExLjQxNDFIMjQuMDAwNkwxMi43NDEyIDAuMTU0MTA3VjQuMzE2MDRMMTkuODA2MSAxMS40MTQxWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTQuMzQ5MTggMTIuNzM5M0gwLjE1NDY5NkwxMS40MTQxIDIzLjk5OTJWMTkuODM3M0w0LjM0OTE4IDEyLjczOTNaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTkuODAzMiAxMi43NDAySDIzLjk5NzZMMTIuNzM4MyAyNC4wMDAyVjE5LjgzODNMMTkuODAzMiAxMi43NDAyWiIgZmlsbD0id2hpdGUiLz4KPHJlY3Qgd2lkdGg9IjguNzU3MjkiIGhlaWdodD0iMi41ODY4NiIgdHJhbnNmb3JtPSJtYXRyaXgoMSAwIDAgLTEgMTIuMDQxIDE1LjMyNjIpIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K',
				6
			);
			if ( ! empty( $page_hook_suffix ) ) {
				add_action( 'admin_print_styles-' . $page_hook_suffix, array( $this, 'forms_listing_styles' ) );
				add_action( 'admin_print_scripts-' . $page_hook_suffix, array( $this, 'forms_listing_scripts' ) );
				//Action after register_admin_menu
				do_action( 'gutena_forms_register_admin_menu', $page_hook_suffix );
		   }
		}

		/**
		 * Form Dashboard pages: including form list, respective form entries etc
		 */
		public function forms_dashboard() {
			if ( ! function_exists('wp_localize_script') && ( ! has_filter( 'gutena_forms_check_user_access' ) && ! $this->is_gfadmin() )  || ! class_exists( 'Gutena_Forms_Store' ) || ! apply_filters( 'gutena_forms_check_user_access', true, 'view_entries' ) ) {
				esc_html_e( 'Access forbidden! Please contact admin to view this page.', 'gutena-forms' );
				return;
			}
			
			$form_store = new Gutena_Forms_Store();
			$form_table = '';
			$dashboard_html = '';
			$form_id = '';
			$dropdown = '';
			echo '<div class="gutena-forms-dashboard '.( is_gutena_forms_pro() ? '':'gf-basic' ).' ">'; 
			if ( ! empty( $_GET['formid'] ) && is_numeric( $_GET['formid'] ) ) {
				$form_id = absint( sanitize_key( $_GET['formid'] ) );
			} 
			if ( empty( $_GET['pagetype'] ) ) {
				if ( '' === $form_id ) {
					if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php' ) ) {
						require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-list-table.php';
						if ( class_exists( 'Gutena_Forms_List_Table' ) ) {
							$form_table = new Gutena_Forms_List_Table();
						}
					}
				} else if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php' ) ) {
					require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-forms-entries-table.php';
					if ( class_exists( 'Gutena_Forms_Entries_Table' ) ) {
						$form_list = $form_store->get_form_list();
						//form list
						if ( ! empty( $form_list ) ) {
							$dropdown .= '<select  class="gf-heading select-change-url" url="' . esc_url( admin_url( 'admin.php?page=gutena-forms&formid=' ) ) . '"  >';
							foreach ($form_list as $form) {
								$dropdown .='<option value="' . esc_attr( $form->form_id ) . '" '.( ( ! empty( $_GET['formid'] ) && $form->form_id === $_GET['formid'] ) ? 'selected':''  ).' >'.esc_attr( $form->form_name ).'</option>';
							}
							$dropdown .= '</select>';
						}
						$form_table = new Gutena_Forms_Entries_Table();
					}
				}
			} else if ( ! is_gutena_forms_pro() ) {
				$pagetype = sanitize_key( wp_unslash( $_GET['pagetype'] ) );
				//Entry View Page
				if ( ! empty( $_GET['form_entry_id'] ) && is_numeric( $_GET['form_entry_id'] ) && 'view' === $pagetype  ) {
					
					$dashboard_html .= '<div id="gfp-entry-view"></div>';
					//Provide data for form submission script
					wp_localize_script(
						'gutena-forms-dashboard',
						'gutenaFormsEntryDetails',
						array(
							'entry_data'       => $form_store->get_entry_details( absint( wp_unslash( $_GET['form_entry_id'] ) ) ),
							'entry_view_url' => esc_url( admin_url( 'admin.php?page=gutena-forms&pagetype=view&form_entry_id=' ) ),
							'entry_list_url' => esc_url( admin_url( 'admin.php?page=gutena-forms&formid=' ) ),
						)
					);
				} else if ( $this->is_gfadmin() ) {
					$dashboard_html .=  '<div id="gfp-page-' . esc_attr( $pagetype ) . '"></div>';
				}

				
			} 
			//Top header
			echo  $form_store->get_dashboard_header( $dropdown );

			//placeholder for react based dashboard
			if ( '' === $dashboard_html ) {
				do_action( 'gutena_forms_entries_load_custom_page' );
			} else {
				echo $dashboard_html;
			}
			
			//render list
            if ( ! empty( $form_table ) ) {
                $form_table->render_list_table( $form_store );
            }
			//pro modal
			$this->go_pro_modal();
			$this->entry_delete_modal();
			echo '</div>';
		}

		/**
		 * Go Pro Modal: 
		 * to open this modal use : <a modalid="gutena-forms-go-pro-modal" href="#" class="gutena-forms-modal-btn"  ></a> - anywhere in html document
		 */
		public function go_pro_modal() {
			//Action Html
			echo '
			<div id="gutena-forms-go-pro-modal"  class="gutena-forms-modal gf-small-modal" >
			<div class="gutena-forms-modal-content" >
			<span class="gf-close-btn gf-close-icon">&times;</span>
			<div class="gf-header" > 
				<div class="gf-title" >'.__( 'Upgrade to Complete the Experience!', 'gutena-forms' ).'</div> 
			</div>
			
			<div class="gf-body" >
			<p class="gf-description"> '.__( 'Get Gutena Forms Pro Today and Unlock all the Features', 'gutena-forms' ).'</p>
			<a href="https://gutena.io/pricing" class="gf-btn gf-pro-btn" target="_blank" > <span class="gf-btn-text">'.__( 'Go Premium - 14 Day Free Trial', 'gutena-forms' ).'</span> </a>
			</div></div></div>';
		}

		/**
		 * Delete a form entry modal: 
		 * to open this modal use : <a modalid="gutena-forms-entry-delete-modal" href="#" class="gutena-forms-modal-btn"  ></a> - anywhere in html document
		 */
		public function entry_delete_modal() {
			//Action Html
			echo '
			<div id="gutena-forms-entry-delete-modal"  class="gutena-forms-modal gf-small-modal" >
			<div class="gutena-forms-modal-content" >
			<span class="gf-close-btn gf-close-icon">&times;</span>
			<div class="gf-header" > 
				<div class="gf-title" >'.__( 'Trash Entry', 'gutena-forms' ).'</div> 
			</div>
			
			<div class="gf-body" >
			<p class="gf-description"> '.__( 'Do you really wanna trash this entry?', 'gutena-forms' ).'</p>
			<div class="gf-action-btns">
				<a href="#" class="gf-btn gf-close-btn" target="_blank" > <span class="gf-btn-text">'.__( 'Cancel', 'gutena-forms' ).'</span> </a>
				<a href="#" class="gf-btn gf-entry-delete-btn" target="_blank" > <span class="gf-btn-text">'.__( 'Trash', 'gutena-forms' ).'</span> </a>
			</div>
			
			</div></div></div>';
		}

		public function forms_listing_styles() {
			
		}

		public function forms_listing_scripts() {

			$asset_file = include_once( GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php' );

			if ( ! empty( $asset_file['dependencies'] ) ) {
				if ( ! is_gutena_forms_pro() ) {
					wp_enqueue_script( 'gutena-forms-dashboard', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/index.js', $asset_file['dependencies'], $asset_file['version'], true );
				}
				
				wp_enqueue_script( 'gutena-forms-dashboard-script', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/script.js', array(), $asset_file['version'], true );

				//Provide data for form submission script
				wp_localize_script(
					'gutena-forms-dashboard-script',
					'gutenaFormsDashboard',
					array(
						'read_status_action'       => 'gutena_forms_entries_read',
						'ajax_url'            => admin_url( 'admin-ajax.php' ),
						'nonce'               => wp_create_nonce( 'gutena_Forms' ),
						'entry_view_url' => esc_url( admin_url( 'admin.php?page=gutena-forms&pagetype=view&form_entry_id=' ) ),
					)
				);


				wp_enqueue_style( 'gutena-forms-dashboard-style', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/style-index.css', array( 'wp-components','wp-edit-blocks' ), $asset_file['version'], 'all' );
			}
			
			
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

		public function is_forms_store_exists() {
			return empty(  get_option( 'gutena_forms_store_version', false ) ) ? false : true;
		}
	}

	Gutena_Forms_Admin::get_instance();
}