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
		 * Render Form Block
		 *
		 * @since 1.6.0
		 * @param array  $attributes Block attributes.
		 * @param string $content Block content.
		 *
		 * @return string
		 */
		public function render_block( $attributes, $content ) {
			// No changes if attributes is empty.
			if ( empty( $attributes ) || empty( $attributes['adminEmails'] ) ) {
				return $content;
			}

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
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
				$content       = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					' data-validation-messages="' . esc_attr( $messages_json ) . '">',
					$content,
					1
				);
			}

			// Add recaptcha data attributes for frontend (per-form site key / type).
			if ( ! empty( $effective_recaptcha ) ) {
				$recaptcha_attrs = ' data-recaptcha-site-key="' . esc_attr( $effective_recaptcha['site_key'] ) . '" data-recaptcha-type="' . esc_attr( $effective_recaptcha['type'] ) . '"';
				$content         = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					$recaptcha_attrs . '>',
					$content,
					1
				);
			}

			// Add required html.
			if ( ! empty( $html ) ) {
				$content = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					'>' . $html,
					$content,
					1
				);
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
			$content   = $this->strip_form_inline_padding( $content );
			$form_style = isset( $attributes['formStyle'] ) ? $attributes['formStyle'] : '';
			$form_style = $this->strip_padding_from_css( $form_style );
			$padding_css = $this->get_block_padding_css( $attributes );
			$alignfull_css = $this->get_alignfull_containment_css( $attributes );
			if ( ! empty( $padding_css ) ) {
				$form_style .= ' ' . $padding_css;
			}
			if ( ! empty( $alignfull_css ) ) {
				$form_style .= ' ' . $alignfull_css;
			}
			// Enqueue block styles.
			$this->enqueue_block_styles( $form_style );

			return $content;
		}

		/**
		 * Normalize a spacing value for CSS output.
		 *
		 * @since 2.0.0
		 * @param mixed $value Raw spacing value.
		 * @return string
		 */
		private function normalize_spacing_value( $value ) {
			if ( null === $value || '' === $value ) {
				return '';
			}

			if ( '0' === $value || 0 === $value ) {
				return '0';
			}

			if ( is_string( $value ) && preg_match( '/var:preset\|spacing\|(.+)/', $value, $matches ) ) {
				return 'var(--wp--preset--spacing--' . $matches[1] . ')';
			}

			return $value;
		}

		/**
		 * Get the CSS selector used for per-form inline styles.
		 *
		 * @since 2.0.0
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		private function get_form_style_selector( $attributes ) {
			if ( ! empty( $attributes['formClasses'] ) ) {
				$classes = preg_split( '/\s+/', trim( $attributes['formClasses'] ) );
				foreach ( $classes as $class ) {
					if ( 0 === strpos( $class, 'gutena-forms-' ) && 0 !== strpos( $class, 'gutena_forms_ID_' ) ) {
						return '.' . $class;
					}
				}
			}

			return '.wp-block-gutena-forms';
		}

		/**
		 * Build padding CSS from block spacing attributes.
		 *
		 * @since 2.0.0
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		private function get_block_padding_css( $attributes ) {
			if ( empty( $attributes['style']['spacing']['padding'] ) || ! is_array( $attributes['style']['spacing']['padding'] ) ) {
				return '';
			}

			$padding     = $attributes['style']['spacing']['padding'];
			$padding_css = '';

			foreach ( array( 'top', 'right', 'bottom', 'left' ) as $side ) {
				if ( ! array_key_exists( $side, $padding ) ) {
					continue;
				}

				$value = $this->normalize_spacing_value( $padding[ $side ] );
				if ( '' !== $value ) {
					$padding_css .= 'padding-' . $side . ':' . $value . ';';
				}
			}

			if ( '' === $padding_css ) {
				return '';
			}

			return $this->get_form_style_selector( $attributes ) . ' .gutena-forms-content-wrapper{' . $padding_css . '}';
		}

		/**
		 * Remove padding declarations from a CSS string.
		 *
		 * @since 2.0.0
		 * @param string $css CSS string.
		 * @return string
		 */
		private function strip_padding_from_css( $css ) {
			if ( empty( $css ) ) {
				return '';
			}

			return preg_replace( '/padding-(top|right|bottom|left)\s*:\s*[^;]+;/', '', $css );
		}

		/**
		 * Strip inline padding from the saved form tag markup.
		 *
		 * @since 2.0.0
		 * @param string $content Form block content.
		 * @return string
		 */
		private function strip_form_inline_padding( $content ) {
			if ( empty( $content ) || false === stripos( $content, '<form' ) ) {
				return $content;
			}

			return preg_replace_callback(
				'/<form\b([^>]*)\sstyle="([^"]*)"([^>]*)>/i',
				static function ( $matches ) {
					$style = preg_replace( '/padding-(top|right|bottom|left)\s*:\s*[^;"]+;?\s*/i', '', $matches[2] );
					$style = trim( $style, '; ' );

					if ( '' === $style ) {
						return '<form' . $matches[1] . $matches[3] . '>';
					}

					return '<form' . $matches[1] . ' style="' . $style . '"' . $matches[3] . '>';
				},
				$content,
				1
			);
		}

		/**
		 * Build CSS to contain alignfull columns inside the form content wrapper.
		 *
		 * @since 2.0.0
		 * @param array $attributes Block attributes.
		 * @return string
		 */
		private function get_alignfull_containment_css( $attributes ) {
			$selector = $this->get_form_style_selector( $attributes ) . ' .gutena-forms-content-wrapper';

			return $selector . ' .wp-block-columns,' .
				$selector . ' .wp-block-columns.alignfull,' .
				$selector . ' .alignfull{' .
				'margin-left:0!important;margin-right:0!important;width:100%!important;max-width:100%!important;' .
				'}';
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
					echo '<style>' . esc_attr( $new_style ) . "</style>\n";
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
