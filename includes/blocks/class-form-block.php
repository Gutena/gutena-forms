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
			$global = get_option( 'gutena_forms__recaptcha', false );
			$use_global = (
				( ! isset( $block_recaptcha['defaultSettings'] ) || false !== $block_recaptcha['defaultSettings'] )
				|| ( ( empty( $block_recaptcha['site_key'] ) || empty( $block_recaptcha['secret_key'] ) ) && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) )
			);
			if ( $use_global && ! empty( $global ) && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) && ! empty( $global['type'] ) ) {
				return array_merge(
					array(
						'type'           => 'v3',
						'thresholdScore' => 0.5,
					),
					$global
				);
			}
			if ( ! empty( $block_recaptcha['site_key'] ) && ! empty( $block_recaptcha['secret_key'] ) && ! empty( $block_recaptcha['type'] ) ) {
				return array_merge(
					array(
						'thresholdScore' => 0.5,
					),
					$block_recaptcha
				);
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
			$global = get_option( 'gutena_forms__cloudflare', array() );
			$use_global = (
				( ! isset( $block_turnstile['defaultSettings'] ) || false !== $block_turnstile['defaultSettings'] )
				|| ( ( empty( $block_turnstile['site_key'] ) || empty( $block_turnstile['secret_key'] ) ) && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) )
			);
			if ( $use_global && ! empty( $global['site_key'] ) && ! empty( $global['secret_key'] ) ) {
				return array_merge(
					array(
						'enable' => true,
						'site_key' => '',
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

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
			}

			$recaptcha_html = $turnstile_html = $honeypot_html = '';

			$recaptcha_settings = array();
			if ( isset( $attributes['recaptcha']['defaultSettings'] ) && $attributes['recaptcha']['defaultSettings'] ) {
				$recaptcha_settings = get_option( 'gutena_forms__recaptcha', false );
			} else {
				$recaptcha_settings = $attributes['recaptcha'];
			}

			if ( isset( $recaptcha_settings['enable'] ) && $recaptcha_settings['enable'] ) {
				if  ( 'v2' === $recaptcha_settings['type'] ) {
					$recaptcha_html .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( $recaptcha_settings['site_key'] ) . '"></div><input type="hidden" name="recaptcha_enable" value="1" />';
				} elseif ( 'v3' === $recaptcha_settings['type'] ) {
					$recaptcha_html .= '<input type="hidden" name="g-recaptcha-response" value="" id="g-recaptcha-response-' . esc_attr( strtolower( $attributes['formID'] ) ) . '" />';
					$recaptcha_html .= '<script type="text/javascript">
						grecaptcha.ready( function () {
                            grecaptcha.execute( "' . esc_attr( $recaptcha_settings['site_key'] ) . '", { action: "submit" } ).then( function ( token ) {
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

			$effective_turnstile = isset( $attributes['cloudflareTurnstile'] ) ? self::get_effective_turnstile( $attributes['cloudflareTurnstile'] ) : null;
			if ( ! empty( $effective_turnstile ) && ! empty( $effective_turnstile['site_key'] ) ) {
				$turnstile_html = '<div class="cf-turnstile" data-sitekey="' . esc_attr( $effective_turnstile['site_key'] ) . '"></div>';
			}


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

			// Add validation messages data attribute
			if ( ! empty( $attributes['messages'] ) && ( ! isset( $attributes['messages']['defaultSettings'] ) || false === $attributes['messages']['defaultSettings'] ) ) {
				$messages_json = wp_json_encode( $attributes['messages'] );
				$content = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					' data-validation-messages="' . esc_attr( $messages_json ) . '">',
					$content,
					1
				);
			}

			// Add recaptcha data attributes for frontend (per-form site key / type)
			if ( ! empty( $effective_recaptcha ) ) {
				$recaptcha_attrs = ' data-recaptcha-site-key="' . esc_attr( $effective_recaptcha['site_key'] ) . '" data-recaptcha-type="' . esc_attr( $effective_recaptcha['type'] ) . '"';
				$content = preg_replace(
					'/' . preg_quote( '>', '/' ) . '/',
					$recaptcha_attrs . '>',
					$content,
					1
				);
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
				$recaptcha_html . $turnstile_html . $honeypot_html . '<button ',
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
			static $recaptcha_start = 0;
			if ( 0 === $recaptcha_start  ) {
				$grecaptcha = get_option( 'gutena_forms_grecaptcha', false );
				if ( ! empty( $grecaptcha ) && ! empty( $grecaptcha['site_key'] ) && ! empty( $grecaptcha['type'] ) ) {

					wp_enqueue_script(
						'google-recaptcha',
						esc_url( 'https://www.google.com/recaptcha/api.js'.( ( 'v2' === $grecaptcha['type'] ) ? '' : '?render='. esc_attr( $grecaptcha['site_key'] )  ) ),
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
			static $turnstile_start = 0;
			if ( 0 === $turnstile_start  ) {
				$cloudflare_turnstile = get_option( 'gutena_forms_cloudflare_turnstile', false );
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
