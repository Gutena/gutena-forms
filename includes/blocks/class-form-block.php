<?php
/**
 * Class Form Block
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Block' ) ) :
	/**
	 * Form Block Class
	 *
	 * @since 1.6.0
	 */
	class Gutena_Forms_Form_Block {
		/**
		 * The single instance of the class.
		 *
		 * @since 1.6.0
		 * @var Gutena_Forms_Form_Block $instance The single instance of the class.
		 */
		private static $instance;

		/**
		 * Register form block
		 *
		 * @since 1.6.0
		 */
		public function register_block() {
			register_block_type(
				GUTENA_FORMS_DIR_PATH . 'build',
				array(
					'render_callback' => array( $this, 'render_block' ),
				)
			);

			$this->localize_block_scripts();
		}

		private function localize_block_scripts() {
			$settings        = apply_filters( 'gutena_forms__settings', array() );
			$integrations    = apply_filters( 'gutena_forms__integrations', array() );
			$settings        = array_merge( $settings, $integrations );
			$allowed_modules = apply_filters( 'gutena_forms__settings_merge',  array( 'honeypot', 'google-recaptcha', 'cloudflare-turnstile', 'validation-messages' ) );
			$field_settings  = array(
				'settings' => array(
					'validation-messages'  => array(),
					'google-recaptcha'     => array(),
					'cloudflare-turnstile' => array(),
					'honeypot'             => array(),
				),
			);

			foreach ( $settings as $module_key => $module ) {
				if ( in_array( $module_key, $allowed_modules, true ) ) {
					$class = new $module();

					if ( $class instanceof Gutena_Forms_Forms_Settings ) {
						$field_settings['settings'][ $module_key ] = $class->settings;
					}
				}
			}

			// Add integrations for Pro (used for global display in editor).
			if ( in_array( 'activecampaign', $allowed_modules, true ) || in_array( 'brevo', $allowed_modules, true ) || in_array( 'mailchimp', $allowed_modules, true ) ) {
				$field_settings['settings']['integrations'] = get_option( 'gutena_forms__integration_settings', array() );
			}

			wp_localize_script(
				'gutena-forms-editor-script',
				'gutenaFormsFormFieldsSettings',
				$field_settings
			);
		}

		/**
		 * Check if block has a form-level override for the given attribute.
		 *
		 * @since 1.8.0
		 * @param array  $block_attrs Block attributes.
		 * @param string $key         Attribute key (e.g. 'recaptcha', 'cloudflareTurnstile').
		 * @return bool True if block has a non-empty override.
		 */
		private function has_form_override( $block_attrs, $key ) {
			if ( empty( $block_attrs ) || ! isset( $block_attrs[ $key ] ) ) {
				return false;
			}
			$val = $block_attrs[ $key ];
			if ( null === $val || array() === $val ) {
				return false;
			}
			if ( ! is_array( $val ) ) {
				return '' !== $val;
			}
			// For objects: consider it override if any meaningful key has a value.
			$meaningful_keys = array(
				'recaptcha'           => array( 'enable', 'site_key', 'type' ),
				'cloudflareTurnstile' => array( 'enable', 'site_key' ),
				'honeypot'            => array( 'enable', 'timeCheckValue' ),
				'messages'            => array( 'required_msg', 'required_msg_select', 'required_msg_check', 'required_msg_optin', 'invalid_email_msg', 'min_value_msg', 'max_value_msg' ),
			);
			$check = isset( $meaningful_keys[ $key ] ) ? $meaningful_keys[ $key ] : array_keys( $val );
			foreach ( $check as $k ) {
				if ( isset( $val[ $k ] ) && '' !== $val[ $k ] && null !== $val[ $k ] ) {
					return true;
				}
			}
			return false;
		}

		/**
		 * Merge block attributes with global settings. Block override takes precedence when present.
		 *
		 * @since 1.8.0
		 * @param array $block_attrs Block attributes.
		 * @return array Effective form attributes (block + global merged).
		 */
		public function get_effective_form_attrs( $block_attrs ) {
			if ( ! is_array( $block_attrs ) ) {
				$block_attrs = array();
			}
			$global = array(
				'recaptcha'           => get_option( 'gutena_forms__recaptcha', array() ),
				'cloudflareTurnstile' => get_option( 'gutena_forms__cloudflare', array() ),
				'honeypot'            => get_option( 'gutena_forms__honeypot', array() ),
				'messages'            => get_option( 'gutena_forms__form_validation_messages', array() ),
			);
			$effective = $block_attrs;
			if ( ! $this->has_form_override( $block_attrs, 'recaptcha' ) ) {
				$effective['recaptcha'] = wp_parse_args( (array) ( $block_attrs['recaptcha'] ?? array() ), (array) $global['recaptcha'] );
			}
			if ( ! $this->has_form_override( $block_attrs, 'cloudflareTurnstile' ) ) {
				$effective['cloudflareTurnstile'] = wp_parse_args( (array) ( $block_attrs['cloudflareTurnstile'] ?? array() ), (array) $global['cloudflareTurnstile'] );
			}
			if ( ! $this->has_form_override( $block_attrs, 'honeypot' ) ) {
				$effective['honeypot'] = wp_parse_args( (array) ( $block_attrs['honeypot'] ?? array() ), (array) $global['honeypot'] );
			}
			if ( ! $this->has_form_override( $block_attrs, 'messages' ) ) {
				$effective['messages'] = wp_parse_args( (array) ( $block_attrs['messages'] ?? array() ), (array) $global['messages'] );
			}
			$allowed = apply_filters( 'gutena_forms__settings_merge', array( 'honeypot', 'google-recaptcha', 'cloudflare-turnstile', 'validation-messages' ) );
			$has_integrations = in_array( 'activecampaign', $allowed, true ) || in_array( 'brevo', $allowed, true ) || in_array( 'mailchimp', $allowed, true );
			if ( $has_integrations ) {
				$global_integration = get_option( 'gutena_forms__integration_settings', array() );
				$block_settings     = $block_attrs['settings'] ?? array();
				$block_integration  = $block_settings['integration'] ?? array();
				$has_integration_override = ! empty( $block_integration ) && is_array( $block_integration );
				foreach ( array( 'mailchimp', 'activecampaign', 'brevo' ) as $crm ) {
					if ( $has_integration_override && ! empty( $block_integration[ $crm ] ) ) {
						continue;
					}
					if ( isset( $global_integration[ $crm ] ) ) {
						$effective['settings']             = isset( $effective['settings'] ) ? $effective['settings'] : array();
						$effective['settings']['integration'] = isset( $effective['settings']['integration'] ) ? $effective['settings']['integration'] : array();
						$effective['settings']['integration'][ $crm ] = wp_parse_args(
							(array) ( $effective['settings']['integration'][ $crm ] ?? array() ),
							(array) $global_integration[ $crm ]
						);
					}
				}
			}
			return apply_filters( 'gutena_forms_effective_form_attrs', $effective, $block_attrs );
		}

		/**
		 * Render Form Block
		 *
		 * @since 1.6.0
		 * @param array    $attributes Block attributes.
		 * @param string   $content Block content.
		 * @param WP_Block $block Block.
		 *
		 * @return string
		 */
		public function render_block( $attributes, $content, $block ) {
			// No changes if attributes is empty
			if ( empty( $attributes ) || empty( $attributes['adminEmails'] ) ) {
				return $content;
			}

			$attributes = $this->get_effective_form_attrs( $attributes );

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
			}

			//google recaptcha
			$recaptcha_html = '';
			if ( ! empty( $attributes['recaptcha'] ) && ! empty( $attributes['recaptcha']['enable'] ) && ! empty( $attributes['recaptcha']['site_key'] ) && ! empty( $attributes['recaptcha']['type'] ) ) {
				$effective_recaptcha = $attributes['recaptcha'];
				add_action(
					'wp_enqueue_scripts',
					function () use ( $effective_recaptcha ) {
						$this->enqueue_grecaptcha_scripts_with_settings( $effective_recaptcha );
					}
				);

				//input box for v2 type only
				if ( 'v2' === $attributes['recaptcha']['type'] ){
					$recaptcha_html = '<div class="g-recaptcha" data-sitekey="' . esc_attr( $attributes['recaptcha']['site_key'] ) . '"></div><br>';
				}

				//input field to check if recaptcha or not
				$html .= '<input type="hidden" name="recaptcha_enable" value="' . esc_attr( $attributes['recaptcha']['enable'] ) . '" />';
			}

			//cloudflare turnstile
			$turnstile_html = '';
			if ( ! empty( $attributes['cloudflareTurnstile'] ) && ! empty( $attributes['cloudflareTurnstile']['enable'] ) && ! empty( $attributes['cloudflareTurnstile']['site_key'] ) ) {
				$effective_turnstile = $attributes['cloudflareTurnstile'];
				add_action(
					'wp_enqueue_scripts',
					function () use ( $effective_turnstile ) {
						$this->enqueue_cloudflare_turnstile_scripts_with_settings( $effective_turnstile );
					}
				);

				$turnstile_html .= '<div
					class="cf-turnstile"
					data-sitekey="' . esc_attr( $attributes['cloudflareTurnstile']['site_key'] ) . '"
					data-theme="light"
					data-size="normal"
					data-callback="onSuccess"
				></div><br />';

				$html .= '<input type="hidden" name="turnstile_enable" value="' . esc_attr( $attributes['cloudflareTurnstile']['enable'] ) . '" />';
			}

			$honeypot_html = '';
			if ( ! empty( $attributes['honeypot'] ) && ! empty( $attributes['honeypot']['enable'] ) ) {
				$time_check_value = ! empty( $attributes['honeypot']['timeCheckValue'] )
					? intval( $attributes['honeypot']['timeCheckValue'] )
					: 4; // default 4 seconds
				// we need to different hidden fields 1 for checking time second for check honeypot
				$honeypot_html .= '<div style="display:none;">
					<label for="gf_hp_' . esc_attr( $attributes['formID'] ) . '">' . esc_html__( 'Leave this field empty', 'gutena-forms' ) . '</label>
					<input type="text" name="gf_hp_' . esc_attr( $attributes['formID'] ) . '" id="gf_hp_' . esc_attr( $attributes['formID'] ) . '" value="" />
					<input type="hidden" name="gf_time_check_' . esc_attr( $attributes['formID'] ) . '" value="' . esc_attr( time() + $time_check_value ) . '" />
				</div>';
			}

			// Add required html
			if ( ! empty( $html ) ) {
				$content = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					'>'.$html,
					$content,
					1
				);
			}

			//Submit Button HTML markup : change link to button tag
			$content = $this->str_last_replace(
				'<a',
				$recaptcha_html . $turnstile_html . $honeypot_html . '<button',
				$content
			);

			$content = $this->str_last_replace(
				'</a>',
				'</button>',
				$content
			);
			//filter content
			$content = apply_filters( 'gutena_forms_render_form', $content, $attributes );
			// Enqueue block styles
			$this->enqueue_block_styles( $attributes['formStyle'] );

			return $content;
		}

		/**
		 * String last replace.
		 *
		 * @since 1.0.0
		 * @param string $search String to search.
		 * @param string $replace string to replace.
		 * @param string $str the subject string.
		 *
		 * @return string
		 */
		public function str_last_replace( $search, $replace, $str ) {
			//finds the position of the last occurrence of a string
			$pos = strripos($str, $search);

			if ( $pos !== false ) {
				$str = substr_replace($str, $replace, $pos, strlen($search));
			}

			return $str;
		}

		/**
		 * Enqueue Block Styles
		 *
		 * @since 1.0.0
		 * @param string $style The styling string.
		 * @param int    $priority The priority for action hook.
		 */
		public function enqueue_block_styles( $style, $priority = 10 ) {

			if ( empty( $style ) || ! function_exists( 'wp_strip_all_tags' ) ) {
				return;
			}

			$action_hook_name = 'wp_footer';
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				$action_hook_name = 'wp_head';
			}
			add_action(
				$action_hook_name,
				static function () use ( $style ) {
					if ( str_contains( $style, 'u002' ) ) {
						$style = str_replace(
							array( 'u002d', 'u0022', 'u003e' ), array( '-', '"', '>' ), $style
						);
					}

					$new_style = wp_strip_all_tags( $style );
					echo '<style>' . $new_style . "</style>\n";
				},
				$priority
			);
		}

		/**
		 * Enqueue Google Recaptcha Scripts
		 *
		 * @since 1.0.0
		 */
		public function enqueue_grecaptcha_scripts() {
			$grecaptcha = get_option( 'gutena_forms__recaptcha', false );
			$this->enqueue_grecaptcha_scripts_with_settings( is_array( $grecaptcha ) ? $grecaptcha : array() );
		}

		/**
		 * Enqueue Google Recaptcha Scripts with provided settings.
		 *
		 * @since 1.8.0
		 * @param array $grecaptcha Recaptcha settings (site_key, type, etc.).
		 */
		public function enqueue_grecaptcha_scripts_with_settings( $grecaptcha ) {
			static $recaptcha_start = 0;
			if ( 0 === $recaptcha_start ) {
				if ( ! empty( $grecaptcha ) && ! empty( $grecaptcha['site_key'] ) && ! empty( $grecaptcha['type'] ) ) {
					wp_enqueue_script(
						'google-recaptcha',
						esc_url( 'https://www.google.com/recaptcha/api.js' . ( ( 'v2' === $grecaptcha['type'] ) ? '' : '?render=' . esc_attr( $grecaptcha['site_key'] ) ) ),
						array(),
						GUTENA_FORMS_VERSION,
						false
					);
				}
				++$recaptcha_start;
			}
		}

		/**
		 * Enqueue Cloudflare Turnstile scripts
		 *
		 * @since 1.3.0
		 */
		public function enqueue_cloudflare_turnstile_scripts() {
			$cloudflare_turnstile = get_option( 'gutena_forms__cloudflare', false );
			$this->enqueue_cloudflare_turnstile_scripts_with_settings( is_array( $cloudflare_turnstile ) ? $cloudflare_turnstile : array() );
		}

		/**
		 * Enqueue Cloudflare Turnstile scripts with provided settings.
		 *
		 * @since 1.8.0
		 * @param array $cloudflare_turnstile Turnstile settings (site_key, etc.).
		 */
		public function enqueue_cloudflare_turnstile_scripts_with_settings( $cloudflare_turnstile ) {
			static $turnstile_start = 0;
			if ( 0 === $turnstile_start ) {
				if ( ! empty( $cloudflare_turnstile ) && ! empty( $cloudflare_turnstile['site_key'] ) ) {
					wp_enqueue_script(
						'cloudflare-turnstile',
						esc_url( 'https://challenges.cloudflare.com/turnstile/v0/api.js' ),
						array(),
						null,
						array(
							'defer' => true,
							'async' => true,
						)
					);
					++$turnstile_start;
				}
			}
		}

		/**
		 * Get Instance
		 *
		 * @since 1.6.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}
	}
endif;
