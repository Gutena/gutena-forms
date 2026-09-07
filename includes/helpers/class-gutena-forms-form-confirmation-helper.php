<?php
/**
 * Form Confirmation helper: defaults, sanitization, effective settings,
 * merge tag resolution and frontend payload building.
 *
 * @since 2.4.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Confirmation_Helper' ) ) :
	/**
	 * Form Confirmation helper class.
	 *
	 * Settings shape (global option + per-form attribute):
	 *  - type          string  'success' | 'redirect'
	 *  - successMessage string Confirmation message HTML.
	 *  - errorMessage  string  Error message HTML.
	 *  - afterSubmit   string  'hide' | 'reset' (success type only).
	 *  - redirectType  string  'page' | 'url' (redirect type only).
	 *  - redirectPage  int     Page ID for redirectType 'page'.
	 *  - redirectUrl   string  Custom URL for redirectType 'url'.
	 * Per-form attribute additionally carries:
	 *  - enabled         bool Form-level enable toggle.
	 *  - defaultSettings bool True while the form inherits global defaults.
	 */
	class Gutena_Forms_Form_Confirmation_Helper {

		/**
		 * Option name for global form confirmation defaults.
		 */
		const OPTION_NAME = 'gutena_forms__form_confirmation';

		/**
		 * Get global default settings.
		 *
		 * @return array
		 */
		public static function get_global_defaults() {
			return array(
				'type'           => 'success',
				'successMessage' => '<p class="has-text-align-center"><span class="gutena-forms-confirmation-icon" aria-hidden="true">&#10003;</span></p><p class="has-text-align-center"><strong>' . __( 'Thank you', 'gutena-forms' ) . '</strong></p><p class="has-text-align-center">' . __( "Your form has been submitted successfully. We'll review your details and get back to you soon.", 'gutena-forms' ) . '</p>',
				'errorMessage'   => '<p class="has-text-align-center">' . __( 'Something went wrong while submitting the form. Please check your entries and try again.', 'gutena-forms' ) . '</p>',
				'afterSubmit'    => 'hide',
				'redirectType'   => 'page',
				'redirectPage'   => 0,
				'redirectUrl'    => '',
			);
		}

		/**
		 * Get saved global settings merged with defaults.
		 *
		 * @return array
		 */
		public static function get_global_settings() {
			$settings = get_option( self::OPTION_NAME, array() );
			if ( ! is_array( $settings ) ) {
				$settings = array();
			}
			return wp_parse_args( $settings, self::get_global_defaults() );
		}

		/**
		 * Sanitize a confirmation message (HTML allowed, kses post).
		 *
		 * @param string $message Raw message.
		 * @return string
		 */
		public static function sanitize_message( $message ) {
			return wp_kses( (string) $message, wp_kses_allowed_html( 'post' ) );
		}

		/**
		 * Validate and sanitize a redirect URL.
		 * Only http/https absolute URLs or root-relative paths are allowed.
		 * Unsafe URLs (javascript:, data:, malformed) return an empty string.
		 *
		 * @param string $url Raw URL.
		 * @return string Safe URL or '' when unsafe/invalid.
		 */
		public static function validate_redirect_url( $url ) {
			$url = trim( (string) $url );
			if ( '' === $url ) {
				return '';
			}

			// Root-relative URLs are safe; make them absolute for validation.
			if ( '/' === $url[0] ) {
				$url = home_url( $url );
			}

			$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
			if ( ! in_array( $scheme, array( 'http', 'https' ), true ) ) {
				return '';
			}

			$escaped = esc_url_raw( $url );
			if ( '' === $escaped || ! wp_http_validate_url( $escaped ) ) {
				return '';
			}

			return $escaped;
		}

		/**
		 * Sanitize global settings before saving.
		 * Strips the per-form keys (enabled / defaultSettings).
		 *
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_global_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();
			$defaults = self::get_global_defaults();

			$type         = ( isset( $settings['type'] ) && 'redirect' === $settings['type'] ) ? 'redirect' : 'success';
			$after_submit = ( isset( $settings['afterSubmit'] ) && 'reset' === $settings['afterSubmit'] ) ? 'reset' : 'hide';
			$redirect_type = ( isset( $settings['redirectType'] ) && 'url' === $settings['redirectType'] ) ? 'url' : 'page';

			$success_message = isset( $settings['successMessage'] ) ? self::sanitize_message( $settings['successMessage'] ) : '';
			$error_message   = isset( $settings['errorMessage'] ) ? self::sanitize_message( $settings['errorMessage'] ) : '';

			return array(
				'type'           => $type,
				'successMessage' => '' !== trim( $success_message ) ? $success_message : $defaults['successMessage'],
				'errorMessage'   => '' !== trim( $error_message ) ? $error_message : $defaults['errorMessage'],
				'afterSubmit'    => $after_submit,
				'redirectType'   => $redirect_type,
				'redirectPage'   => isset( $settings['redirectPage'] ) ? absint( $settings['redirectPage'] ) : 0,
				'redirectUrl'    => isset( $settings['redirectUrl'] ) ? self::validate_redirect_url( $settings['redirectUrl'] ) : '',
			);
		}

		/**
		 * Get effective confirmation settings for a form.
		 *
		 * Resolution order:
		 *  1. formConfirmation.enabled false/absent  -> legacy behavior (enabled=false).
		 *  2. enabled + defaultSettings truthy        -> global settings (live inheritance).
		 *  3. enabled + customized (defaultSettings false) -> per-form values over global defaults.
		 *
		 * @param array $schema Form schema.
		 * @return array Effective settings with 'enabled' key.
		 */
		public static function get_effective_confirmation( $schema ) {
			$attrs = ( is_array( $schema ) && ! empty( $schema['form_attrs'] ) && is_array( $schema['form_attrs'] ) )
				? $schema['form_attrs']
				: array();

			$fc = ( ! empty( $attrs['formConfirmation'] ) && is_array( $attrs['formConfirmation'] ) )
				? $attrs['formConfirmation']
				: array();

			if ( empty( $fc['enabled'] ) ) {
				return array( 'enabled' => false );
			}

			$global     = self::get_global_settings();
			$use_global = ! array_key_exists( 'defaultSettings', $fc ) || rest_sanitize_boolean( $fc['defaultSettings'] );

			if ( $use_global ) {
				$effective = $global;
			} else {
				$per_form  = array_intersect_key( $fc, $global );
				$effective = wp_parse_args( $per_form, $global );
			}

			$effective['enabled'] = true;

			return $effective;
		}

		/**
		 * Resolve the final redirect URL for effective settings.
		 *
		 * @param array $settings Effective confirmation settings.
		 * @return string Safe redirect URL or '' when not usable.
		 */
		public static function get_redirect_url( $settings ) {
			if ( empty( $settings['enabled'] ) || empty( $settings['type'] ) || 'redirect' !== $settings['type'] ) {
				return '';
			}

			if ( 'url' === ( isset( $settings['redirectType'] ) ? $settings['redirectType'] : 'page' ) ) {
				return self::validate_redirect_url( isset( $settings['redirectUrl'] ) ? $settings['redirectUrl'] : '' );
			}

			$page_id = ! empty( $settings['redirectPage'] ) ? absint( $settings['redirectPage'] ) : 0;
			if ( 0 === $page_id || 'publish' !== get_post_status( $page_id ) ) {
				return '';
			}

			$permalink = get_permalink( $page_id );
			return is_string( $permalink ) ? esc_url_raw( $permalink ) : '';
		}

		/**
		 * Resolve merge tags in a confirmation message and run shortcodes.
		 * Unresolved merge tags are replaced with empty values.
		 *
		 * @param string $message          Message template.
		 * @param array  $form_submit_data Form submission data.
		 * @param array  $schema           Full form schema.
		 * @return string Resolved, sanitized HTML.
		 */
		public static function resolve_message( $message, $form_submit_data, $schema ) {
			$message = (string) $message;
			if ( '' === $message ) {
				return '';
			}

			// Merge tags (shared with email notifications).
			if ( class_exists( 'Gutena_Forms_Email_Notifications_Helper' ) ) {
				$message = Gutena_Forms_Email_Notifications_Helper::replace_merge_tags( $message, is_array( $form_submit_data ) ? $form_submit_data : array(), is_array( $schema ) ? $schema : array() );

				// Unresolved tags resolve to empty values.
				$message = preg_replace( '/\{[^{}\n]{1,100}\}/', '', $message );
			}

			// Shortcode support.
			if ( str_contains( $message, '[' ) && function_exists( 'do_shortcode' ) ) {
				$message = do_shortcode( $message );
			}

			// Sanitize for output.
			$message = self::sanitize_message( $message );

			return apply_filters( 'gutena_forms_form_confirmation_message', $message, $form_submit_data, $schema );
		}

		/**
		 * Build the frontend confirmation payload for a successful submission.
		 *
		 * Returns null when Form Confirmation is disabled for the form
		 * (legacy behavior applies). When the redirect target is missing or
		 * invalid, the payload safely falls back to the success message flow.
		 *
		 * @param array $form_submit_data Form submission data.
		 * @param array $schema           Full form schema.
		 * @return array|null
		 */
		public static function get_confirmation_payload( $form_submit_data, $schema ) {
			$effective = self::get_effective_confirmation( $schema );
			if ( empty( $effective['enabled'] ) ) {
				return null;
			}

			$after_submit = ( isset( $effective['afterSubmit'] ) && 'reset' === $effective['afterSubmit'] ) ? 'reset' : 'hide';

			$payload = array(
				'type'         => 'success',
				'message'      => '',
				'after_submit' => $after_submit,
				'redirect_url' => '',
			);

			if ( 'redirect' === ( isset( $effective['type'] ) ? $effective['type'] : 'success' ) ) {
				$redirect_url = self::get_redirect_url( $effective );
				if ( ! empty( $redirect_url ) ) {
					$payload['type']         = 'redirect';
					$payload['redirect_url'] = $redirect_url;
					return $payload;
				}

				// Missing or invalid redirect: stay on the page with a safe
				// success message fallback.
				$fallback           = self::resolve_message( $effective['successMessage'], $form_submit_data, $schema );
				$payload['message'] = '' !== trim( wp_strip_all_tags( $fallback ) )
					? $fallback
					: self::resolve_message( self::get_global_defaults()['successMessage'], $form_submit_data, $schema );

				return $payload;
			}

			$message           = self::resolve_message( $effective['successMessage'], $form_submit_data, $schema );
			$payload['message'] = '' !== trim( wp_strip_all_tags( $message ) )
				? $message
				: self::resolve_message( self::get_global_defaults()['successMessage'], $form_submit_data, $schema );

			return $payload;
		}

		/**
		 * Get the configured error message for failed submissions.
		 * Empty string when Form Confirmation is disabled or no message set.
		 *
		 * @param array $schema           Full form schema.
		 * @param array $form_submit_data Form submission data (may be empty).
		 * @return string
		 */
		public static function get_confirmation_error_message( $schema, $form_submit_data = array() ) {
			$effective = self::get_effective_confirmation( $schema );
			if ( empty( $effective['enabled'] ) ) {
				return '';
			}

			$error = self::resolve_message( isset( $effective['errorMessage'] ) ? $effective['errorMessage'] : '', $form_submit_data, $schema );

			return '' !== trim( wp_strip_all_tags( $error ) ) ? $error : '';
		}

		/**
		 * Get merge tags available for confirmation messages (admin UI).
		 *
		 * @return array
		 */
		public static function get_static_merge_tags() {
			return array(
				'{site_name}',
				'{site_url}',
				'{submission_date}',
				'{form_title}',
				'{user_email}',
				'{user_name}',
				'{First Name}',
				'{Last Name}',
				'{Email}',
				'{all_data}',
			);
		}
	}
endif;
