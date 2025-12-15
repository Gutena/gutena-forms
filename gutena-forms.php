<?php
/**
 * Plugin Name:       Gutena Forms - Contact Forms Block
 * Description:       Gutena Forms is the easiest way to create forms inside the WordPress block editor. Our plugin does not use jQuery and is lightweight, so you can rest assured that it won’t slow down your website. Instead, it allows you to quickly and easily create custom forms right inside the block editor.
 * Requires at least: 6.5
 * Requires PHP:      5.6
 * Version:           1.6.0
 * Author:            Gutena Forms
 * Author URI:        https://gutenaforms.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gutena-forms
 *
 * @package           gutena-forms
 */

defined( 'ABSPATH' ) || exit;

include_once plugin_dir_path( __FILE__ ) . 'constants.php';
include_once GUTENA_FORMS_DIR_PATH . 'freemius.php';

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms' ) ) :
	class Gutena_Forms {

		// The instance of this class
		private static $instance = null;

		/**
		 * The submit handler instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Submit_Handler $submit_handler Submit handler instance.
		 */
		private $submit_handler;

		/**
		 * The block registry instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Block_Registry $block_registry Block registry instance.
		 */
		private $block_registry;

		/**
		 * The form schema instance.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Form_Schema $form_schema Form schema instance.
		 */
		private $form_schema;

		// Returns the instance of this class.
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			$this->includes();
			$this->initialize_modules();
			$this->initialize_security();
			$this->initialize_email();
			$this->run();
		}

		/**
		 * Including required dependencies
		 *
		 * @since 1.5.0
		 */
		private function includes() {
			include_once GUTENA_FORMS_DIR_PATH . 'includes/helpers/class-gutena-forms-helper.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/handlers/class-gutena-forms-submit-handler.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-gutena-forms-block-registry.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-gutena-forms-form-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-gutena-forms-field-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-forms-form-schema.php';

			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-cpt.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email-report/email-reports.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-migration.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-dummy-fields.php';

			// Security classes
			include_once GUTENA_FORMS_DIR_PATH . 'includes/security/class-gutena-forms-security-abstract.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/security/class-gutena-forms-security-manager.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/security/class-gutena-forms-recaptcha.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/security/class-gutena-forms-cloudflare-turnstile.php';

			// Email classes
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email/class-gutena-forms-email-abstract.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email/class-gutena-forms-email-manager.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email/class-gutena-forms-email-admin.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email/class-gutena-forms-email-user.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email/class-gutena-forms-email-weekly-report.php';
		}

		/**
		 * Initialize required modules
		 *
		 * @since 1.5.0
		 */
		private function initialize_modules() {
			$this->submit_handler = Gutena_Forms_Submit_Handler::get_instance();
			$this->block_registry = Gutena_Forms_Block_Registry::get_instance();
			$this->form_schema    = Gutena_Forms_Form_Schema::get_instance();
		}

		/**
		 * Initialize security implementations
		 *
		 * @since 1.6.0
		 */
		private function initialize_security() {
			// Register built-in security implementations
			Gutena_Forms_Security_Manager::register( 'Gutena_Forms_Recaptcha' );
			Gutena_Forms_Security_Manager::register( 'Gutena_Forms_Cloudflare_Turnstile' );
			
			// Allow third-party plugins to register their own
			do_action( 'gutena_forms_register_security' );
		}

		/**
		 * Initialize email implementations
		 *
		 * @since 1.6.0
		 */
		private function initialize_email() {
			// Register email classes
			Gutena_Forms_Email_Manager::register( 'admin_notification', 'Gutena_Forms_Email_Admin' );
			Gutena_Forms_Email_Manager::register( 'user_autoresponder', 'Gutena_Forms_Email_User' );
			Gutena_Forms_Email_Manager::register( 'weekly_report', 'Gutena_Forms_Email_Weekly_Report' );
		}

		/**
		 * Running the whole plugin
		 *
		 * @since 1.5.0
		 */
		private function run() {
			add_action( 'init', array( $this->block_registry, 'register_blocks_and_scripts' ) );
			add_action( 'init', array( $this->block_registry, 'register_blocks_styles' ) );
			add_action( 'wp_ajax_gutena_forms_submit', array( $this->submit_handler, 'handle_submit' ) );
			add_action( 'wp_ajax_nopriv_gutena_forms_submit', array( $this->submit_handler, 'handle_submit' ) );
			add_action( 'save_post', array( $this->form_schema, 'save_forms_schema' ), 10, 3 );
			add_action( 'added_post_meta', array( $this->form_schema, 'save_forms_pattern' ), 10, 4 );

			add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ), -1 );

			$this->load_dashboard();
		}

		public function plugin_action_links( $prev_links ) {

			if ( ! Gutena_Forms_Helper::has_pro() ) {
				$new_link = sprintf(
					'<a style="color: #e35d3f; font-weight: 600;" target="_blank" href="https://gutenaforms.com/pricing/?utm_source=all_plugins&utm_medium=website&utm_campaign=free_plugin">%s</a>',
					__( 'Get Gutena Forms Pro' )
				);

				// required link in first place
				array_unshift( $prev_links, $new_link );
			}

			return $prev_links;
		}

		private function load_dashboard() {
			if ( ! class_exists( 'Gutena_Forms_Admin' ) && file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php';
			}
		}

		public function register_category( $block_categories, $editor_context ) {
			$fields = wp_list_pluck( $block_categories, 'slug' );

			if ( ! empty( $editor_context->post ) && ! in_array( 'gutena', $fields, true ) ) {
				$block_categories[] = array(
					'slug' => 'gutena',
					'title' => __('Gutena', 'gutena-forms'),
				);

				if ( ! Gutena_Forms_Helper::has_pro() ) {
					$block_categories[] = array(
						'slug' => 'gutena-pro',
						'title' => 'Gutena Forms Pro',
					);
				}
			}

			return $block_categories;
		}
	}

	Gutena_Forms::get_instance();
endif;
