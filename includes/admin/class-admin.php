<?php
/**
 * Admin Area
 * like: file upload
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms_Admin' ) && class_exists( 'Gutena_Forms' ) ) :
	/**
	 * Gutena Forms admin
	 */
	class Gutena_Forms_Admin extends Gutena_Forms {
		/**
		 * Form Id
		 *
		 * @var string $form_id Form Id.
		 */
		private $form_id = '';

		/**
		 * The instance of this class
		 *
		 * @var Gutena_Forms_Admin $instance The instance of this class.
		 */
		private static $instance = null;

		/**
		 * Returns the instance of this class.
		 *
		 * @return Gutena_Forms_Admin
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->includes();
			$this->initialize();
			$this->run();
		}

		/**
		 * Including the files for loading admin area
		 */
		private function includes() {
			if ( class_exists( 'Gutena_Forms_Activate_Deactivate' ) ) {
				return false;
			}

			/**
			 * Activate-deactivate : Action on activation
			 * store : common db functions
			 * create-store extends store : create tables for storing form submission
			 * manage-store extends store : manage store : Read, Update and delete
			 */
			foreach ( array( 'activate-deactivate', 'store', 'create-store', 'manage-store' ) as $key => $filename ) {
				if ( file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-' . $filename . '.php' ) ) {
					require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-' . $filename . '.php';
				}
			}

			include_once GUTENA_FORMS_DIR_PATH . 'includes/helpers/class-admin-helper.php';
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
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/validation-messages/class-validation-messages.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/integrations/class-inegrations.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/mcp/class-mcp.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/modules/settings-migrator/class-settings-migrator.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/admin/rest-api/class-rest-api-controller.php';
		}

		/**
		 * Initialize form id if exist in url
		 */
		private function initialize() {
			if ( ! empty( $_GET['formid'] ) && is_numeric( $_GET['formid'] ) ) {
				$this->form_id = absint( sanitize_key( $_GET['formid'] ) );
			}
		}

		/**
		 * Running the admin file
		 */
		private function run() {
			add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'load_admin_classes' ) );
			add_action( 'admin_head', array( $this, 'admin_head' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ) );
			add_action( 'current_screen', array( $this, 'redirect_when_old_screen' ) );

			if ( ! is_gutena_forms_pro( false ) ) {
				add_action( 'admin_notices', array( $this, 'view_dashboard_notice' ) );
				add_action( 'wp_ajax_gutena_forms_dismiss_notice', array( $this, 'dismiss_notice' ) );
			}
		}

		/**
		 * Admin head
		 */
		public function admin_head() {
			if ( ! is_gutena_forms_pro() ) {
				echo '<style>
					#toplevel_page_gutena-forms ul li:last-child a{background:#27a68a!important;border:1px solid #27a68a!important;color:#fff!important;font-weight:600}#toplevel_page_gutena-forms ul li:last-child a:focus,#toplevel_page_gutena-forms ul li:last-child a:hover{color:#fff!important}
				</style>';

				echo '<script type="text/javascript">
					!function(){var e;e=()=>{const e=document.querySelector("#toplevel_page_gutena-forms ul li:last-child a");e&&"Upgrade"===e.innerText&&e.setAttribute("target","_blank")},"undefined"!=typeof document&&("complete"!==document.readyState&&"interactive"!==document.readyState?document.addEventListener("DOMContentLoaded",e):e())}();
				</script>';
			}
		}

		/**
		 * Load admin classes
		 */
		public function load_admin_classes() {
			Gutena_Forms_Admin_Helper::optimize_submenu();
			Gutena_Forms_Admin_Helper::pricing_page_redirection();
			Gutena_Forms_Admin_Helper::feature_request_redirection();
			Gutena_Forms_Admin_Helper::include_wp_list_table();
		}

		/**
		 * Admin Menu
		 */
		public function register_admin_menu() {
			if ( ( ! has_filter( 'gutena_forms_check_user_access' ) && ! Gutena_Forms_Admin_Helper::is_admin() ) || ! class_exists( 'Gutena_Forms_Store' ) || ! apply_filters( 'gutena_forms_check_user_access', true, 'view_entries' ) ) {
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

			add_submenu_page(
				'gutena-forms',
				__( 'Dashboard', 'gutena-forms' ),
				__( 'Dashboard', 'gutena-forms' ),
				'manage_options',
				'admin.php?page=gutena-forms#/settings/dashboard'
			);

			add_submenu_page(
				'gutena-forms',
				__( 'Forms', 'gutena-forms' ),
				__( 'Forms', 'gutena-forms' ),
				'manage_options',
				'admin.php?page=gutena-forms#/settings/forms'
			);

			add_submenu_page(
				'gutena-forms',
				__( 'Add New Forms', 'gutena-forms' ),
				__( 'Add New Forms', 'gutena-forms' ),
				'manage_options',
				'post-new.php?post_type=gutena_forms'
			);

			add_submenu_page(
				'gutena-forms',
				__( 'Entries', 'gutena-forms' ),
				__( 'Entries', 'gutena-forms' ),
				'manage_options',
				'admin.php?page=gutena-forms#/settings/entries'
			);

			add_submenu_page(
				'gutena-forms',
				__( 'Settings', 'gutena-forms' ),
				__( 'Settings', 'gutena-forms' ),
				'manage_options',
				'admin.php?page=gutena-forms#/settings/settings/manage-status'
			);

			if ( ! is_gutena_forms_pro() ) {
				add_submenu_page(
					'gutena-forms',
					__( 'Upgrade', 'gutena-forms' ),
					__( 'Upgrade', 'gutena-forms' ),
					'manage_options',
					'https://gutenaforms.com/pricing/?utm_source=plugin_dashboard&utm_medium=website&utm_campaign=free_plugin'
				);
			}

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

		/**
		 * Enqueue styles for form listing page
		 */
		public function forms_listing_styles() {
			do_action( 'gutena_forms__admin_enqueue_styles' );
			$assets_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php';
			if ( ! file_exists( $assets_file ) ) {
				return;
			}

			wp_enqueue_style( 'gutena-forms-dashboard' );
		}

		/**
		 * Enqueue scripts for form listing page
		 */
		public function forms_listing_scripts() {
			do_action( 'gutena_forms__admin_enqueue_scripts' );
			$assets_file = GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php';
			if ( ! file_exists( $assets_file ) ) {
				return;
			}

			wp_enqueue_script( 'gutena-forms-dashboard' );

			$gutena_forms_admin = include plugin_dir_path( __FILE__ ) . 'localize/array-gutena-forms-admin.php';
			wp_localize_script( 'gutena-forms-dashboard', 'gutenaFormsAdmin', $gutena_forms_admin );
		}

		/**
		 * Unserialize data
		 *
		 * @param mixed $data Data to serialize.
		 *
		 * @return mixed|string
		 */
		public function maybe_unserialize( $data ) {
			return ( function_exists( 'maybe_unserialize' ) && ! empty( $data ) ) ? maybe_unserialize( $data ) : $data;
		}

		/**
		 * Get current user id
		 *
		 * @return int
		 */
		public function current_user_id() {
			// current user id.
			return intval( function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0 );
		}

		/**
		 * View dashboard notice
		 */
		public function view_dashboard_notice() {
			$notice_id = 'gutena-forms-view-dashboard-notice';
			$notice    = $this->get_notices_and_status( $notice_id );
			if ( false === $notice['dismissed'] ) {
				echo '<div id="' . esc_attr( $notice_id ) . '" class="notice notice-info is-dismissible gutena-forms-admin-notice" style="display:flex;align-items:center; gap:6px;" > <svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M4.22379 11.333H0.00196697L11.3347 0.000257024V4.1891L4.22379 11.333Z" fill="#0DA88C"/>
					<path d="M19.7791 11.3325H24.001L12.6682 -0.000231258V4.18861L19.7791 11.3325Z" fill="#0DA88C"/>
					<path d="M4.22159 12.6655H-0.000230298L11.3325 23.9983V19.8094L4.22159 12.6655Z" fill="#0DA88C"/>
					<path d="M19.7772 12.6675H23.999L12.6663 24.0002V19.8114L19.7772 12.6675Z" fill="#0DA88C"/>
					<rect width="8.81436" height="2.60358" transform="matrix(1 0 0 -1 11.9624 15.2695)" fill="#0DA88C"/>
					</svg>
					<p> <strong>' . __( 'Exciting News!', 'gutena-forms' ) . ' </strong>' . __( ' Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="' . esc_url( admin_url( 'admin.php?page=gutena-forms' ) ) . '" style="color: #E35D3F;margin-left:1rem;" > ' . __( 'See all Entries', 'gutena-forms' ) . ' </strong></a></p></div>';
			}
		}

		/**
		 * Enqueue admin scripts
		 */
		public function enqueue_scripts_admin() {
			if ( is_gutena_forms_pro() ) {
				wp_enqueue_script( 'gutena-forms-admin', GUTENA_FORMS_PLUGIN_URL . 'assets/minify/js/admin.min.js', array(), GUTENA_FORMS_VERSION, true );
			}

			wp_localize_script(
				'gutena-forms-admin',
				'gutenaFormsAdmin',
				array(
					'dismiss_notice_action' => 'gutena_forms_dismiss_notice',
					'ajax_url'              => admin_url( 'admin-ajax.php' ),
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

		/**
		 * Redirect old screen to new dashboard
		 */
		public function redirect_when_old_screen() {
			if ( isset( $_GET['post_type'] ) && 'gutena_forms' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) {
				global $current_screen;
				if ( ! is_null( $current_screen ) && 'edit-gutena_forms' === $current_screen->id ) {
					wp_safe_redirect(
						add_query_arg(
							array(
								'page' => 'gutena-forms#/settings/forms',
							),
							admin_url( 'admin.php' )
						)
					);
					exit;
				}
			}
		}

		/**
		 * Get and check if provided notice id is dismissed or not
		 *
		 * @param string $notice_id Notice Id.
		 *
		 * @return array
		 */
		private function get_notices_and_status( $notice_id = '' ) {
			$notices = get_option( 'gutena_forms_dismiss_notices', array() );
			return array(
				'notices'   => $notices,
				'dismissed' => ( empty( $notice_id ) || ( ! empty( $notices ) && in_array( $notice_id, $notices ) ) ),
			);
		}

		/**
		 * Dismiss notice ajax
		 */
		public function dismiss_notice() {
			check_ajax_referer( 'gutena_Forms', 'gfnonce' );
			if ( Gutena_Forms_Admin_Helper::is_admin() && ! empty( $_POST['notice_id'] ) ) {
				$notice_id = sanitize_key( wp_unslash( $_POST['notice_id'] ) );
				$notice    = $this->get_notices_and_status( $notice_id );
				if ( is_array( $notice['notices'] ) && false === $notice['dismissed'] ) {
					array_push( $notice['notices'], $notice_id );
					update_option( 'gutena_forms_dismiss_notices', $notice['notices'] );
				}
			}

			wp_send_json_success(
				array(
					'status'  => 'success',
					'message' => __( 'Notice dismissed successfully', 'gutena-forms' ),
				)
			);
		}

		/**
		 * Check if data tables exists
		 *
		 * @return bool
		 */
		public function is_forms_store_exists() {
			return empty( get_option( 'gutena_forms_store_version', false ) ) ? false : true;
		}
	}

	Gutena_Forms_Admin::get_instance();
endif;
