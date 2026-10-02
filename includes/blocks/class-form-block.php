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
		 * Resolve reCAPTCHA settings with hierarchical key lookup.
		 *
		 * @since 1.6.0
		 * @param array $settings Raw settings.
		 * @return array
		 */
		private static function resolve_recaptcha_settings( $settings ) {
			if ( class_exists( 'Gutena_Forms_ReCAPTCHA' ) ) {
				return Gutena_Forms_ReCAPTCHA::resolve_settings( $settings );
			}

			$settings = is_array( $settings ) ? $settings : array();
			$type     = ( ! empty( $settings['type'] ) && in_array( $settings['type'], array( 'v2', 'v3' ), true ) ) ? $settings['type'] : 'v2';

			return array(
				'enable'         => ! empty( $settings['enable'] ),
				'type'           => $type,
				'site_key'       => ! empty( $settings[ $type . '_site_key' ] ) ? $settings[ $type . '_site_key' ] : ( isset( $settings['site_key'] ) ? $settings['site_key'] : '' ),
				'secret_key'     => ! empty( $settings[ $type . '_secret_key' ] ) ? $settings[ $type . '_secret_key' ] : ( isset( $settings['secret_key'] ) ? $settings['secret_key'] : '' ),
				'thresholdScore' => isset( $settings['thresholdScore'] ) ? floatval( $settings['thresholdScore'] ) : 0.5,
			);
		}

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
				GUTENA_FORMS_DIR_PATH . 'build/blocks/form',
				array(
					'render_callback' => array( $this, 'render_block' ),
				)
			);
		}

		/**
		 * Get effective reCAPTCHA settings (global default or form override).
		 *
		 * @since 1.6.0
		 * @param array $block_recaptcha Block recaptcha attributes.
		 * @return array|null Effective recaptcha settings (type, site_key, secret_key, thresholdScore) or null if disabled/empty.
		 */
		public static function get_effective_recaptcha( $block_recaptcha ) {
			if ( empty( $block_recaptcha ) || empty( $block_recaptcha['enable'] ) ) {
				return null;
			}

			$global     = get_option( 'gutena_forms__recaptcha', false );
			$use_global = (
				( ! isset( $block_recaptcha['defaultSettings'] ) || false !== $block_recaptcha['defaultSettings'] )
				|| ( ( empty( $block_recaptcha['site_key'] ) || empty( $block_recaptcha['secret_key'] ) ) && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) )
			);

			if ( $use_global && ! empty( $global ) ) {
				$resolved = self::resolve_recaptcha_settings( $global );
				if ( ! empty( $resolved['site_key'] ) && ! empty( $resolved['secret_key'] ) && ! empty( $resolved['type'] ) ) {
					return $resolved;
				}
			}

			$resolved = self::resolve_recaptcha_settings( $block_recaptcha );
			if ( ! empty( $resolved['site_key'] ) && ! empty( $resolved['secret_key'] ) && ! empty( $resolved['type'] ) ) {
				return $resolved;
			}

			return null;
		}

		/**
		 * Get effective Cloudflare Turnstile settings (global default or form override).
		 *
		 * @since 1.8.0
		 * @param array $block_turnstile Block cloudflareTurnstile attributes.
		 * @return array|null Effective turnstile settings (enable, site_key, secret_key) or null if disabled/empty.
		 */
		public static function get_effective_turnstile( $block_turnstile ) {
			if ( empty( $block_turnstile ) || empty( $block_turnstile['enable'] ) ) {
				return null;
			}
			$global     = get_option( 'gutena_forms__cloudflare', array() );
			$use_global = (
				( ! isset( $block_turnstile['defaultSettings'] ) || false !== $block_turnstile['defaultSettings'] )
				|| ( ( empty( $block_turnstile['site_key'] ) || empty( $block_turnstile['secret_key'] ) ) && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) )
			);
			if ( $use_global && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) ) {
				return array_merge(
					array(
						'enable'     => true,
						'site_key'   => '',
						'secret_key' => '',
					),
					$global
				);
			}
			if ( ! empty( $block_turnstile['site_key'] ) && ! empty( $block_turnstile['secret_key'] ) ) {
				return array_merge( array( 'enable' => true ), $block_turnstile );
			}
			return null;
		}

		/**
		 * Convert the outer wrapper from form to div when an embedded form is present.
		 * Prevents invalid nested forms (e.g. existing-forms block inside gutena/forms).
		 *
		 * @since 1.6.0
		 * @param string $content Block HTML.
		 * @return string
		 */
		private function demote_nested_form_wrapper( $content ) {
			if ( ! preg_match_all( '/<form\b/i', $content, $matches ) || count( $matches[0] ) < 2 ) {
				return $content;
			}

			$content = preg_replace( '/<form\b/i', '<div', $content, 1 );
			$pos     = strripos( $content, '</form>' );

			if ( false !== $pos ) {
				$content = substr_replace( $content, '</div>', $pos, 7 );
			}

			return $content;
		}

		/**
		 * Inject a data attribute into the opening form tag.
		 *
		 * @since 1.6.0
		 * @param string $content            Block HTML.
		 * @param string $attribute_fragment Attribute string (leading space required).
		 * @return string
		 */
		private function inject_form_opening_attribute( $content, $attribute_fragment ) {
			return preg_replace(
				'/<form\b([^>]*)>/i',
				'<form$1' . $attribute_fragment . '>',
				$content,
				1
			);
		}

		/**
		 * Inject HTML immediately after the opening form tag.
		 *
		 * @since 2.3.0
		 * @param string $content       Block HTML.
		 * @param string $html_fragment HTML to insert after <form>.
		 * @return string
		 */
		private function inject_after_form_opening( $content, $html_fragment ) {
			if ( empty( $html_fragment ) ) {
				return $content;
			}

			return preg_replace(
				'/(<form\b[^>]*>)/i',
				'$1' . $html_fragment,
				$content,
				1
			);
		}

		/**
		 * Form block CSS class derived from the form name.
		 *
		 * @param string $form_name Form name attribute.
		 * @return string
		 */
		private static function get_form_name_class( $form_name ) {
			$name = is_string( $form_name ) && '' !== trim( $form_name ) ? $form_name : 'Contact Form';

			return 'gutena-forms-' . strtolower( preg_replace( '/\s+/', '-', $name ) );
		}

		/**
		 * Whether a style attribute value is empty.
		 *
		 * @param mixed $value Raw value.
		 * @return bool
		 */
		private static function is_empty_style_value( $value ) {
			return null === $value || '' === $value;
		}

		/**
		 * Build per-form CSS variables from block attributes (editor parity fallback).
		 *
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		public static function build_form_style_from_attributes( $attributes ) {
			$attributes = is_array( $attributes ) ? $attributes : array();

			$form_class        = self::get_form_name_class( $attributes['formName'] ?? '' );
			$label_typography  = isset( $attributes['labelTypography'] ) && is_array( $attributes['labelTypography'] ) ? $attributes['labelTypography'] : array();
			$placeholder_typo  = isset( $attributes['placeholderTypography'] ) && is_array( $attributes['placeholderTypography'] ) ? $attributes['placeholderTypography'] : array();
			$style_spacing     = isset( $attributes['style']['spacing'] ) && is_array( $attributes['style']['spacing'] ) ? $attributes['style']['spacing'] : array();
			$input_label_gap   = $attributes['inputLabelGap'] ?? '10px';
			$block_gap         = $style_spacing['blockGap'] ?? '2em';

			$vars = array();

			if ( ! self::is_empty_style_value( $attributes['labelColor'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--label-color:' . $attributes['labelColor'] . ';';
			}
			if ( ! self::is_empty_style_value( $attributes['placeholderColor'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--placeholder-color:' . $attributes['placeholderColor'] . ';';
			}
			if ( ! self::is_empty_style_value( $attributes['inputBgColor'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--input-bg-color:' . $attributes['inputBgColor'] . ';';
			}
			if ( ! self::is_empty_style_value( $attributes['inputBorderColor'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--input-border-color:' . $attributes['inputBorderColor'] . ';';
			}
			if ( ! self::is_empty_style_value( $attributes['inputFocusBorderColor'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--input-focus-border-color:' . $attributes['inputFocusBorderColor'] . ';';
			}
			if ( ! empty( $label_typography['fontFamily'] ) ) {
				$vars[] = '--wp--gutena-forms--label-font-family:' . $label_typography['fontFamily'] . ';';
			}
			if ( ! empty( $label_typography ) ) {
				if ( ! self::is_empty_style_value( $label_typography['fontSize'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--label-font-size:' . $label_typography['fontSize'] . ';';
				}
				if ( ! self::is_empty_style_value( $label_typography['lineHeight'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--label-line-height:' . $label_typography['lineHeight'] . ';';
				}
				if ( ! self::is_empty_style_value( $label_typography['fontWeight'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--label-font-weight:' . $label_typography['fontWeight'] . ';';
				}
			}
			if ( ! empty( $placeholder_typo['fontFamily'] ) ) {
				$vars[] = '--wp--gutena-forms--placeholder-font-family:' . $placeholder_typo['fontFamily'] . ';';
			}
			if ( ! empty( $placeholder_typo ) ) {
				if ( ! self::is_empty_style_value( $placeholder_typo['fontSize'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--placeholder-font-size:' . $placeholder_typo['fontSize'] . ';';
				}
				if ( ! self::is_empty_style_value( $placeholder_typo['lineHeight'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--placeholder-line-height:' . $placeholder_typo['lineHeight'] . ';';
				}
				if ( ! self::is_empty_style_value( $placeholder_typo['fontWeight'] ?? '' ) ) {
					$vars[] = '--wp--gutena-forms--placeholder-font-weight:' . $placeholder_typo['fontWeight'] . ';';
				}
			}
			if ( ! self::is_empty_style_value( $attributes['inputBorderWidth'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--input-border-width:' . $attributes['inputBorderWidth'] . ';';
			}
			if ( ! self::is_empty_style_value( $attributes['inputBorderRadius'] ?? '' ) ) {
				$vars[] = '--wp--gutena-forms--input-border-radius:' . $attributes['inputBorderRadius'] . ';';
			}

			$vars[] = '--wp--style--block-gap:' . $block_gap . ';';

			$css  = '.' . $form_class . ' {' . implode( '', $vars ) . '}';
			$css .= '.' . $form_class . ' .wp-block-gutena-field-group {--wp--style--block-gap:' . $input_label_gap . ';}';

			return $css;
		}

		/**
		 * Resolve saved or generated form style CSS for frontend output.
		 *
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		public static function get_form_style_for_render( $attributes ) {
			$attributes = is_array( $attributes ) ? $attributes : array();

			if ( ! empty( $attributes['formStyle'] ) ) {
				return (string) $attributes['formStyle'];
			}

			return self::build_form_style_from_attributes( $attributes );
		}

		/**
		 * Normalize and inject per-form CSS before rendered block markup.
		 *
		 * @param string $content    Block HTML.
		 * @param string $form_style Raw CSS string.
		 * @return string
		 */
		private function inject_form_style_markup( $content, $form_style ) {
			if ( empty( $form_style ) ) {
				return $content;
			}

			if ( str_contains( $form_style, 'u002' ) ) {
				$form_style = str_replace(
					array( 'u002d', 'u0022', 'u003e' ),
					array( '-', '"', '>' ),
					$form_style
				);
			}

			return '<style>' . wp_strip_all_tags( $form_style ) . '</style>' . $content;
		}

		/**
		 * Render Form Block
		 *
		 * @since 1.6.0
		 * @param array  $attributes Block attributes.
		 * @param string $content Block content.
		 *
		 * @return string
		 */
		public function render_block( $attributes, $content ) {
			if ( empty( $attributes ) ) {
				return $content;
			}

			$content = $this->demote_nested_form_wrapper( $content );

			$form_style = self::get_form_style_for_render( $attributes );

			$content = $this->inject_form_style_markup( $content, $form_style );

			// Page-break / multi-step settings for front end (independent of email settings).
			if ( ! empty( $attributes['settings']['pageBreak'] ) && is_array( $attributes['settings']['pageBreak'] ) ) {
				$page_break_json = wp_json_encode( $attributes['settings']['pageBreak'] );
				if ( $page_break_json ) {
					$content = $this->inject_form_opening_attribute(
						$content,
						' data-page-break-settings="' . esc_attr( $page_break_json ) . '"'
					);
				}
			}

			$confirmation_config = null;
			if ( class_exists( 'Gutena_Forms_Confirmation_Helper' ) ) {
				$confirmation_config = Gutena_Forms_Confirmation_Helper::get_frontend_config( $attributes );
				if ( ! empty( $confirmation_config ) ) {
					$confirmation_json = wp_json_encode( $confirmation_config );
					$form_attrs        = ' data-form-confirmation="' . esc_attr( $confirmation_json ) . '"';

					if ( ! empty( $attributes['formName'] ) ) {
						$form_attrs .= ' data-form-name="' . esc_attr( $attributes['formName'] ) . '"';
					}

					$content = $this->inject_form_opening_attribute( $content, $form_attrs );
				}
			}

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
			} elseif (
				! empty( $confirmation_config )
				&& 'redirect' === ( $confirmation_config['confirmationType'] ?? '' )
			) {
				$legacy_redirect = '';
				if ( 'custom_url' === ( $confirmation_config['redirectType'] ?? '' ) ) {
					$legacy_redirect = $confirmation_config['redirectUrl'] ?? '';
				} else {
					$legacy_redirect = $confirmation_config['resolvedRedirectUrl'] ?? '';
				}
				if ( ! empty( $legacy_redirect ) ) {
					$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $legacy_redirect ) ) . '" />';
				}
			}

			$recaptcha_html      = '';
			$turnstile_html      = '';
			$honeypot_html       = '';
			$effective_recaptcha = self::get_effective_recaptcha( isset( $attributes['recaptcha'] ) ? $attributes['recaptcha'] : array() );
			$effective_turnstile = self::get_effective_turnstile( isset( $attributes['cloudflareTurnstile'] ) ? $attributes['cloudflareTurnstile'] : array() );

			if ( ! empty( $effective_recaptcha ) && ! empty( $effective_recaptcha['enable'] ) ) {
				if ( 'v2' === $effective_recaptcha['type'] ) {
					$recaptcha_html .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( $effective_recaptcha['site_key'] ) . '"></div><input type="hidden" name="recaptcha_enable" value="1" />';
				} elseif ( 'v3' === $effective_recaptcha['type'] ) {
					$recaptcha_html .= '<input type="hidden" name="g-recaptcha-response" value="" id="g-recaptcha-response-' . esc_attr( strtolower( $attributes['formID'] ) ) . '" />';
					$recaptcha_html .= '<script type="text/javascript">
						grecaptcha.ready( function () {
                            grecaptcha.execute( "' . esc_attr( $effective_recaptcha['site_key'] ) . '", { action: "submit" } ).then( function ( token ) {
                                var recaptchaResponse = document.getElementById( "g-recaptcha-response-' . esc_attr( strtolower( $attributes['formID'] ) ) . '" );
								if ( recaptchaResponse ) {
									recaptchaResponse.value = token;
								}
                        	} );
						} );
					</script>
					<input type="hidden" name="recaptcha_enable" value="1" />';
				}
			}

			if ( ! isset( $attributes['cloudflareTurnstile']['defaultSettings'] ) || false !== $attributes['cloudflareTurnstile']['defaultSettings'] ) {
				$turnstile_settings = get_option( 'gutena_forms__cloudflare', array() );
				$turnstile_source   = 'global';
			} else {
				$turnstile_settings = $attributes['cloudflareTurnstile'];
				$turnstile_source   = 'form';
			}

			if ( isset( $turnstile_settings['enable'] ) && $turnstile_settings['enable'] ) {
				$turnstile_html .= '<div class="cf-turnstile" data-sitekey="' . esc_attr( $turnstile_settings['site_key'] ) . '"></div><input type="hidden" name="turnstile_enable" value="1" />';
			}

			if ( ! isset( $attributes['honeypot']['defaultSettings'] ) || false !== $attributes['honeypot']['defaultSettings'] ) {
				$honeypot_settings = get_option( 'gutena_forms__honeypot', array() );
				$honeypot_source   = 'global';
			} else {
				$honeypot_settings = $attributes['honeypot'];
				$honeypot_source   = 'form';
			}

			if ( isset( $honeypot_settings['enable'] ) && $honeypot_settings['enable'] ) {
				$time_check_value = ! empty( $honeypot_settings['timeCheckValue'] )
					? intval( $honeypot_settings['timeCheckValue'] )
					: 4; // default 4 seconds
				// we need to different hidden fields 1 for checking time second for check honeypot.
				$honeypot_html .= '<div style="display:none;" aria-hidden="true">
					<label for="gf_hp_' . esc_attr( $attributes['formID'] ) . '">' . esc_html__( 'Leave this field empty', 'gutena-forms' ) . '</label>
					<input type="text" name="gf_hp_' . esc_attr( $attributes['formID'] ) . '" id="gf_hp_' . esc_attr( $attributes['formID'] ) . '" value="" autocomplete="off" tabindex="-1" />
					<input type="hidden" name="gf_time_check_' . esc_attr( $attributes['formID'] ) . '" value="' . esc_attr( time() + $time_check_value ) . '" autocomplete="off" />
				</div>';
			}

			// Add validation messages data attribute.
			if ( ! empty( $attributes['messages'] ) && isset( $attributes['messages']['defaultSettings'] ) && false === $attributes['messages']['defaultSettings'] ) {
				$messages_json = wp_json_encode( $attributes['messages'] );
				$content       = $this->inject_form_opening_attribute(
					$content,
					' data-validation-messages="' . esc_attr( $messages_json ) . '"'
				);
			}

			// Add recaptcha data attributes for frontend (per-form site key / type).
			if ( ! empty( $effective_recaptcha ) ) {
				$recaptcha_attrs = ' data-recaptcha-site-key="' . esc_attr( $effective_recaptcha['site_key'] ) . '" data-recaptcha-type="' . esc_attr( $effective_recaptcha['type'] ) . '"';
				$content         = $this->inject_form_opening_attribute( $content, $recaptcha_attrs );
			}

			// Legacy redirect hidden input (inside the form).
			if ( ! empty( $html ) ) {
				$content = $this->inject_after_form_opening( $content, $html );
			}

			// Submit Button HTML markup : change link to button tag.
			$content = $this->str_last_replace(
				'<a',
				$recaptcha_html . $turnstile_html . $honeypot_html . '<button ',
				$content
			);

			$content = $this->str_last_replace(
				'</a>',
				'</button>',
				$content
			);
			// filter content.
			$content = apply_filters( 'gutena_forms_render_form', $content, $attributes );

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
			// finds the position of the last occurrence of a string.
			$pos = strripos( $str, $search );

			if ( false !== $pos ) {
				$str = substr_replace( $str, $replace, $pos, strlen( $search ) );
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
							array( 'u002d', 'u0022', 'u003e' ),
							array( '-', '"', '>' ),
							$style
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
			static $recaptcha_start = 0;
			if ( 0 === $recaptcha_start ) {
				$grecaptcha = get_option( 'gutena_forms__recaptcha', array() );
				$grecaptcha = self::resolve_recaptcha_settings( $grecaptcha );
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
