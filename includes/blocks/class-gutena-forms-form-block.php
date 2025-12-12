<?php
/**
 * Class Gutena_Forms_Form_Block
 *
 * @since 1.6.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Block' ) ) :
	/**
	 * Gutena Forms Form Block Class
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
		 * Get the instance of the class.
		 *
		 * @since 1.6.0
		 * @return Gutena_Forms_Form_Block
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Render the form block.
		 *
		 * @since 1.6.0
		 */
		public function render( $attributes, $content, $block ) {
			// No changes if attributes is empty
			if ( empty( $attributes ) || empty( $attributes['adminEmails'] ) ) {
				return $content;
			}

			$html = '';
			if ( ! empty( $attributes['redirectUrl'] ) ) {
				$html = '<input type="hidden" name="redirect_url" value="' . esc_attr( esc_url( $attributes['redirectUrl'] ) ) . '" />';
			}

			//google recaptcha
			$recaptcha_html = '';
			if ( ! empty( $attributes['recaptcha'] ) && ! empty( $attributes['recaptcha']['enable'] ) && ! empty( $attributes['recaptcha']['site_key'] ) && ! empty( $attributes['recaptcha']['type'] ) ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_grecaptcha_scripts' ));

				//input box for v2 type only
				if ( 'v2' === $attributes['recaptcha']['type'] ){
					$recaptcha_html = '<div class="g-recaptcha" data-sitekey="' . esc_attr( $attributes['recaptcha']['site_key'] ) . '"></div><br>';
				}

				//input field to check if recaptcha or not
				$html .= '<input type="hidden" name="recaptcha_enable" value="' . esc_attr( $attributes['recaptcha']['enable'] ) . '" />';
			}

			//cloudflare turnstile
			$turnstile_html = '';
			if ( ! empty( $attributes['cloudflareTurnstile'] ) && ! empty( $attributes['cloudflareTurnstile']['enable'] && ! empty( $attributes['cloudflareTurnstile']['site_key'] ) ) ) {

				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_cloudflare_turnstile_scripts' ));

				$turnstile_html .= '<div
					class="cf-turnstile"
					data-sitekey="' . esc_attr( $attributes['cloudflareTurnstile']['site_key'] ) . '"
					data-theme="light"
					data-size="normal"
					data-callback="onSuccess"
				></div><br />';

				$html .= '<input type="hidden" name="turnstile_enable" value="' . esc_attr( $attributes['cloudflareTurnstile']['enable'] ) . '" />';
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
			$content = Gutena_Forms_Helper::str_last_replace(
				'<a',
				$recaptcha_html.$turnstile_html.'<button',
				$content
			);

			$content = Gutena_Forms_Helper::str_last_replace(
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
		 * Enqueue block styles.
		 *
		 * @since 1.6.0
		 * @param string $style CSS styles.
		 * @param int    $priority Action hook priority.
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
		 * Enqueue Google reCAPTCHA scripts.
		 *
		 * @since 1.6.0
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
		 * Enqueue Cloudflare Turnstile scripts.
		 *
		 * @since 1.6.0
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
	}
endif;
