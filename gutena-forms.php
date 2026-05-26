<?php
/**
 * Plugin Name:       Gutena Forms - Contact Forms Block
 * Description:       Gutena Forms is the easiest way to create forms inside the WordPress block editor. Our plugin does not use jQuery and is lightweight, so you can rest assured that it won’t slow down your website. Instead, it allows you to quickly and easily create custom forms right inside the block editor.
 * Requires at least: 6.5
 * Requires PHP:      5.6
 * Version:           1.9.0
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
	define( 'GUTENA_FORMS_VERSION', '1.9.0' );
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
	 * Has gutena forms pro
	 *
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
	 * Gutena Forms main class.
	 */
	class Gutena_Forms {

		/**
		 * The instance of this class
		 *
		 * @var Gutena_Forms $instance The instance of this class.
		 */
		private static $instance = null;

		/**
		 * Returns the instance of this class.
		 *
		 * @return Gutena_Forms
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Gutena Forms constructor
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
			include_once GUTENA_FORMS_DIR_PATH . 'includes/helpers/class-gutena-forms-helper.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-cpt.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/email-report/email-reports.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-migration.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/class-gutena-dummy-fields.php';

			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-form-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-field-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-form-field-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-existing-forms-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-field-label-block.php';
			include_once GUTENA_FORMS_DIR_PATH . 'includes/blocks/class-fields.php';

			include_once GUTENA_FORMS_DIR_PATH . 'includes/handlers/class-handle-save-form.php';
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
			add_action( 'template_redirect', array( $this, 'enqueue_recaptcha_scripts' ) );
			add_filter( 'block_categories_all', array( $this, 'register_category' ), 10, 2 );
			add_action( 'save_post', array( $this, 'save_gutena_forms_schema' ), 10, 3 );
			add_action( 'added_post_meta', array( $this, 'save_gutena_forms_pattern' ), 10, 4 );
			add_action( 'wp_ajax_gutena_forms_submit', array( Gutena_Forms_Submit_Form_Handler::get_instance(), 'handle_submit' ) );
			add_action( 'wp_ajax_nopriv_gutena_forms_submit', array( Gutena_Forms_Submit_Form_Handler::get_instance(), 'handle_submit' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ), 1000 );
			add_filter( 'gutena_forms__register_fields', array( $this, 'register_fields' ) );

			$this->load_dashboard();
		}

		/**
		 * Enqueue scripts
		 */
		public function enqueue_recaptcha_scripts() {
			global $post;
			$has_direct_form_block    = ! empty( $post->post_content ) && function_exists( 'has_block' ) && has_block( 'gutena/forms', $post->post_content );
			$has_existing_forms_block = ! empty( $post->post_content ) && function_exists( 'has_block' ) && has_block( 'gutena/existing-forms', $post->post_content );

			/**
			 * Enqueue external security scripts by scanning a block list.
			 * This is needed because `gutena/existing-forms` injects forms via do_blocks(),
			 * so the page content itself may not contain the nested `gutena/forms` blocks.
			 *
			 * @param array $blocks Parsed blocks.
			 * @param int   $i      Counter for unique v3 handles.
			 * @return int Updated counter.
			 */
			$scan_blocks_for_security = function ( $blocks, $i ) use ( &$scan_blocks_for_security ) {
				if ( empty( $blocks ) || ! is_array( $blocks ) ) {
					return $i;
				}

				foreach ( $blocks as $block ) {
					if ( empty( $block['blockName'] ) ) {
						continue;
					}

					// Direct forms block: can enqueue scripts from its attrs.
					if ( 'gutena/forms' === $block['blockName'] ) {
						$attributes = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();

						if ( ! isset( $attributes['recaptcha']['defaultSettings'] ) || false !== $attributes['recaptcha']['defaultSettings'] ) {
							$recaptcha_settings = Gutena_Forms_ReCAPTCHA::resolve_settings( get_option( 'gutena_forms__recaptcha', array() ) );
						} elseif ( isset( $attributes['recaptcha']['enable'] ) && $attributes['recaptcha']['enable'] ) {
								$recaptcha_settings = Gutena_Forms_ReCAPTCHA::resolve_settings( $attributes['recaptcha'] );
						}

						if ( isset( $recaptcha_settings['enable'] ) && $recaptcha_settings['enable'] ) {

							if ( 'v2' === $recaptcha_settings['type'] ) {
								wp_enqueue_script(
									'gutena-forms-recaptcha-v2-scripts',
									'https://www.google.com/recaptcha/api.js',
									array(),
									null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
									false
								);
							} elseif ( 'v3' === $recaptcha_settings['type'] ) {
								if ( isset( $recaptcha_settings['defaultSettings'] ) && $recaptcha_settings['defaultSettings'] ) {
									wp_enqueue_script(
										'gutena-forms-recaptcha-v3-scripts',
										'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $recaptcha_settings['site_key'] ),
										array(),
										null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
										false
									);
								} else {

									wp_enqueue_script(
										'gutena-forms-recaptcha-v3-' . esc_attr( $i ) . '-scripts',
										'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $recaptcha_settings['site_key'] ),
										array(),
										null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
										false
									);

									++$i;
								}
							}
						}

						$cloudflare_attr = ( isset( $attributes['cloudflareTurnstile'] ) && is_array( $attributes['cloudflareTurnstile'] ) ) ? $attributes['cloudflareTurnstile'] : array();
						if ( ! isset( $cloudflare_attr['defaultSettings'] ) || false !== $cloudflare_attr['defaultSettings'] ) {
							$cloudflare_settings = get_option( 'gutena_forms__cloudflare', array() );
						} else {
							$cloudflare_settings = $cloudflare_attr;
						}

						if ( isset( $cloudflare_settings['enable'] ) && $cloudflare_settings['enable'] ) {
							wp_enqueue_script(
								'gutena-forms-cloudflare-turnstile-scripts',
								'https://challenges.cloudflare.com/turnstile/v0/api.js',
								array(),
								null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
								false
							);
						}

						// Continue into inner blocks if any.
						if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
							$i = $scan_blocks_for_security( $block['innerBlocks'], $i );
						}
						continue;
					}

					// Existing forms block: fetch the referenced form post and scan its blocks too.
					if ( 'gutena/existing-forms' === $block['blockName'] ) {
						$attrs   = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
						$post_id = isset( $attrs['formID'] ) ? intval( $attrs['formID'] ) : 0;
						if ( $post_id > 0 ) {
							$form_post = get_post( $post_id );
							if ( $form_post && ! empty( $form_post->post_content ) ) {
								$i = $scan_blocks_for_security( parse_blocks( $form_post->post_content ), $i );
							}
						}
					}

					// Recurse into inner blocks for any other container blocks.
					if ( ! empty( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
						$i = $scan_blocks_for_security( $block['innerBlocks'], $i );
					}
				}

				return $i;
			};

			// Scan page content for both direct forms + existing forms references.
			if ( ! empty( $post->post_content ) && function_exists( 'parse_blocks' ) ) {
				$scan_blocks_for_security( parse_blocks( $post->post_content ), 0 );
			}
		}

		/**
		 * Modify plugin action links
		 *
		 * @param array $prev_links Plugin action links.
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
		 * Load form dashboard
		 */
		private function load_dashboard() {
			if ( ! class_exists( 'Gutena_Forms_Admin' ) && file_exists( GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php' ) ) {
				require_once GUTENA_FORMS_DIR_PATH . 'includes/admin/class-admin.php';
			}
		}

		/**
		 * Register blocks and scripts
		 */
		public function register_blocks_and_scripts() {
			if ( ! function_exists( 'register_block_type' ) ) {
				// Block editor is not available.
				return;
			}

			Gutena_Forms_Form_Block::get_instance()->register_block();
			Gutena_Forms_Existing_Forms_Block::get_instance()->register_block();
			Gutena_Forms_Fields::get_instance()->register_blocks();
			
			
			Gutena_Forms_Field_Block::get_instance()->register_block( 'backward compatibility' );
			Gutena_Forms_Form_Field_Block::get_instance()->register_block( 'backward compatibility' );
			Gutena_Forms_Field_Label_Block::get_instance()->register_block( 'backward compatibility' );

			// Form Confirmation Message Block.
			register_block_type( __DIR__ . '/build/blocks/form-confirm-msg' );

			// Form Error Message Block.
			register_block_type( __DIR__ . '/build/blocks/form-error-msg' );

			// google recaptcha.
			$grecaptcha = get_option( 'gutena_forms__recaptcha', array() );

			// Form messages.
			$gutena_forms_messages = get_option( 'gutena_forms__form_validation_messages', array() );

			// cloudflare turnstile: global default settings (option gutena_forms__cloudflare).
			$cloudflare_turnstile_defaults = get_option( 'gutena_forms__cloudflare', array() );

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
						'submit_action'                 => 'gutena_forms_submit',
						'ajax_url'                      => admin_url( 'admin-ajax.php' ),
						'nonce'                         => wp_create_nonce( 'gutena_Forms' ),
						'grecaptcha'                    => ! empty( $grecaptcha )
							? $grecaptcha
							: array(
								'enable' => false,
								'type'   => 'v2',
							),
						'pricing_link'                  => 'https://gutenaforms.com/pricing/',
						'cloudflare_turnstile_defaults' => is_array( $cloudflare_turnstile_defaults ) ? $cloudflare_turnstile_defaults : array(),
						'is_pro'                        => is_gutena_forms_pro(),
						'is_gutena_forms_post_type'     => $gutena_forms_post_type,
						'forms_available'               => $forms_available,
						'honeypot'                      => get_option( 'gutena_forms__honeypot', array() ),
					),
					$gf_message
				)
			);
		}

		/**
		 * Register fields
		 *
		 * @since 1.2.0
		 * @param array $blocks Registered fields.
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
		 * Register block styles
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
		 * Register Gutena category if not exists
		 *
		 * @param array                   $block_categories Block categories.
		 * @param WP_Block_Editor_Context $editor_context Block editor context.
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
		 * Save form schema
		 *
		 * @param int     $post_id Post id.
		 * @param WP_Post $post Post object.
		 * @param boolean $update is save or update.
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

			/**
			 * Function docs link
			 *
			 * @link https://developer.wordpress.org/reference/functions/parse_blocks/
			 */
			$form_schema = $this->get_form_schema( parse_blocks( $post->post_content ) );
			if ( empty( $form_schema ) || ! is_array( $form_schema ) ) {
				return;
			}

			$gutena_form_ids = get_option( 'gutena_form_ids', array() );

			// Save gutena form schema in wp option.
			if ( ! empty( $form_schema['form_schema'] ) && is_array( $form_schema['form_schema'] ) ) {
				$gutena_forms_blocks = explode( '<!-- wp:gutena/forms', $post->post_content );
				foreach ( $form_schema['form_schema'] as $single_form_schema ) {
					if ( ! empty( $single_form_schema['form_attrs']['formID'] ) ) {
						// get block markup.
						foreach ( $gutena_forms_blocks as $gf_block ) {
							if ( false !== stripos( $gf_block, $single_form_schema['form_attrs']['formID'] ) ) {
								$gf_block                           = explode( 'wp:gutena/forms -->', $gf_block );
								$single_form_schema['block_markup'] = '<!-- wp:gutena/forms' . $gf_block[0] . 'wp:gutena/forms -->';
								break;
							}
						}

						// Normalize validation messages: when the form uses default messages
						// (defaultSettings is true or missing), persist a complete messages object
						// derived from the global option (or built-in fallbacks). This ensures
						// every form's individual settings are mapped to the current globals,
						// even when the user has not edited any message field.
						$global_messages = get_option( 'gutena_forms__form_validation_messages', array() );
						$base_messages   = array(
							'required_msg'        => __( 'Please fill in this field', 'gutena-forms' ),
							'required_msg_optin'  => __( 'Please check this checkbox', 'gutena-forms' ),
							'required_msg_select' => __( 'Please select an option', 'gutena-forms' ),
							'required_msg_check'  => __( 'Please check an option', 'gutena-forms' ),
							'invalid_email_msg'   => __( 'Please enter a valid email address', 'gutena-forms' ),
							'min_value_msg'       => __( 'Input value should be greater than', 'gutena-forms' ),
							'max_value_msg'       => __( 'Input value should be less than', 'gutena-forms' ),
						);
						$effective_global = array_merge(
							$base_messages,
							is_array( $global_messages ) ? $global_messages : array()
						);

						$current_messages    = isset( $single_form_schema['form_attrs']['messages'] ) && is_array( $single_form_schema['form_attrs']['messages'] )
							? $single_form_schema['form_attrs']['messages']
							: array();
						$is_default_messages = empty( $current_messages )
							|| ! isset( $current_messages['defaultSettings'] )
							|| false !== $current_messages['defaultSettings'];

						if ( $is_default_messages ) {
							$single_form_schema['form_attrs']['messages'] = array_merge(
								$effective_global,
								array( 'defaultSettings' => true )
							);
						}

						// filter for formSchema.
						$form_schema_filtered = apply_filters( 'gutena_forms_save_form_schema', $single_form_schema, $single_form_schema['form_attrs']['formID'], $gutena_form_ids );
						// Save form schema (prefixed option name prevents arbitrary option overwrite).
						update_option(
							GUTENA_FORMS_SCHEMA_OPTION_PREFIX . sanitize_key( $single_form_schema['form_attrs']['formID'] ),
							Gutena_Forms_Helper::sanitize_array( $form_schema_filtered, true )
						);

						// Global reCAPTCHA is set only from global settings UI, not from form block save.
						// Cloudflare Turnstile: verification uses form schema + gutena_forms__cloudflare (global defaults).

						// Save common form messages (Primary Form Sync Logic).
						if ( ! empty( $single_form_schema['form_attrs']['messages'] ) && is_array( $single_form_schema['form_attrs']['messages'] ) ) {
							$all_form_ids    = get_option( 'gutena_form_ids', array() );
							$current_form_id = $single_form_schema['form_attrs']['formID'];
							// Only inherited/default messages may update the global option; local overrides stay on this form only.
							// If no forms exist yet, or only this form exists, it is the Primary Form.
							if ( $is_default_messages && ( empty( $all_form_ids ) || ( count( $all_form_ids ) === 1 && in_array( $current_form_id, $all_form_ids, true ) ) ) ) {
								$messages_for_global = json_decode( wp_json_encode( $single_form_schema['form_attrs']['messages'] ), true );
								if ( ! is_array( $messages_for_global ) ) {
									$messages_for_global = is_array( $single_form_schema['form_attrs']['messages'] ) ? $single_form_schema['form_attrs']['messages'] : array();
								}
								if ( class_exists( 'Gutena_Forms_Settings_Migrator' ) ) {
									$messages_for_global = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $messages_for_global );
								} elseif ( is_array( $messages_for_global ) && isset( $messages_for_global['defaultSettings'] ) ) {
									unset( $messages_for_global['defaultSettings'] );
								}

								update_option(
									'gutena_forms__form_validation_messages',
									$messages_for_global
								);
							}
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
					Gutena_Forms_Helper::sanitize_array( $gutena_form_ids )
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
		 * Get Form schema from block parsing
		 *
		 * @param array  $blocks Array of blocks.
		 * @param string $form_id Form id.
		 *
		 * @return array
		 */
		private function get_form_schema( $blocks, $form_id = 0 ) {
			if ( empty( $blocks ) || ! is_array( $blocks ) ) {
				return array();
			}

			$form_schema = array();
			$form_ids    = array();

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
	}

	Gutena_Forms::get_instance();
endif;
