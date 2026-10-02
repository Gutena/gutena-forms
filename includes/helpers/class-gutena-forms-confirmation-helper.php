<?php
/**
 * Form confirmation helper: defaults, sanitization, and redirect utilities.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Confirmation_Helper' ) ) :
	/**
	 * Form confirmation helper class.
	 */
	class Gutena_Forms_Confirmation_Helper {

		/**
		 * Option name for global form confirmation defaults.
		 */
		const OPTION_NAME = 'gutena_forms__form_confirmation';

		/**
		 * Default settings.
		 *
		 * @return array
		 */
		public static function get_defaults() {
			return array(
				'confirmation_type' => 'message',
				'success_message'   => self::get_default_success_message_html(),
				'error_message'     => self::get_default_error_message_html(),
				'after_submit'      => 'hide',
				'redirect_type'     => 'page',
				'redirect_page_id'  => 0,
				'redirect_url'      => '',
			);
		}

		/**
		 * Default success message HTML.
		 *
		 * @return string
		 */
		public static function get_default_success_message_html() {
			$icon_url = esc_url(
				GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/success-tick.svg'
			);

			$message = __( 'Your form submitted successfully!', 'gutena-forms' );

			return sprintf(
				'<div class="gutena-forms-success-banner gutena-forms-success-banner--stacked"><img src="%1$s" alt="%2$s" class="form-message-icon" width="16" height="16" /><p class="gutena-forms-success-text">%3$s</p></div>',
				$icon_url,
				esc_attr__( 'Success', 'gutena-forms' ),
				esc_html( $message )
			);
		}

		/**
		 * Default error message HTML.
		 *
		 * @return string
		 */
		public static function get_default_error_message_html() {
			$icon_url = esc_url(
				GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/error.svg'
			);

			$heading = __( 'Something went wrong', 'gutena-forms' );
			$message = __( 'Something went wrong while submitting the form. Please check your entries and try again.', 'gutena-forms' );

			return sprintf(
				'<div class="gutena-forms-error-message"><img src="%1$s" alt="%2$s" class="form-message-icon" width="16" height="16" /><h3>%3$s</h3><p class="gutena-forms-error-text">%4$s</p></div>',
				$icon_url,
				esc_attr__( 'Error', 'gutena-forms' ),
				esc_html( $heading ),
				esc_html( $message )
			);
		}

		/**
		 * Success message icon URL.
		 *
		 * @return string
		 */
		public static function get_success_message_icon_url() {
			return esc_url(
				GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/success-tick.svg'
			);
		}

		/**
		 * Error message icon URL.
		 *
		 * @return string
		 */
		public static function get_error_message_icon_url() {
			return esc_url(
				GUTENA_FORMS_PLUGIN_URL . 'src/blocks/form/variations/assets/error.svg'
			);
		}

		/**
		 * Success message icon image markup.
		 *
		 * @return string
		 */
		public static function get_success_message_icon_img() {
			return sprintf(
				'<img src="%1$s" alt="%2$s" class="form-message-icon" width="16" height="16" />',
				self::get_success_message_icon_url(),
				esc_attr__( 'Success', 'gutena-forms' )
			);
		}

		/**
		 * Replace the {icon} merge tag with the success icon image.
		 *
		 * @param string $html Raw HTML.
		 * @return string
		 */
		public static function replace_success_icon_merge_tag( $html ) {
			if ( false === strpos( $html, '{icon}' ) ) {
				return $html;
			}

			$icon = self::get_success_message_icon_img();
			$html = preg_replace( '/<p>\s*\{icon\}\s*<\/p>/i', $icon, $html );

			return str_replace( '{icon}', $icon, $html );
		}

		/**
		 * Whether the success message should use stacked icon + text layout.
		 *
		 * @param string $html HTML content.
		 * @return bool
		 */
		private static function success_message_uses_stacked_layout( $html ) {
			if ( false !== strpos( $html, '{icon}' ) ) {
				return true;
			}

			if ( preg_match( '/<p>\s*<img[^>]*form-message-icon[^>]*>\s*<\/p>/i', $html ) ) {
				return true;
			}

			return 1 < preg_match_all( '/<p\b/i', $html );
		}

		/**
		 * Ensure a simple success banner uses the stacked layout modifier.
		 *
		 * @param string $html Banner HTML.
		 * @return string
		 */
		private static function ensure_success_banner_stacked_class( $html ) {
			if ( false !== strpos( $html, 'gutena-forms-success-banner--stacked' )
				|| false !== strpos( $html, 'gutena-forms-success-banner--rich' ) ) {
				return $html;
			}

			return str_replace(
				'gutena-forms-success-banner"',
				'gutena-forms-success-banner gutena-forms-success-banner--stacked"',
				$html
			);
		}

		/**
		 * Normalize legacy success HTML into the green banner layout.
		 *
		 * @param string $html Raw success HTML.
		 * @return string
		 */
		public static function normalize_success_message_html( $html ) {
			$raw_input = (string) $html;
			$html      = self::sanitize_confirmation_html( $html );

			if ( '' === trim( $html ) ) {
				return self::get_default_success_message_html();
			}

			if ( false !== strpos( $html, 'gutena-forms-success-banner' ) ) {
				$html = self::replace_success_icon_merge_tag( $html );
				$html = self::ensure_success_banner_stacked_class( $html );

				return $html;
			}

			$had_icon_token = false !== strpos( $raw_input, '{icon}' );
			$html           = self::replace_success_icon_merge_tag( $html );

			$inner = $html;

			if ( preg_match( '/<div[^>]*class="[^"]*gutena-forms-confirmation-message[^"]*"[^>]*>(.*)<\/div>\s*$/is', $html, $matches ) ) {
				$inner = $matches[1];
			}

			$inner = preg_replace( '/^\s*<img[^>]*class="[^"]*form-message-icon[^"]*"[^>]*>\s*/i', '', $inner );
			$inner = trim( (string) $inner );

			if ( '' === $inner ) {
				return self::get_default_success_message_html();
			}

			$has_heading  = (bool) preg_match( '/<h[1-6][^>]*>/i', $inner );
			$banner_class = 'gutena-forms-success-banner';
			$uses_stacked = $had_icon_token || self::success_message_uses_stacked_layout( $inner );

			if ( $has_heading ) {
				$banner_class .= ' gutena-forms-success-banner--rich';
				$content = sprintf(
					'<div class="gutena-forms-success-banner__content">%s</div>',
					$inner
				);
			} else {
				if ( $uses_stacked ) {
					$banner_class .= ' gutena-forms-success-banner--stacked';
				}

				if ( preg_match( '/^<p(?![^>]*class=)/i', $inner ) ) {
					$inner = preg_replace( '/^<p/i', '<p class="gutena-forms-success-text"', $inner, 1 );
				}

				$inner = preg_replace(
					'/<p(?![^>]*class=)/i',
					'<p class="gutena-forms-success-text"',
					$inner
				);

				$content = $inner;
			}

			$has_icon_in_content = false !== strpos( $content, 'form-message-icon' );

			if ( $has_icon_in_content ) {
				$output = sprintf(
					'<div class="%1$s">%2$s</div>',
					esc_attr( $banner_class ),
					$content
				);
			} else {
				$output = sprintf(
					'<div class="%1$s">%2$s%3$s</div>',
					esc_attr( $banner_class ),
					self::get_success_message_icon_img(),
					$content
				);
			}

			return $output;
		}

		/**
		 * Normalize legacy error HTML into the structured error layout.
		 *
		 * @param string $html Raw error HTML.
		 * @return string
		 */
		public static function normalize_error_message_html( $html ) {
			$html = self::sanitize_confirmation_html( $html );

			if ( '' === trim( $html ) ) {
				return self::get_default_error_message_html();
			}

			if ( false !== strpos( $html, 'gutena-forms-error-message' ) ) {
				return $html;
			}

			$inner = preg_replace( '/^\s*<img[^>]*class="[^"]*form-message-icon[^"]*"[^>]*>\s*/i', '', $html );
			$inner = trim( (string) $inner );

			if ( '' === $inner ) {
				return self::get_default_error_message_html();
			}

			if ( ! preg_match( '/<h[1-6][^>]*>/i', $inner ) ) {
				$inner = sprintf(
					'<h3>%1$s</h3><p class="gutena-forms-error-text">%2$s</p>',
					esc_html__( 'Something went wrong', 'gutena-forms' ),
					$inner
				);
			} elseif ( ! preg_match( '/class="[^"]*gutena-forms-error-text[^"]*"/i', $inner ) ) {
				$inner = preg_replace(
					'/<p(?![^>]*class=)/i',
					'<p class="gutena-forms-error-text"',
					$inner
				);
			}

			return sprintf(
				'<div class="gutena-forms-error-message"><img src="%1$s" alt="%2$s" class="form-message-icon" width="16" height="16" />%3$s</div>',
				self::get_error_message_icon_url(),
				esc_attr__( 'Error', 'gutena-forms' ),
				$inner
			);
		}

		/**
		 * Get saved settings merged with defaults.
		 *
		 * @return array
		 */
		public static function get_settings() {
			$settings = get_option( self::OPTION_NAME, array() );
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}

			return wp_parse_args( $settings, self::get_defaults() );
		}

		/**
		 * Form confirmation defaults for newly created forms (block editor).
		 *
		 * @return array
		 */
		public static function get_form_defaults_for_editor() {
			$settings = self::get_settings();

			return array(
				'confirmation_type'     => $settings['confirmation_type'],
				'success_message'       => self::normalize_success_message_html( $settings['success_message'] ),
				'error_message'         => self::normalize_error_message_html( $settings['error_message'] ),
				'after_submit'          => $settings['after_submit'],
				'redirect_type'         => $settings['redirect_type'],
				'redirect_page_id'      => (int) $settings['redirect_page_id'],
				'redirect_url'          => $settings['redirect_url'],
				'resolved_redirect_url' => self::resolve_redirect_url( $settings ),
			);
		}

		/**
		 * Static merge tags for admin UI.
		 *
		 * @return array
		 */
		public static function get_static_merge_tags() {
			return array(
				'{icon}',
				'{site_name}',
				'{site_url}',
				'{submission_date}',
				'{form-title}',
				'{form_title}',
				'{admin_email}',
			);
		}

		/**
		 * Sanitize confirmation HTML before save/output.
		 *
		 * @param string $html Raw HTML.
		 * @return string
		 */
		public static function sanitize_confirmation_html( $html ) {
			$html = (string) $html;

			if ( '' !== trim( $html ) && false === strpos( $html, '<' ) ) {
				$html = wpautop( $html );
			}

			return wp_kses_post( $html );
		}

		/**
		 * Sanitize redirect page ID.
		 *
		 * @param mixed $page_id Page ID.
		 * @return int
		 */
		public static function sanitize_redirect_page_id( $page_id ) {
			$page_id = absint( $page_id );

			if ( $page_id <= 0 ) {
				return 0;
			}

			if ( 'page' !== get_post_type( $page_id ) || 'publish' !== get_post_status( $page_id ) ) {
				return 0;
			}

			return $page_id;
		}

		/**
		 * Sanitize and validate a redirect URL.
		 *
		 * @param string $url Raw URL.
		 * @return string Safe URL or empty string.
		 */
		public static function sanitize_redirect_url( $url ) {
			$url = trim( (string) $url );

			if ( '' === $url ) {
				return '';
			}

			$url = esc_url_raw( $url );

			if ( '' === $url ) {
				return '';
			}

			$parsed = wp_parse_url( $url );
			if ( empty( $parsed['scheme'] ) || ! in_array( strtolower( $parsed['scheme'] ), array( 'http', 'https' ), true ) ) {
				return '';
			}

			if ( function_exists( 'wp_http_validate_url' ) && ! wp_http_validate_url( $url ) ) {
				return '';
			}

			return $url;
		}

		/**
		 * Whether form confirmation was explicitly disabled in saved settings.
		 *
		 * @param array $settings Form confirmation settings.
		 * @return bool
		 */
		public static function is_confirmation_explicitly_disabled( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			return isset( $settings['hasSavedConfig'] )
				&& true === (bool) $settings['hasSavedConfig']
				&& isset( $settings['enabled'] )
				&& false === (bool) $settings['enabled'];
		}

		/**
		 * Whether a form inherits global confirmation messages.
		 *
		 * @param array $settings Form confirmation settings.
		 * @return bool
		 */
		public static function uses_global_confirmation_defaults( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			if ( isset( $settings['hasSavedConfig'] ) && true === (bool) $settings['hasSavedConfig']
				&& isset( $settings['defaultSettings'] ) && false === (bool) $settings['defaultSettings'] ) {
				return false;
			}

			if ( isset( $settings['hasSavedConfig'] ) && true === (bool) $settings['hasSavedConfig']
				&& ! isset( $settings['defaultSettings'] ) ) {
				return false;
			}

			return ! isset( $settings['defaultSettings'] ) || true === (bool) $settings['defaultSettings'];
		}

		/**
		 * Resolve stored confirmation messages with global or form settings.
		 *
		 * @param array $settings Form confirmation settings.
		 * @return array
		 */
		public static function resolve_confirmation_messages( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$global   = self::get_settings();

			if ( self::uses_global_confirmation_defaults( $settings ) ) {
				return array(
					'success_message' => self::normalize_success_message_html( $global['success_message'] ),
					'error_message'   => self::normalize_error_message_html( $global['error_message'] ),
				);
			}

			$success_message = isset( $settings['successMessage'] )
				? self::sanitize_confirmation_html( $settings['successMessage'] )
				: '';
			$error_message   = isset( $settings['errorMessage'] )
				? self::sanitize_confirmation_html( $settings['errorMessage'] )
				: '';

			if ( '' === trim( (string) $success_message ) ) {
				$success_message = $global['success_message'];
			}

			if ( '' === trim( (string) $error_message ) ) {
				$error_message = $global['error_message'];
			}

			return array(
				'success_message' => self::normalize_success_message_html( $success_message ),
				'error_message'   => self::normalize_error_message_html( $error_message ),
			);
		}

		/**
		 * Resolve full confirmation settings (messages, redirect, type) from global or form attrs.
		 *
		 * @param array $settings Form confirmation settings from block attrs.
		 * @return array
		 */
		public static function resolve_confirmation_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$global   = self::get_settings();
			$uses_global = self::uses_global_confirmation_defaults( $settings );

			if ( $uses_global ) {
				$confirmation_type = sanitize_key( $global['confirmation_type'] );
				$after_submit      = sanitize_key( $global['after_submit'] );
				$redirect_type       = sanitize_key( $global['redirect_type'] );
				$redirect_page_id    = self::sanitize_redirect_page_id( $global['redirect_page_id'] );
				$redirect_url        = self::sanitize_redirect_url( $global['redirect_url'] );
				$messages            = array(
					'success_message' => self::normalize_success_message_html( $global['success_message'] ),
					'error_message'   => self::normalize_error_message_html( $global['error_message'] ),
				);
			} else {
				$confirmation_type = isset( $settings['confirmationType'] ) ? sanitize_key( $settings['confirmationType'] ) : 'message';
				$after_submit      = isset( $settings['afterSubmit'] ) ? sanitize_key( $settings['afterSubmit'] ) : 'hide';
				$redirect_type       = isset( $settings['redirectType'] ) ? sanitize_key( $settings['redirectType'] ) : 'page';
				$redirect_page_id    = self::sanitize_redirect_page_id( $settings['redirectPageId'] ?? 0 );
				$redirect_url        = self::sanitize_redirect_url( $settings['redirectUrl'] ?? '' );
				$messages            = self::resolve_confirmation_messages( $settings );
			}

			if ( ! in_array( $confirmation_type, array( 'message', 'redirect' ), true ) ) {
				$confirmation_type = 'message';
			}

			if ( ! in_array( $after_submit, array( 'hide', 'reset' ), true ) ) {
				$after_submit = 'hide';
			}

			if ( ! in_array( $redirect_type, array( 'page', 'custom_url' ), true ) ) {
				$redirect_type = 'page';
			}

			$resolved_redirect_url = self::resolve_redirect_url(
				array(
					'confirmation_type' => $confirmation_type,
					'redirect_type'       => $redirect_type,
					'redirect_page_id'    => $redirect_page_id,
					'redirect_url'        => $redirect_url,
				)
			);

			return array(
				'confirmation_type'     => $confirmation_type,
				'after_submit'          => $after_submit,
				'redirect_type'         => $redirect_type,
				'redirect_page_id'      => $redirect_page_id,
				'redirect_url'          => $redirect_url,
				'resolved_redirect_url' => $resolved_redirect_url,
				'success_message'       => $messages['success_message'],
				'error_message'         => $messages['error_message'],
			);
		}

		/**
		 * Build frontend-safe confirmation config from block attributes.
		 *
		 * @param array $attributes Form block attributes.
		 * @return array|null
		 */
		public static function get_frontend_config( $attributes ) {
			$attributes = is_array( $attributes ) ? $attributes : array();
			$settings   = isset( $attributes['settings']['formConfirmation'] ) && is_array( $attributes['settings']['formConfirmation'] )
				? $attributes['settings']['formConfirmation']
				: array();

			if ( self::is_confirmation_explicitly_disabled( $settings ) ) {
				return null;
			}

			// Match editor behavior: missing formConfirmation inherits global defaults.
			if ( empty( $settings ) ) {
				$settings = array(
					'defaultSettings' => true,
					'enabled'         => true,
				);
			}

			$resolved = self::resolve_confirmation_settings( $settings );

			return array(
				'enabled'             => true,
				'confirmationType'    => $resolved['confirmation_type'],
				'successMessage'      => $resolved['success_message'],
				'errorMessage'        => $resolved['error_message'],
				'afterSubmit'         => $resolved['after_submit'],
				'redirectType'        => $resolved['redirect_type'],
				'redirectPageId'      => $resolved['redirect_page_id'],
				'redirectUrl'         => $resolved['redirect_url'],
				'resolvedRedirectUrl' => $resolved['resolved_redirect_url'],
			);
		}

		/**
		 * Resolve a safe redirect URL from saved settings.
		 *
		 * @param array $settings Confirmation settings.
		 * @return string
		 */
		public static function resolve_redirect_url( $settings ) {
			$settings = wp_parse_args( is_array( $settings ) ? $settings : array(), self::get_defaults() );

			if ( 'redirect' !== $settings['confirmation_type'] ) {
				return '';
			}

			if ( 'page' === $settings['redirect_type'] ) {
				$page_id = absint( $settings['redirect_page_id'] );
				if ( $page_id <= 0 ) {
					return '';
				}

				$permalink = get_permalink( $page_id );
				if ( ! $permalink ) {
					return '';
				}

				return self::sanitize_redirect_url( $permalink );
			}

			return self::sanitize_redirect_url( $settings['redirect_url'] );
		}

		/**
		 * Validate settings payload before save.
		 *
		 * @param array $settings Raw settings.
		 * @return true|WP_Error
		 */
		public static function validate_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$defaults = self::get_defaults();

			$confirmation_type = isset( $settings['confirmation_type'] ) ? sanitize_key( $settings['confirmation_type'] ) : $defaults['confirmation_type'];
			if ( ! in_array( $confirmation_type, array( 'message', 'redirect' ), true ) ) {
				return new WP_Error(
					'gutena_forms_invalid_confirmation_type',
					__( 'Invalid confirmation type.', 'gutena-forms' )
				);
			}

			$after_submit = isset( $settings['after_submit'] ) ? sanitize_key( $settings['after_submit'] ) : $defaults['after_submit'];
			if ( ! in_array( $after_submit, array( 'hide', 'reset' ), true ) ) {
				return new WP_Error(
					'gutena_forms_invalid_after_submit',
					__( 'Invalid after-submit behavior.', 'gutena-forms' )
				);
			}

			$redirect_type = isset( $settings['redirect_type'] ) ? sanitize_key( $settings['redirect_type'] ) : $defaults['redirect_type'];
			if ( ! in_array( $redirect_type, array( 'page', 'custom_url' ), true ) ) {
				return new WP_Error(
					'gutena_forms_invalid_redirect_type',
					__( 'Invalid redirect type.', 'gutena-forms' )
				);
			}

			if ( 'redirect' === $confirmation_type ) {
				if ( 'custom_url' === $redirect_type ) {
					$redirect_url = isset( $settings['redirect_url'] ) ? trim( (string) $settings['redirect_url'] ) : '';
					if ( '' === $redirect_url ) {
						return new WP_Error(
							'gutena_forms_missing_redirect_url',
							__( 'Please enter a redirect URL.', 'gutena-forms' )
						);
					}

					if ( '' === self::sanitize_redirect_url( $redirect_url ) ) {
						return new WP_Error(
							'gutena_forms_invalid_redirect_url',
							__( 'Please enter a valid redirect URL using http:// or https://.', 'gutena-forms' )
						);
					}
				} elseif ( absint( $settings['redirect_page_id'] ?? 0 ) <= 0 ) {
					return new WP_Error(
						'gutena_forms_missing_redirect_page',
						__( 'Please select a page to redirect to.', 'gutena-forms' )
					);
				}
			}

			return true;
		}

		/**
		 * Sanitize settings payload before save.
		 *
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$defaults = self::get_defaults();

			$confirmation_type = isset( $settings['confirmation_type'] ) ? sanitize_key( $settings['confirmation_type'] ) : $defaults['confirmation_type'];
			if ( ! in_array( $confirmation_type, array( 'message', 'redirect' ), true ) ) {
				$confirmation_type = $defaults['confirmation_type'];
			}

			$after_submit = isset( $settings['after_submit'] ) ? sanitize_key( $settings['after_submit'] ) : $defaults['after_submit'];
			if ( ! in_array( $after_submit, array( 'hide', 'reset' ), true ) ) {
				$after_submit = $defaults['after_submit'];
			}

			$redirect_type = isset( $settings['redirect_type'] ) ? sanitize_key( $settings['redirect_type'] ) : $defaults['redirect_type'];
			if ( ! in_array( $redirect_type, array( 'page', 'custom_url' ), true ) ) {
				$redirect_type = $defaults['redirect_type'];
			}

			$success_message = isset( $settings['success_message'] ) ? self::sanitize_confirmation_html( $settings['success_message'] ) : $defaults['success_message'];
			$error_message   = isset( $settings['error_message'] ) ? self::sanitize_confirmation_html( $settings['error_message'] ) : $defaults['error_message'];
			$success_message = self::normalize_success_message_html( $success_message );
			$error_message   = self::normalize_error_message_html( $error_message );

			$redirect_page_id = self::sanitize_redirect_page_id( $settings['redirect_page_id'] ?? 0 );
			$redirect_url     = self::sanitize_redirect_url( $settings['redirect_url'] ?? '' );

			if ( 'redirect' === $confirmation_type && 'page' === $redirect_type ) {
				$redirect_url = '';
			}

			if ( 'redirect' === $confirmation_type && 'custom_url' === $redirect_type ) {
				$redirect_page_id = 0;
			}

			return array(
				'confirmation_type' => $confirmation_type,
				'success_message'   => $success_message,
				'error_message'     => $error_message,
				'after_submit'      => $after_submit,
				'redirect_type'     => $redirect_type,
				'redirect_page_id'  => $redirect_page_id,
				'redirect_url'      => $redirect_url,
			);
		}
	}
endif;
