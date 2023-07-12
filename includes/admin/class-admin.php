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
			$pagetype = empty( $_GET['pagetype'] ) ? '': sanitize_key( wp_unslash( $_GET['pagetype'] ) );
			echo '<div id="gutena-forms-dashboard-page" class="gutena-forms-dashboard '.( is_gutena_forms_pro() ? '':'gf-basic' ).' " style="display:none;" >';  

			if ( '' === $pagetype ) {
				if ( '' === $this->form_id ) {
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
							//Provide data for form submission script
							wp_localize_script(
								'gutena-forms-dashboard',
								'gutenaFormsList',
								array(
									'list'       => $form_list
								)
							);
						}
						$form_table = new Gutena_Forms_Entries_Table();
					}
				}
			}

			//view-entry page
			if ( ! is_gutena_forms_pro() ) {
				//Entry View Page
				if ( ! empty( $_GET['form_entry_id'] ) && is_numeric( $_GET['form_entry_id'] ) && 'viewentry' === $pagetype  ) {
					
					//Provide data for form submission script
					wp_localize_script(
						'gutena-forms-dashboard',
						'gutenaFormsEntryDetails',
						array(
							'entry_data'       => $form_store->get_entry_details( absint( wp_unslash( $_GET['form_entry_id'] ) ) ),
						)
					);
				} 
			} 

			//data require to create page by react js
			$this->script_page_data( $pagetype );

			//Top header
			echo  $form_store->get_dashboard_header();
			echo '<div id="gutena-forms-dashboard-admin-message"></div>';
			//Placeholder for breadcrumb
			if ( ( '' === $pagetype && ! empty( $this->form_id ) ) || in_array( $pagetype, array( 'viewentry') ) ) {
				echo '<div id="gfp-page-breadcrumb"></div>';
			}
			//placeholder for react based dashboard
			echo '<div id="gfp-page-' . esc_attr( $pagetype ) . '"></div>';
			do_action( 'gutena_forms_entries_load_custom_page' );
			
			//render list
            if ( ! empty( $form_table ) ) {
                $form_table->render_list_table( $form_store );
				$this->entry_delete_modal();
            }
			//pro modal
			$this->go_pro_modal();
			echo '</div>';
		}

		/**
		 * Load script page data
		 */
		private function script_page_data( $pagetype = '' ){

			if ( ! function_exists('wp_localize_script') ) {
				return;
			}

			//introduction page data
			if ( 'introduction' === $pagetype ) {
				/**
				 ** EDD checkout:  https://easydigitaldownloads.com/docs/creating-custom-add-to-cart-links/
				 **/
				wp_localize_script(
					'gutena-forms-dashboard',
					'gutenaFormsIntroduction',
					array(
						'section' => array(
							'welcome'   => array(
								'into_img'			=> esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/welcome.png' ),
								'intro_video_link' => esc_url( 'https://www.youtube.com/watch?v=2ZVS3b_02CA' ),
								'title'			=> __( 'Welcome to Gutena Forms!', 'gutena-forms' ),
								'description' 	=> __( 'Gutena is a free block theme for WordPress with modern block patterns in-built. It comes packed with beautiful design patterns which suits a variety of use cases. Gutena aims to be at the forefront of WordPress FSE (Full Site Editing) philosophy.  Gutena is a free block theme for WordPress with modern block patterns in-built.', 'gutena-forms' ),
								'pricing_btn_name'	=> __( 'See Pricing', 'gutena-forms' ),
								'help_btn_name'	=> __( 'Need Help?', 'gutena-forms' ),
								'help_btn_link'	=> esc_url( 'https://wordpress.org/support/plugin/gutena-forms/' )
							),
							'features'	=> array(
								'title' => __( 'Gutena Forms Features', 'gutena-forms' ),
								'items' => array(
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Build form with WP Editor', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Entry Management', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Advance Filter for Entries', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Entry Notes', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Responsive Mobile Friendly', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Dashboard', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Status Management', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Tags Management', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'User Access Management', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'No jQuery', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Google reCAPTCHA', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/feature.svg' ),
										'title' => __( 'Fully customizable', 'gutena-forms' ),
										'description' => __( 'Dummy pages load in less than 3s and have very low bounce rates.', 'gutena-forms' ),
									),
								)
							),
							'fields'	=> array(
								'title' => __( 'Gutena Forms Input Fields', 'gutena-forms' ),
								'items' => array(
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Text', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Number', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Range', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Email', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Dropdown', 'gutena-forms' ),
									),
									array(
										'is_pro' => false,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Checkbox', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Phone', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Radio', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Date', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Time', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Rating', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'Country', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'State', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'File Upload', 'gutena-forms' ),
									),
									array(
										'is_pro' => true,
										'icon' => esc_url( GUTENA_FORMS_PLUGIN_URL . 'assets/img/fields.svg' ),
										'title' => __( 'URL', 'gutena-forms' ),
									),
								)
							),
							'pricing'	=> array(
								'title' => __( 'Achieve more with Premium', 'gutena-forms' ),
								'subtitle' => __( 'Unlock the full potential of Gutena Form with Premium', 'gutena-forms' ),
								'features' => array(
									__( 'All Premium Input Fields', 'gutena-forms' ),
									__( 'All Premium Features', 'gutena-forms' ),
									__( 'One-on-One Support', 'gutena-forms' ),
								),
								'btn_name' => __( 'Get Started', 'gutena-forms' ),
								'items' => array(
									array(
										'title' => __( 'TRIAL PLAN', 'gutena-forms' ),
										'price'	=> '$0.00',
										'description' => __( '1 Site - 14 Days Trial', 'gutena-forms' ),
										'link'	=> 'https://shop.gutena.io/checkout/?edd_action=add_to_cart&download_id=1493807&edd_options[price_id]=4',
									),
									array(
										'title' => __( 'ESSENTIAL PLAN', 'gutena-forms' ),
										'price'	=> '$9.00',
										'description' => __( '1 Site', 'gutena-forms' ),
										'link'	=> 'https://shop.gutena.io/checkout/?edd_action=add_to_cart&download_id=1493807&edd_options[price_id]=1',
									),
									array(
										'title' => __( 'DEVELOPER PLAN', 'gutena-forms' ),
										'price'	=> '$59.00',
										'description' => __( '5 Site', 'gutena-forms' ),
										'link'	=> 'https://shop.gutena.io/checkout/?edd_action=add_to_cart&download_id=1493807&edd_options[price_id]=2',
									),
									array(
										'title' => __( 'AGENCY PLAN', 'gutena-forms' ),
										'price'	=> '$119.00',
										'description' => __( '25 Sites', 'gutena-forms' ),
										'link'	=> 'https://shop.gutena.io/checkout/?edd_action=add_to_cart&download_id=1493807&edd_options[price_id]=3',
									),
								)
								),
							'faq'		=> array(
								'title' => __( 'Frequently asked questions', 'gutena-forms' ),
								'items' => array(
									array(
										'title' => __( 'Do you offer support?
										', 'gutena-forms' ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
									array(
										'title' => __( "What's special about your plugin?

										", "gutena-forms" ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
									array(
										'title' => __( 'How much do you charge?

										', 'gutena-forms' ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
									array(
										'title' => __( 'Do you offer support?
										', 'gutena-forms' ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
									array(
										'title' => __( 'Do you offer refunds?
										', 'gutena-forms' ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
									array(
										'title' => __( 'What are the server requirements?
										', 'gutena-forms' ),
										'description' => __( 'Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts. Separated they live in Bookmarksgrove. Far far away, behind the word mountains, far from the countries Vokalia and Consonantia, there live the blind texts.', 'gutena-forms' ),
									),
								)
							)
						)
						
					)
				);
			}

			//knowledge base page data
			if ( 'doc' === $pagetype ) {
				$gutena_url = 'https://gutena.io';
				wp_localize_script(
					'gutena-forms-dashboard',
					'gutenaFormsDoc',
					array(
						'topics' => array(
							'title' => esc_html__( 'How to Topics and Tips', 'gutena-forms' ),
							'items' => array(
								array(
									'heading' =>  esc_html__( 'How to reuse Gutena forms on Multiple Pages', 'gutena-forms' ),
									'link' => esc_url( $gutena_url . '/reuse-gutena-forms-on-multiple-pages' ),
								),
								array(
									'heading' =>  esc_html__( 'How to generate Google reCaptcha Site Key and Secret Key', 'gutena-forms' ),
									'link' => esc_url( $gutena_url . '/how-to-generate-google-recaptcha-site-key-and-secret-key' ),
								),
							)

						),
						'support' => array(
							'title' => esc_html__( 'Need Help?', 'gutena-forms' ),
							'description' => esc_html__( 'Have a question, we are happy to help! Get in touch with our support team.', 'gutena-forms' ),
							'documentation_link' => esc_url( $gutena_url . '/blog' ),
							'documentation_text' => esc_html__( 'Documentation', 'gutena-forms' ),
							'link_text' => esc_html__( 'Submit Ticket', 'gutena-forms' ),
							'link_url' => esc_url( 'https://wordpress.org/support/plugin/gutena-forms/' ),
						),
						'changelog' => array(
							'title'       => esc_html__( 'Releases and fixes', 'gutena-forms' ),
							'description' => $this->get_changelog(),
						),
					),
				);
			}

			
		}

		// Get Changelog from readme.txt file
		private function get_changelog() {
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
				<a href="' . esc_url( admin_url( 'admin.php?page=gutena-forms&pagetype=introduction#gutena-forms-pricing' ) ) . '" class="gf-btn gf-pro-btn" > <span class="gf-btn-text">'.__( 'Go Premium - 14 Day Free Trial', 'gutena-forms' ).'</span> </a>
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
				<a href="#" class="gf-btn gf-close-btn" target="_blank" > <span class="gf-btn-text">'.__( 'Cancel', 'gutena-forms' ).'</span> </a>
				<a href="#" class="gf-btn gf-entry-delete-btn" target="_blank" > <span class="gf-btn-text">'.__( 'Trash', 'gutena-forms' ).'</span> </a>
			</div>
			
			</div></div></div>';
		}

		public function forms_listing_styles() {
			wp_enqueue_style( 'gutena-forms-dashboard-style', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/style-index.css', array( 'wp-components','wp-edit-blocks' ), GUTENA_FORMS_VERSION, 'all' );

			do_action( 'gutena_forms_dashboard_enqueue_style');

		}

		public function forms_listing_scripts() {

			$asset_file = include_once( GUTENA_FORMS_DIR_PATH . 'includes/admin/dashboard/build/index.asset.php' );

			if ( ! empty( $asset_file['dependencies'] ) && function_exists( 'admin_url' ) ) {
				
				wp_enqueue_script( 'gutena-forms-dashboard', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/index.js', $asset_file['dependencies'], $asset_file['version'], true );
				
				wp_enqueue_script( 'gutena-forms-dashboard-script', GUTENA_FORMS_PLUGIN_URL . 'includes/admin/dashboard/build/script.js', array(), $asset_file['version'], true );

				$dashboard_url =  esc_url( admin_url( 'admin.php?page=gutena-forms' ) );
				$is_admin = $this->is_gfadmin();
				//Provide data for form submission script
				wp_localize_script(
					'gutena-forms-dashboard-script',
					'gutenaFormsDashboard',
					array(
						'read_status_action'    => 'gutena_forms_entries_read',
						'ajax_url'           	=> admin_url( 'admin-ajax.php' ),
						'nonce'                 => wp_create_nonce( 'gutena_Forms' ),
						'entry_view_url'        => $dashboard_url.'&pagetype=viewentry&form_entry_id=',
						'entry_list_url' 		=> $dashboard_url.'&formid=',
						'page_url'				=> $dashboard_url.'&pagetype=',
						'is_gutena_forms_pro'   => is_gutena_forms_pro() ? '1' : '0',
						'is_admin'				=> $is_admin,
						'pagetype'				=> empty( $_GET['pagetype'] ) ? '': sanitize_key( wp_unslash( $_GET['pagetype'] ) ),
						'form_id'				=> $this->form_id,
						'dashboard_menu' => array(
							array(
								'slug'  => 'introduction',
								'title' => __( 'Introduction', 'gutena-forms' ),
								'enable' => true
							),
							array(
								'slug'  => '',
								'title' => __( 'Entries', 'gutena-forms' ),
								'enable' => true
							),
							array(
								'slug'  => 'settings',
								'title' => __( 'Settings', 'gutena-forms' ),
								'enable' => $is_admin
							),
							array(
								'slug'  => 'doc',
								'title' => __( 'Knowledge Base', 'gutena-forms' ),
								'enable' => true
							)

						)
						
					)
				);

				do_action( 'gutena_forms_dashboard_enqueue_scripts');
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