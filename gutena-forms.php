<?php
/**
 * Plugin Name:       Gutena Forms - Contact Forms Block
 * Description:       Gutena Forms is the easiest way to create forms inside the WordPress block editor. Our plugin does not use jQuery and is lightweight, so you can rest assured that it won’t slow down your website. Instead, it allows you to quickly and easily create custom forms right inside the block editor.
 * Requires at least: 6.5
 * Requires PHP:      5.6
 * Version:           1.8.0
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
	define( 'GUTENA_FORMS_FILE', __FILE__ );
}

/**
 * Plugin dir path
 */
if ( ! defined( 'GUTENA_FORMS_DIR_PATH' ) ) {
	define( 'GUTENA_FORMS_DIR_PATH', plugin_dir_path( __FILE__ ) );
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
	define( 'GUTENA_FORMS_VERSION', '1.8.0' );
}

/**
 * Option name prefix for form schema (security: prevents arbitrary option overwrite).
 */
if ( ! defined( 'GUTENA_FORMS_SCHEMA_OPTION_PREFIX' ) ) {
	define( 'GUTENA_FORMS_SCHEMA_OPTION_PREFIX', 'gutena_forms_schema_' );
}

if ( ! function_exists( 'gutena_forms_get_form_schema_option' ) ) {
	/**
	 * Get form schema option value. Checks non-prefixed first, then prefixed; if both exist, returns prefixed.
	 *
	 * @param string $form_id Form ID (option key).
	 * @param mixed  $default_value Default if neither option exists.
	 *
	 * @return mixed Form schema array or $default.
	 */
	function gutena_forms_get_form_schema_option( $form_id, $default_value = false ) {
		$form_id = sanitize_key( $form_id );
		if ( '' === $form_id ) {
			return $default_value;
		}
		$non_prefixed     = get_option( $form_id, null );
		$prefixed         = get_option( GUTENA_FORMS_SCHEMA_OPTION_PREFIX . $form_id, null );
		$has_non_prefixed = ( null !== $non_prefixed );
		$has_prefixed     = ( null !== $prefixed );
		if ( $has_non_prefixed && $has_prefixed ) {
			return $prefixed;
		}
		if ( $has_prefixed ) {
			return $prefixed;
		}
		if ( $has_non_prefixed ) {
			return $non_prefixed;
		}
		return $default_value;
	}
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
					'id'             => '20975',
					'slug'           => 'gutena-forms',
					'type'           => 'plugin',
					'public_key'     => 'pk_d66286e6558c1d5d6a4ccf3304cfb',
					'is_premium'     => false,
					'has_addons'     => true,
					'has_paid_plans' => false,
					'menu'           => array(
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

if ( ! function_exists( 'is_gutena_forms_pro' ) ) :
	/**
	 * Gutena Forms has pro
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	function is_gutena_forms_pro() {
		if ( did_action( 'plugins_loaded' ) ) {
			if ( defined( 'GUTENA_FORMS__PRO_LOADED' ) && GUTENA_FORMS__PRO_LOADED ) {
				return class_exists( 'Gutena_Forms_Pro' );
			}
		}

		return class_exists( 'Gutena_Forms_Pro' );
	}
endif;

/**
 * Abort if the class is already exists.
 */
if ( ! class_exists( 'Gutena_Forms' ) ) :
	/**
	 * Gutena Forms plugin main class
	 *
	 * @since 1.0.0
	 */
	class Gutena_Forms {

		/**
		 * The instance of the class
		 *
		 * @since 1.0.0
		 * @var Gutena_Forms $instance The instance of the class.
		 */
		private static $instance = null;

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.0.0
		 * @return Gutena_Forms
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->includes();
			$this->run();
		}

		/**
		 * Including required dependencies
		 *
		 * @since 1.5.0
		 */
		private function includes() {
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-cpt.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email-report/email-reports.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-migration.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-dummy-fields.php';

			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-form-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-field-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-form-field-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-existing-forms-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-field-label-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/handlers/class-form-submit-handler.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/rest-api/class-rest-api.php';
		}

		/**
		 * Running the whole plugin
		 *
		 * @since 1.5.0
		 */
		private function run() {
			gutena_forms__fs()->add_filter( 'hide_account_tabs', '__return_true' );

			add_action( 'init', array( $this, 'register_blocks_and_scripts' ) );
			add_action( 'init', array( $this, 'register_blocks_styles' ) );
			add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
			add_action( 'added_post_meta', array( $this, 'save_gutena_forms_pattern' ), 10, 4 );
			add_action( 'wp_ajax_gutena_forms_submit', array( Gutena_Forms_Submit_Form_Handler::get_instance(), 'handle_submit' ) );
			add_action( 'wp_ajax_nopriv_gutena_forms_submit', array( Gutena_Forms_Submit_Form_Handler::get_instance(), 'handle_submit' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ), 1000 );
			add_filter( 'gutena_forms__register_fields', array( $this, 'register_fields' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_css_responsive' ) );

			$this->load_dashboard();
		}

		/**
		 * Registring custom action links.
		 *
		 * @since 1.5.0
		 * @param array $prev_links Plugin action links contains (activate, deactivate).
		 *
		 * @return array
		 */
		public function plugin_action_links( $prev_links ) {

			if ( isset( $prev_links['addons'] ) ) {
				unset( $prev_links['addons'] );
			}

			if ( ! is_gutena_forms_pro() ) {
				$new_link = sprintf(
					'<a style="color: #36a78a; font-weight: 600;" target="_blank" href="https://gutenaforms.com/pricing/?utm_source=all_plugins&utm_medium=website&utm_campaign=free_plugin">%s</a>',
					__( 'Get Gutena Forms Pro' )
				);

				// required link in first place.
				array_unshift( $prev_links, $new_link );
			}

			return $prev_links;
		}

		/**
		 * Loading dashbaord
		 *
		 * @since 1.0.0
		 */
		private function load_dashboard() {
			if ( ! class_exists( 'Gutena_Forms_Admin' ) && file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php';
			}
		}

		/**
		 * Registering blocks and scripts
		 *
		 * @since 1.0.0
		 */
		public function register_blocks_and_scripts() {
			if ( ! function_exists( 'register_block_type' ) ) {
				// Block editor is not available.
				return;
			}

			Gutena_Forms_Form_Block::get_instance()->register_block();
			Gutena_Forms_Field_Block::get_instance()->register_block();
			Gutena_Forms_Form_Field_Block::get_instance()->register_block();
			Gutena_Forms_Existing_Forms_Block::get_instance()->register_block();
			Gutena_Forms_Field_Label_Block::get_instance()->register_block();

			// Form Confirmation Message Block.
			register_block_type( __DIR__ . '/build/form-confirm-msg' );

			// Form Error Message Block.
			register_block_type( __DIR__ . '/build/form-error-msg' );

			register_block_type( __DIR__ . '/build/field-group' );

			// google recaptcha.
			$grecaptcha = get_option( 'gutena_forms_grecaptcha', array() );

			// Form messages.
			$gutena_forms_messages = get_option( 'gutena_forms_messages', array() );

			// cloudflare turnstile.
			$cloudflare_turnstile = get_option( 'gutena_forms_cloudflare_turnstile', array() );

			$gutena_forms_messages = empty( $gutena_forms_messages ) ? array() : $gutena_forms_messages;
			$gf_message            = array(
				'required_msg'        => __( 'Please fill in this field', 'gutena-forms' ),
				'required_msg_optin'  => __( 'Please check this checkbox', 'gutena-forms' ),
				'required_msg_select' => __( 'Please select an option', 'gutena-forms' ),
				'required_msg_check'  => __( 'Please check an option', 'gutena-forms' ),
				'invalid_email_msg'   => __( 'Please enter a valid email address', 'gutena-forms' ),
				'min_value_msg'       => __( 'Input value should be greater than', 'gutena-forms' ),
				'max_value_msg'       => __( 'Input value should be less than', 'gutena-forms' ),
			);
			// get saved messages by admin.
			foreach ( $gf_message as $msg_key => $msg_value ) {
				if ( ! empty( $gutena_forms_messages[ $msg_key ] ) ) {
					$gf_message[ $msg_key ] = $gutena_forms_messages[ $msg_key ];
				}
			}

			$gutena_forms_post_type = false;
			$forms_available        = false;

			if ( is_admin() ) {
				if ( isset( $_GET['post_type'] ) && 'gutena_forms' === sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$gutena_forms_post_type = true;
				}

				if ( isset( $_GET['post'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$post_type = get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					if ( 'gutena_forms' === $post_type ) {
						$gutena_forms_post_type = true;
					}
				}

				$gutena_forms_forms = get_posts(
					array(
						'post_type'      => 'gutena_forms',
						'post_status'    => array( 'publish', 'draft', 'private' ),
						'posts_per_page' => 1,
					)
				);

				if ( ! empty( $gutena_forms_forms ) && is_array( $gutena_forms_forms ) ) {
					$forms_available = true;
				}
			}

			wp_localize_script(
				'gutena-forms-script',
				'gutenaFormsBlock',
				array_merge(
					array(
						'submit_action'             => 'gutena_forms_submit',
						'ajax_url'                  => admin_url( 'admin-ajax.php' ),
						'nonce'                     => wp_create_nonce( 'gutena_Forms' ),
						'grecaptcha_type'           => ( empty( $grecaptcha ) || empty( $grecaptcha['type'] ) ) ? '0' : $grecaptcha['type'],
						'grecaptcha_site_key'       => empty( $grecaptcha['site_key'] ) ? '' : $grecaptcha['site_key'],
						'grecaptcha_secret_key'     => ( function_exists( 'is_admin' ) && is_admin() && ! empty( $grecaptcha['secret_key'] ) ) ? $grecaptcha['secret_key'] : '',
						'pricing_link'              => 'https://gutenaforms.com/pricing/',
						'cloudflare_turnstile'      => empty( $cloudflare_turnstile ) ? array() : $cloudflare_turnstile,
						'is_pro'                    => is_gutena_forms_pro(),
						'is_gutena_forms_post_type' => $gutena_forms_post_type,
						'forms_available'           => $forms_available,
					),
					$gf_message
				)
			);
		}

		/**
		 * Registering fields
		 *
		 * @since 1.5.0
		 * @param array $blocks Array of fields.
		 *
		 * @return array
		 */
		public function register_fields( $blocks ) {
			$fields = array(
				'text-field-group'     => array(
					'name'  => 'gutena/text-field-group',
					'type'  => 'text',
					'title' => 'Text Field',
					'dir'   => 'text-field-group',
				),
				'email-field-group'    => array(
					'name'  => 'gutena/email-field-group',
					'type'  => 'email',
					'title' => 'Email Field',
					'dir'   => 'email-field-group',
				),
				'textarea-field-group' => array(
					'name'  => 'gutena/textarea-field-group',
					'type'  => 'textarea',
					'title' => 'Textarea Field',
					'dir'   => 'textarea-field-group',
				),
				'range-field-group'    => array(
					'name'  => 'gutena/range-field-group',
					'type'  => 'range',
					'title' => 'Range Slider Field',
					'dir'   => 'range-field-group',
				),
				'radio-field-group'    => array(
					'name'  => 'gutena/radio-field-group',
					'type'  => 'radio',
					'title' => 'Radio Field',
					'dir'   => 'radio-field-group',
				),
				'checkbox-field-group' => array(
					'name'  => 'gutena/checkbox-field-group',
					'type'  => 'checkbox',
					'title' => 'Checkbox Field',
					'dir'   => 'checkbox-field-group',
				),
				'dropdown-field-group' => array(
					'name'  => 'gutena/dropdown-field-group',
					'type'  => 'select',
					'title' => 'Dropdown Field',
					'dir'   => 'dropdown-field-group',
				),
				'optin-field-group'    => array(
					'name'  => 'gutena/optin-field-group',
					'type'  => 'optin',
					'title' => 'Opt-in Field',
					'dir'   => 'optin-field-group',
				),
				'number-field-group'   => array(
					'name'  => 'gutena/number-field-group',
					'type'  => 'number',
					'title' => 'Number Field',
					'dir'   => 'number-field-group',
				),
			);

			foreach ( $fields as $k => $field ) {
				$blocks[ $k ] = $field;
			}

			return $blocks;
		}

		/**
		 * Admin CSS for responsive table in dashboard
		 *
		 * @since 1.8.0
		 */
		public function admin_css_responsive() {
			wp_enqueue_style( 'admin_responsive', plugin_dir_url( __FILE__ ) . 'assets/css/admin-responsive.css', array(), GUTENA_FORMS_VERSION, 'all' );
			wp_enqueue_script( 'admin_responsive', plugin_dir_url( __FILE__ ) . 'assets/js/custom.js', array( 'jquery' ), GUTENA_FORMS_VERSION, true );
		}

		/**
		 * Registering block styles
		 *
		 * @since 1.0.0
		 */
		public function register_blocks_styles() {
			if ( function_exists( 'register_block_style' ) ) {

				// Range Slider single.
				register_block_style(
					'gutena/form-field',
					array(
						'name'         => 'round-range-slider',
						'label'        => __( 'Border Style', 'gutena-forms' ),
						'is_default'   => false,
						'inline_style' => '.wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field {
							-webkit-appearance: none;
							width: 100%;
							height: 8px;
							border: 1px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 5px;
							background: var(--wp--gutena-forms--input-bg-color,"transparent");
							outline: none;
							-webkit-transition: .2s;
							transition: opacity .2s;
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field:hover{
							border: 1px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							opacity: 1;
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field:focus {
							border: 1px solid var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
						 }
						 .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field::-webkit-slider-thumb {
							-webkit-appearance: none;
							appearance: none;
							width: 20px;
							height: 20px;
							border: 2px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 50%;
							background: var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
							cursor: pointer;
						  }
						  .wp-block-gutena-forms .is-style-round-range-slider .gutena-forms-field.range-field::-moz-range-thumb {
							width: 20px;
							height: 20px;
							border: 2px solid var(--wp--gutena-forms--input-border-color, #D7DBE7);
							border-radius: 50%;
							background: var(--wp--gutena-forms--input-focus-border-color, var(--wp--preset--color--primary, #3F6DE4 ));
							cursor: pointer;
						  }
						',
					)
				);
			}
		}

		/**
		 * Registering block category
		 *
		 * @param array                   $block_categories Array of registered categories.
		 * @param WP_Block_Editor_Context $editor_context Editor context.
		 *
		 * @return array
		 */
		public function register_category( $block_categories, $editor_context ) {
			$fields = wp_list_pluck( $block_categories, 'slug' );

			if ( ! empty( $editor_context->post ) && ! in_array( 'gutena', $fields, true ) ) {
				$block_categories[] = array(
					'slug'  => 'gutena',
					'title' => __( 'Gutena Forms General Fields', 'gutena-forms' ),
				);

				if ( ! is_gutena_forms_pro() ) {
					$block_categories[] = array(
						'slug'  => 'gutena-pro',
						'title' => __( 'Gutena Forms Pro', 'gutena-forms' ),
					);
				}
			}

			return $block_categories;
		}

		/**
		 * Saving gutena forms schema
		 *
		 * @param int|string $post_id Post id.
		 * @param WP_Post    $post Post object.
		 * @param boolean    $update Is update.
		 */
		public function save_gutena_forms_schema( $post_id, $post, $update ) {
			// post should not be a rivision or trash.
			if ( empty( $post_id ) || empty( $post ) || ! function_exists( 'parse_blocks' ) || ! function_exists( 'wp_is_post_revision' ) || wp_is_post_revision( $post_id ) || ! function_exists( 'get_post_status' ) || 'trash' === get_post_status( $post_id ) || 'auto-draft' === get_post_status( $post_id ) || ! has_block( 'gutena/forms', $post ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) ) {
				return;
			}

			// block pattern.
			if ( $update && 'wp_block' === $post->post_type ) {

				$wp_pattern_sync_status = get_post_meta( $post->ID, 'wp_pattern_sync_status', true );
				if ( ! empty( $wp_pattern_sync_status ) && 'unsynced' === $wp_pattern_sync_status ) {
					// unhook this function so it doesn't loop infinitely.
					remove_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10 );
					// correct and save unsynced pattern.
					$this->correct_gutena_forms_pattern( $post );
					// re-hook this function.
					add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
					return;
				}
			}

			// developer.wordpress.org/reference/functions/parse_blocks/.
			$form_schema = $this->get_form_schema( parse_blocks( $post->post_content ) );
			if ( empty( $form_schema ) || ! is_array( $form_schema ) ) {
				return;
			}

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );

			// Save gutena form schema in wp option.
			if ( ! empty( $form_schema['form_schema'] ) && is_array( $form_schema['form_schema'] ) ) {
				$gutena_forms_blocks = explode( '<!-- wp:gutena/forms', $post->post_content );
				foreach ( $form_schema['form_schema'] as  $form_schema ) {
					if ( ! empty( $form_schema['form_attrs']['formID'] ) ) {
						// get block markup.
						foreach ( $gutena_forms_blocks as $gf_block ) {
							if ( false !== stripos( $gf_block, $form_schema['form_attrs']['formID'] ) ) {
								$gf_block                    = explode( 'wp:gutena/forms -->', $gf_block );
								$form_schema['block_markup'] = '<!-- wp:gutena/forms' . $gf_block[0] . 'wp:gutena/forms -->';
								break;
							}
						}
						// filter for formSchema.
						$form_schema_filtered = apply_filters( 'gutena_forms_save_form_schema', $form_schema, $form_schema['form_attrs']['formID'], $gutena_form_ids );
						// Save form schema (prefixed option name prevents arbitrary option overwrite).
						update_option(
							GUTENA_FORMS_SCHEMA_OPTION_PREFIX . sanitize_key( $form_schema['form_attrs']['formID'] ),
							$this->sanitize_array( $form_schema_filtered, true )
						);

						// Save Google reCAPTCHA details.
						if ( ! empty( $form_schema['form_attrs']['recaptcha'] ) && ! empty( $form_schema['form_attrs']['recaptcha']['site_key'] ) && ! empty( $form_schema['form_attrs']['recaptcha']['secret_key'] ) ) {
							update_option(
								'gutena_forms_grecaptcha',
								$this->sanitize_array( $form_schema['form_attrs']['recaptcha'] )
							);
						}

						// cloudflare turnstile.
						if ( ! empty( $form_schema['form_attrs']['cloudflareTurnstile'] ) && ! empty( $form_schema['form_attrs']['cloudflareTurnstile']['site_key'] ) && ! empty( $form_schema['form_attrs']['cloudflareTurnstile']['secret_key'] ) ) {
							update_option(
								'gutena_forms_cloudflare_turnstile',
								$this->sanitize_array( $form_schema['form_attrs']['cloudflareTurnstile'] )
							);
						}

						// Save common form messages.
						if ( ! empty( $form_schema['form_attrs']['messages'] ) && is_array( $form_schema['form_attrs']['messages'] ) ) {
							update_option(
								'gutena_forms_messages',
								$form_schema['form_attrs']['messages']
							);
						}
					}
				}
			}

			// Save gutena form ids in array gutena_form_ids.
			if ( ! empty( $form_schema['form_ids'] ) ) {

				if ( ! empty( $gutena_form_ids ) && is_array( $gutena_form_ids ) ) {
					$gutena_form_ids = array_merge( $gutena_form_ids, $form_schema['form_ids'] );
				} else {
					$gutena_form_ids = $form_schema['form_ids'];
				}
				// unique ids only.
				$gutena_form_ids = array_unique( $gutena_form_ids );

				update_option(
					'gutena_form_ids',
					$this->sanitize_array( $gutena_form_ids )
				);
			}
		}

		/**
		 * Correct unsynced form pattern
		 * remove form id from unsynched pattern so that it can be reuse
		 *
		 * @param  integer $meta_id    ID of the meta data field.
		 * @param  integer $post_id    Post ID.
		 * @param  string  $meta_key    Name of meta field.
		 * @param  string  $meta_value  Value of meta field.
		 */
		public function save_gutena_forms_pattern( $meta_id, $post_id, $meta_key, $meta_value ) {
			// return if post meta is not for unsynced pattern.
			if ( empty( $post_id ) || empty( $meta_key ) || empty( $meta_value ) || 'wp_pattern_sync_status' !== $meta_key || 'unsynced' !== $meta_value ) {
				return;
			}

			$post = get_post( $post_id );
			$this->correct_gutena_forms_pattern( $post );
		}

		/**
		 * Correct and save unsynced pattern
		 *
		 * @param object $post Post object.
		 */
		private function correct_gutena_forms_pattern( $post ) {
			static $func_call = 0;
			// patterns are store under 'wp_block' post type.
			// return if post is empty or not a pattern post_type.
			if ( $func_call > 0 || empty( $post ) || empty( $post->ID ) || 'wp_block' !== $post->post_type || empty( $post->post_content ) || false === stripos( $post->post_content, '{"formID"' ) ) {
				return;
			}

			// get form id.
			$first_extract = '","formName":';
			if ( false === stripos( $post->post_content, $first_extract ) ) {
				$first_extract = '","formClasses":';
			}

			$post_content = explode( $first_extract, $post->post_content );
			$post_content = explode( '{"formID":"gutena_forms_ID_', $post_content[0] );
			$post_content = end( $post_content );
			$form_id      = wp_unslash( $post_content );
			$form_id      = 'gutena_forms_ID_' . $form_id;
			// remove form id.
			$post_content = str_ireplace( $form_id, '', $post->post_content );
			// count function call.
			++$func_call;
			// Update pattern.
			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => $post_content,
				)
			);
		}


		/**
		 * Getting form schema.
		 *
		 * @param array $blocks Array of blocks.
		 * @param int   $form_id form id.
		 *
		 * @return array|false
		 */
		private function get_form_schema( $blocks, $form_id = 0 ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return false;
			}

			$form_schema = array();
			$form_ids    = array();
			$innerblocks = array();

			foreach ( $blocks as $block ) {

				if ( ! empty( $block['blockName'] ) && 'gutena/forms' === $block['blockName'] && ! empty( $block['attrs']['formID'] ) ) {
					$form_id                               = $block['attrs']['formID'];
					$form_ids[]                            = $form_id;
					$form_schema[ $form_id ]['form_attrs'] = $block['attrs'];
				}

				if ( ! empty( $block['blockName'] ) && 'gutena/form-field' === $block['blockName'] && ! empty( $block['attrs']['nameAttr'] ) ) {
					$form_schema[ $form_id ]['form_fields'][ $block['attrs']['nameAttr'] ] = $block['attrs'];
				}

				if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
					$innerblocks = $this->get_form_schema( $block['innerBlocks'], $form_id );
					$form_schema = array_merge_recursive( $form_schema, $innerblocks['form_schema'] );
					$form_ids    = array_merge( $form_ids, $innerblocks['form_ids'] );
				}
			}

			return array(
				'form_ids'    => $form_ids,
				'form_schema' => $form_schema,
			);
		}

		/**
		 * Sanitize data nad array.
		 *
		 * @param array $array_to_sanitize Array to sanitize.
		 * @param bool  $textarea_sanitize Is textarea.
		 *
		 * @return array
		 */
		public function sanitize_array( $array_to_sanitize, $textarea_sanitize = false ) {
			if ( ! empty( $array_to_sanitize ) && is_array( $array_to_sanitize ) ) {
				foreach ( (array) $array_to_sanitize as $key => $value ) {
					if ( is_array( $value ) ) {
						$array_to_sanitize[ $key ] = $this->sanitize_array( $value );
					} elseif ( 'block_markup' === $key && function_exists( 'wp_kses' ) ) {

						$array_to_sanitize[ $key ] = wp_kses(
							$value,
							array_merge(
								wp_kses_allowed_html( 'post' ),
								array(
									'form'  => array(
										'method' => 1,
										'class'  => 1,
										'style'  => 1,
									),
									'input' => array(
										'type'  => 1,
										'name'  => 1,
										'class' => 1,
										'value' => 1,
									),
								)
							)
						);
						// $array[ $key ] = wp_kses_post( $value ); maybe use this if above is too much.
					} else {
						$array_to_sanitize[ $key ] = true === $textarea_sanitize ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
					}
				}
			}
			return $array_to_sanitize;
		}
	}

	Gutena_Forms::get_instance();

endif;
