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

/**
 * Plugin dir path
 */
if ( ! defined( 'GUTENA_FORMS_FILE' ) ) {
	define( 'GUTENA_FORMS_FILE',  __FILE__ );
}

/**
 * Plugin dir path
 */
if ( ! defined( 'GUTENA_FORMS_DIR_PATH' ) ) {
	define( 'GUTENA_FORMS_DIR_PATH',  plugin_dir_path( __FILE__ ) );
}

/**
 * Plugin url
 */
if ( ! defined( 'GUTENA_FORMS_PLUGIN_URL' ) ) {
	define( 'GUTENA_FORMS_PLUGIN_URL', esc_url( trailingslashit( plugins_url( '', __FILE__ ) ) ) );
}

/**
 * Plugin version.
 */
if ( ! defined( 'GUTENA_FORMS_VERSION' ) ) {
	define( 'GUTENA_FORMS_VERSION', '1.5.0' );
}

if ( ! function_exists( 'gutena_forms__fs' ) ) :
	/**
	 * Initialize Freemius.
	 *
	 * @since 1.3.0
	 * @throws Freemius_Exception If unable to load Freemius.
	 * @return Freemius
	 */
	function gutena_forms__fs() {
		global $gutena_forms__fs;

		if ( is_null( $gutena_forms__fs ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'vendor/freemius/start.php';
			$gutena_forms__fs = fs_dynamic_init(
				array(
					'id'                  => '20975',
					'slug'                => 'gutena-forms',
					'type'                => 'plugin',
					'public_key'          => 'pk_d66286e6558c1d5d6a4ccf3304cfb',
					'is_premium'          => false,
					'has_addons'          => true,
					'has_paid_plans'      => false,
					'menu'                => array(
						'slug'       => 'gutena-forms',
						'contact'    => false,
						'support'    => false,
						'account'    => false,
						'first-path' => 'admin.php?page=gutena-forms&pagetype=introduction',
					),
				)
			);
		}

		return $gutena_forms__fs;
	}

	gutena_forms__fs();
	do_action( 'gutena_forms__fs_loaded' );
endif;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms' ) ) {
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

		//load form dashboard
		private function load_dashboard() {
			if ( ! class_exists( 'Gutena_Forms_Admin' ) && file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php';
			}
		}

		// Register Gutena category if not exists
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

}
