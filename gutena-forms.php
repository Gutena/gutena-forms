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

			add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
			add_action( 'added_post_meta', array( $this, 'save_gutena_forms_pattern' ), 10, 4 );
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

		// save form schema
		public function save_gutena_forms_schema( $post_id, $post, $update ) {
			//post should not be a rivision or trash
			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) ) {
				return;
			}

			//block pattern
			if ( $update && 'wp_block' == $post->post_type ) {

				$wp_pattern_sync_status = get_post_meta( $post->ID, 'wp_pattern_sync_status', true );
				if ( ! empty( $wp_pattern_sync_status ) && 'unsynced' == $wp_pattern_sync_status ) {
					// unhook this function so it doesn't loop infinitely
					remove_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10 );
					//correct and save unsynced pattern
					$this->correct_gutena_forms_pattern( $post );
					// re-hook this function.
					add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
					return;
				}
			}

			 // developer.wordpress.org/reference/functions/parse_blocks/
			$form_schema = $this->get_form_schema( parse_blocks( $post->post_content ) );
			if ( empty( $form_schema ) || ! is_array( $form_schema ) ) {
				return;
			}

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );

			// Save gutena form schema in wp option
			if ( ! empty( $form_schema['form_schema'] ) && is_array( $form_schema['form_schema'] ) ) {
				$gutena_forms_blocks = explode( '<!-- wp:gutena/forms', $post->post_content );
				foreach ( $form_schema['form_schema'] as  $formSchema ) {
					if ( ! empty( $formSchema['form_attrs']['formID'] ) ) {
						//get block markup
						foreach ($gutena_forms_blocks as $gf_block) {
							if ( false !== stripos( $gf_block, $formSchema['form_attrs']['formID'] ) ) {
								$gf_block = explode( 'wp:gutena/forms -->', $gf_block );
								$formSchema['block_markup'] = '<!-- wp:gutena/forms' . $gf_block[0] . 'wp:gutena/forms -->';
								break;
							}
						}
						//filter for formSchema
						$formSchema_filtered = apply_filters( 'gutena_forms_save_form_schema', $formSchema, $formSchema['form_attrs']['formID'], $gutena_form_ids );
						//Save form schema
						update_option(
							sanitize_key( $formSchema['form_attrs']['formID'] ),
							Gutena_Forms_Helper::sanitize_array( $formSchema_filtered, true )
						);

						//Save Google reCAPTCHA details
						if ( ! empty( $formSchema['form_attrs']['recaptcha'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['site_key'] ) && ! empty( $formSchema['form_attrs']['recaptcha']['secret_key'] ) ) {
							update_option(
								'gutena_forms_grecaptcha',
								Gutena_Forms_Helper::sanitize_array( $formSchema['form_attrs']['recaptcha'] )
							);
						}

						// cloudflare turnstile
						if ( ! empty( $formSchema['form_attrs']['cloudflareTurnstile'] ) && ! empty( $formSchema['form_attrs']['cloudflareTurnstile']['site_key'] ) && ! empty( $formSchema['form_attrs']['cloudflareTurnstile']['secret_key'] ) ) {
							update_option(
								'gutena_forms_cloudflare_turnstile',
								Gutena_Forms_Helper::sanitize_array( $formSchema['form_attrs']['cloudflareTurnstile'] )
							);
						}

						//Save common form messages
						if ( ! empty( $formSchema['form_attrs']['messages'] ) && is_array( $formSchema['form_attrs']['messages'] ) ) {
							update_option(
								'gutena_forms_messages',
								$formSchema['form_attrs']['messages']
							);
						}
					}
				}
			}

			// Save gutena form ids in array gutena_form_ids
			if ( ! empty( $form_schema['form_ids'] ) ) {

				if ( ! empty( $gutena_form_ids ) && is_array( $gutena_form_ids ) ) {
					$gutena_form_ids = array_merge( $gutena_form_ids, $form_schema['form_ids'] );
				} else {
					$gutena_form_ids = $form_schema['form_ids'];
				}
				//unique ids only
				$gutena_form_ids = array_unique( $gutena_form_ids );

				update_option(
					'gutena_form_ids',
					Gutena_Forms_Helper::sanitize_array( $gutena_form_ids )
				);
			}

		}

		/**
		 * Correct unsynced form pattern
		 * remove form id from unsynched pattern so that it can be reuse
		 *
		 * @param  integer $meta_id    ID of the meta data field
		 * @param  integer $post_id    Post ID
		 * @param  string $meta_key    Name of meta field
		 * @param  string $meta_value  Value of meta field
		 */
		public function save_gutena_forms_pattern( $meta_id, $post_id, $meta_key, $meta_value ) {
			//return if post meta is not for unsynced pattern
			if ( empty( $post_id ) || empty( $meta_key ) || empty( $meta_value ) || 'wp_pattern_sync_status' != $meta_key || 'unsynced' != $meta_value ) {
				return;
			}

			$post = get_post( $post_id );
			$this->correct_gutena_forms_pattern( $post );

		}

		/**
		 * Correct and save unsynced pattern
		 *
		 * @param object $post
		 * @param boolean $check_meta should check meta key or not
		 *
		 */
		private function correct_gutena_forms_pattern( $post ) {
			static $func_call = 0;
			//patterns are store under 'wp_block' post type
			//return if post is empty or not a pattern post_type
			if ( $func_call > 0 || empty( $post ) || empty( $post->ID ) ||  'wp_block' != $post->post_type || empty( $post->post_content ) || false === stripos( $post->post_content,"{\"formID\"" )  ) {
				return;
			}

			//get form id
			$first_extract = "\",\"formName\":";
			if ( false === stripos( $post->post_content, $first_extract ) ) {
				$first_extract = "\",\"formClasses\":";
			}

			$post_content = explode( $first_extract, $post->post_content );
			$post_content = explode( "{\"formID\":\"gutena_forms_ID_", $post_content[0] );
			$post_content = end( $post_content );
			$formID = wp_unslash( $post_content );
			$formID = "gutena_forms_ID_". $formID;
			//remove form id
			$post_content = str_ireplace( $formID, "" , $post->post_content );
			//count function call
			$func_call++;
			//Update pattern
			wp_update_post( array(
				'ID'           => $post->ID,
				'post_content' => $post_content,
			) );

		}


		// Get Form schema from block parsing
		private function get_form_schema( $blocks, $formID = 0 ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return;
			}

			$form_schema = array();
			$form_ids    = array();
			$innerblocks = array();

			foreach ( $blocks as $block ) {

				if ( ! empty( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] && ! empty( $block['attrs']['formID'] ) ) {
					$formID                               = $block['attrs']['formID'];
					$form_ids[]                           = $formID;
					$form_schema[ $formID ]['form_attrs'] = $block['attrs'];
				}

				if ( ! empty( $block['blockName'] ) && 'gutena/form-field' === $block['blockName'] && ! empty( $block['attrs']['nameAttr'] ) ) {
					$form_schema[ $formID ]['form_fields'][ $block['attrs']['nameAttr'] ] = $block['attrs'];
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$innerblocks = $this->get_form_schema( $block['innerBlocks'], $formID );
					$form_schema = array_merge_recursive( $form_schema, $innerblocks['form_schema'] );
					$form_ids    = array_merge( $form_ids, $innerblocks['form_ids'] );
				}
			}

			return array(
				'form_ids'    => $form_ids,
				'form_schema' => $form_schema,
			);
		}

	}

	Gutena_Forms::get_instance();

}
