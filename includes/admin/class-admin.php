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

		private $form_id = '';
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
			if ( ! empty( $_GET['formid'] ) && is_numeric( $_GET['formid'] ) ) {
				$this->form_id = absint( sanitize_key( $_GET['formid'] ) );
			}
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'load_admin_classes' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ) );

		if ( ! is_gutena_forms_pro( false ) ) {
			add_action( 'admin_notices', array( $this, 'view_dashboard_notice' ) );
			add_action( 'wp_ajax_gutena_forms_dismiss_notice', array( $this, 'dismiss_notice' ) );
		}
	}

		public function admin_head() {
			if ( ! is_gutena_forms_pro() ) {
				echo '<style type="text/css">
				#toplevel_page_gutena-forms ul li:last-child a {
					background: #27a68a !important;
					border: 1px solid #27a68a !important;
					color: #ffffff !important;
					font-weight: 600;
				}

				#toplevel_page_gutena-forms ul li:last-child a:hover,
				 #toplevel_page_gutena-forms ul li:last-child a:focus {
					color: #ffffff !important;
				}
			</style>';

				echo '<script type="text/javascript">
					( function() {
						function documentReadyState( callback ) {
							if ( typeof document === "undefined" ) { return; }

							if ( "complete" === document.readyState || "interactive" === document.readyState ) {
								return void callback();
							}

							document.addEventListener( "DOMContentLoaded", callback );
						}

						documentReadyState( () => {
							const el = document.querySelector( "#toplevel_page_gutena-forms ul li:last-child a" );
							if ( el && "Upgrade" === el.innerText ) { el.setAttribute( "target", "_blank" ); }
						} );
					} )();
				</script>';
			}
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

			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/abstract-forms-settings.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/honeypot/class-honeypot.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/recaptcha/class-recaptcha.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/cloudflare/class-cloudflare.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/weekly-report/class-weekly-report.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/manage-tags/class-manage-tags.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/manage-status/class-manage-status.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/user-access/class-user-access.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/forms/class-forms.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/entries/class-entries.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/rest-api/class-rest-api-controller.php';
		}

		/**
		 * Load admin classes
		 */
		public function load_admin_classes() {
			global $submenu;
			if ( isset( $submenu['gutena-forms'][0] ) ) {
				unset( $submenu['gutena-forms'][0] );
			}

			if ( isset( $submenu['gutena-forms'] ) && is_array( $submenu['gutena-forms'] ) ) {
				foreach ( $submenu['gutena-forms'] as $k => $gutena_form ) {
					if ( str_contains( $gutena_form[0], 'Add-Ons' ) ) {
						unset(  $submenu['gutena-forms'][ $k ] );
					}
				}
			}

			if ( isset( $_GET['page'] ) && 'gutena-forms-upgrade' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
				wp_redirect(
					add_query_arg(
						array(
							'utm_source' => 'wordpress_admin_menu',
							'utm_medium' => 'website',
							'utm_campaign' => 'free_plugin',
						),
						'https://gutenaforms.com/pricing/'
					)
				);
				exit;
			}

			if ( isset( $_GET['pagetype'] ) && 'feature-request' === sanitize_key( wp_unslash( $_GET['pagetype'] ) ) ) {
				echo '<script type="text/javascript">
					window.location.href = "https://gutenaforms.com/roadmap/?utm_source=plugin&utm_medium=tab&utm_campaign=feature_requests";
				</script>';
				exit;
			}

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
			if ( ( ! has_filter( 'gutena_forms_check_user_access' ) && ! $this->is_gfadmin() )  || ! class_exists( 'Gutena_Forms_Store' ) || ! apply_filters( 'gutena_forms_check_user_access', true, 'view_entries' ) ) {
				return;
			}

			$page_hook_suffix = add_menu_page(
				__( 'Gutena Forms', 'gutena-forms' ),
				__( 'Gutena Forms', 'gutena-forms' ),
				'manage_options',
				'gutena-forms',
				array( $this, 'forms_dashboard' ),
				'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTQuMzUyMTEgMTEuNDE1NUgwLjE1NzYyNUwxMS40MTcgMC4xNTU1NzJWNC4zMTc1TDQuMzUyMTEgMTEuNDE1NVoiIGZpbGw9IndoaXRlIi8+CjxwYXRoIGQ9Ik0xOS44MDYxIDExLjQxNDFIMjQuMDAwNkwxMi43NDEyIDAuMTU0MTA3VjQuMzE2MDRMMTkuODA2MSAxMS40MTQxWiIgZmlsbD0id2hpdGUiLz4KPHBhdGggZD0iTTQuMzQ5MTggMTIuNzM5M0gwLjE1NDY5NkwxMS40MTQxIDIzLjk5OTJWMTkuODM3M0w0LjM0OTE4IDEyLjczOTNaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBkPSJNMTkuODAzMiAxMi43NDAySDIzLjk5NzZMMTIuNzM4MyAyNC4wMDAyVjE5LjgzODNMMTkuODAzMiAxMi43NDAyWiIgZmlsbD0id2hpdGUiLz4KPHJlY3Qgd2lkdGg9IjguNzU3MjkiIGhlaWdodD0iMi41ODY4NiIgdHJhbnNmb3JtPSJtYXRyaXgoMSAwIDAgLTEgMTIuMDQxIDE1LjMyNjIpIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K',
				27,
			);

			if ( ! empty( $page_hook_suffix ) ) {
				add_action( 'admin_print_styles-' . $page_hook_suffix, array( $this, 'forms_listing_styles' ) );
				add_action( 'admin_print_scripts-' . $page_hook_suffix, array( $this, 'forms_listing_scripts' ) );
			}
		}

		/**
		 * Form Dashboard pages: including form list, respective form entries etc
		 *
		 * @version 1.1.0
		 * @modified 1.6.0
		 * @since 1.0.0
		 */
		public function forms_dashboard() {
			echo '<div id="gutena-forms__root" class="gutena-froms"></div>';
		}

		// Get Changelog from readme.txt file
		public function get_changelog() {
			$response = wp_remote_get( GUTENA_FORMS_PLUGIN_URL . 'readme.txt', array(
				'sslverify' => false
			) );
			if ( ! is_wp_error( $response ) ) {
				$response =  wp_remote_retrieve_body( $response );
				$response = explode( '== Changelog ==', $response, 2 );
				if ( 2 === count( $response ) ) {
					$response =  explode( '== Copyright ==', trim( $response[1] ), 2 );
					if ( ! empty( $response[0] ) ) {
						$response = $response[0];
						$response =  str_ireplace( "= ", "<span class='version'>", $response );
						$response =  str_ireplace( " =", "</span>", $response );
						return $response;
					}
				}
			}
			return '';
		}

		/**
		 * Go Pro Modal:
		 * to open this modal use : <a modalid="gutena-forms-go-pro-modal" href="#" class="gutena-forms-modal-btn"  ></a> - anywhere in html document
		 */
		public function go_pro_modal() {

			//Action Html
			if ( ! is_gutena_forms_pro( false ) ) {
				echo '
				<div id="gutena-forms-go-pro-modal"  class="gutena-forms-modal gf-small-modal" >
				<div class="gutena-forms-modal-content" >
				<span class="gf-close-btn gf-close-icon">&times;</span>
				<div class="gf-header" >
					<div class="gf-title" >'.__( 'Upgrade to Complete the Experience!', 'gutena-forms' ).'</div>
				</div>

				<div class="gf-body" >
				<p class="gf-description"> '.__( 'Get Gutena Forms Pro Today and Unlock all the Features', 'gutena-forms' ).'</p>
				<a href="https://gutenaforms.com/pricing/" target="_blank" rel="noopener noreferrer" class="gf-btn gf-pro-btn" > <span class="gf-btn-text">'.__( 'Go Premium', 'gutena-forms' ).'</span> </a>
				</div></div></div>';
			} else {
				do_action( 'gutena_forms_go_pro_modal' );
			}

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
				<a href="#" class="gf-btn gf-close-btn" > <span class="gf-btn-text">'.__( 'Cancel', 'gutena-forms' ).'</span> </a>
				<a href="#" class="gf-btn gf-entry-delete-btn" > <span class="gf-btn-text">'.__( 'Trash', 'gutena-forms' ).'</span> </a>
			</div>

			</div></div></div>';
		}

		public function forms_listing_styles() {
			do_action( 'gutena_forms__admin_enqueue_styles' );
			$assets_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php';
			if ( ! file_exists( $assets_file ) ) {
				return;
			}

			wp_enqueue_style( 'gutena-forms-dashboard' );
		}

		public function forms_listing_scripts() {
			do_action( 'gutena_forms__admin_enqueue_scripts' );
			$assets_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php';
			if ( ! file_exists( $assets_file ) ) {
				return;
			}

			wp_enqueue_script( 'gutena-forms-dashboard' );

			if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/localize/array-gutena-forms-admin.php' ) ) {
				$localized_assets = include GUTENA_FORMS_DIR_PATH . 'includes/admin/localize/array-gutena-forms-admin.php';
				wp_localize_script( 'gutena-forms-dashboard', 'gutenaFormsAdmin', $localized_assets );
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

		//view dashboard notice
		public function view_dashboard_notice() {
			$notice_id = "gutena-forms-view-dashboard-notice";
			$notice = $this->get_notices_and_status( $notice_id );
			if ( false === $notice['dismissed'] ) {
				echo '<div id="'.esc_attr( $notice_id ).'" class="notice notice-info is-dismissible gutena-forms-admin-notice" style="display:flex;align-items:center; gap:6px;" > <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.22379 11.333H0.00196697L11.3347 0.000257024V4.1891L4.22379 11.333Z" fill="#0DA88C"/>
					<path d="M19.7791 11.3325H24.001L12.6682 -0.000231258V4.18861L19.7791 11.3325Z" fill="#0DA88C"/>
					<path d="M4.22159 12.6655H-0.000230298L11.3325 23.9983V19.8094L4.22159 12.6655Z" fill="#0DA88C"/>
					<path d="M19.7772 12.6675H23.999L12.6663 24.0002V19.8114L19.7772 12.6675Z" fill="#0DA88C"/>
					<rect width="8.81436" height="2.60358" transform="matrix(1 0 0 -1 11.9624 15.2695)" fill="#0DA88C"/>
					</svg>
					<p> <strong>' . __( 'Exciting News!', 'gutena-forms' ) . ' </strong>'. __( ' Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="'.esc_url( admin_url( 'admin.php?page=gutena-forms' ) ).'" style="color: #E35D3F;margin-left:1rem;" > ' . __( 'See all Entries', 'gutena-forms' ) . ' </strong></a></p></div>';
			}
		}

		//enqueue admin scripts
		public function enqueue_scripts_admin() {
			if ( is_gutena_forms_pro() ) {
				wp_enqueue_script( 'gutena-forms-admin', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/js/admin.min.js', array(), GUTENA_FORMS_VERSION, true );
			}

			wp_localize_script(
				'gutena-forms-admin',
				'gutenaFormsAdmin',
				array(
					'dismiss_notice_action'    => 'gutena_forms_dismiss_notice',
					'ajax_url'           	=> admin_url( 'admin-ajax.php' ),
					'nonce'                 => wp_create_nonce( 'gutena_Forms' ),
				)
			);

			$assets_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php';
			if ( file_exists( $assets_file ) ) {
				$assets = include $assets_file;

				wp_register_style(
					'gutena-forms-dashboard',
					GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/index.css',
					array_filter(
						$assets['dependencies'],
						function ( $dependency ) {
							return wp_style_is( $dependency, 'registered' );
						}
					),
					$assets['version'],
					'all'
				);
				wp_register_script(
					'gutena-forms-dashboard',
					GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/index.js',
					array_filter(
						$assets['dependencies'],
						function ( $dependency ) {
							return wp_script_is( $dependency, 'registered' );
						}
					),
					$assets['version'],
					true
				);
			}
		}

		//get and check if provided notice id is dismissed or not
		private function get_notices_and_status( $notice_id = '' ) {
			$notices = get_option( 'gutena_forms_dismiss_notices', array() );
			return array(
				'notices' => $notices,
				'dismissed' => ( empty( $notice_id ) || ( ! empty( $notices ) && in_array( $notice_id, $notices ) ) )
			);
		}

		//dismiss notice ajax
		public function dismiss_notice() {
			check_ajax_referer( 'gutena_Forms', 'gfnonce' );
			if ( $this->is_gfadmin() && ! empty( $_POST['notice_id'] ) ) {
				$notice_id = sanitize_key( wp_unslash( $_POST['notice_id'] ) );
				$notice = $this->get_notices_and_status( $notice_id );
				if ( is_array( $notice['notices'] ) && false === $notice['dismissed'] ) {
					array_push( $notice['notices'], $notice_id );
					update_option( 'gutena_forms_dismiss_notices', $notice['notices'] );
				}
			}

			wp_send_json_success( array(
				'status'  => 'success',
				'message' => __( 'Notice dismissed successfully', 'gutena-forms' ),
			) );
		}

		//check if data tables exists
		public function is_forms_store_exists() {
			return empty(  get_option( 'gutena_forms_store_version', false ) ) ? false : true;
		}
	}

	Gutena_Forms_Admin::get_instance();
}
