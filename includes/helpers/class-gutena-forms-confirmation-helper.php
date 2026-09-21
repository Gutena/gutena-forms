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

			$heading = __( 'Thank you', 'gutena-forms' );
			$message = __( "Your form has been submitted successfully. We'll review your details and get back to you soon.", 'gutena-forms' );

			return sprintf(
				'<div class="gutena-forms-confirmation-message"><img src="%1$s" alt="%2$s" class="form-message-icon" width="16" height="16" /><h3>%3$s</h3><p>%4$s</p></div>',
				$icon_url,
				esc_attr__( 'Success', 'gutena-forms' ),
				esc_html( $heading ),
				esc_html( $message )
			);
		}

		/**
		 * Default error message HTML.
		 *
		 * @return string
		 */
		public static function get_default_error_message_html() {
			$message = __( 'Something went wrong while submitting the form. Please check your entries and try again.', 'gutena-forms' );

			return sprintf(
				'<p>%s</p>',
				esc_html( $message )
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
				'success_message'       => $settings['success_message'],
				'error_message'         => $settings['error_message'],
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

			if ( empty( $settings['enabled'] ) ) {
				return null;
			}

			$confirmation_type = isset( $settings['confirmationType'] ) ? sanitize_key( $settings['confirmationType'] ) : 'message';
			if ( ! in_array( $confirmation_type, array( 'message', 'redirect' ), true ) ) {
				$confirmation_type = 'message';
			}

			$after_submit = isset( $settings['afterSubmit'] ) ? sanitize_key( $settings['afterSubmit'] ) : 'hide';
			if ( ! in_array( $after_submit, array( 'hide', 'reset' ), true ) ) {
				$after_submit = 'hide';
			}

			$redirect_type = isset( $settings['redirectType'] ) ? sanitize_key( $settings['redirectType'] ) : 'page';
			if ( ! in_array( $redirect_type, array( 'page', 'custom_url' ), true ) ) {
				$redirect_type = 'page';
			}

			$redirect_page_id = self::sanitize_redirect_page_id( $settings['redirectPageId'] ?? 0 );
			$redirect_url     = self::sanitize_redirect_url( $settings['redirectUrl'] ?? '' );

			$resolved_redirect_url = self::resolve_redirect_url(
				array(
					'confirmation_type' => $confirmation_type,
					'redirect_type'       => $redirect_type,
					'redirect_page_id'    => $redirect_page_id,
					'redirect_url'        => $redirect_url,
				)
			);

			return array(
				'enabled'             => true,
				'confirmationType'    => $confirmation_type,
				'successMessage'      => self::sanitize_confirmation_html( $settings['successMessage'] ?? '' ),
				'errorMessage'        => self::sanitize_confirmation_html( $settings['errorMessage'] ?? '' ),
				'afterSubmit'         => $after_submit,
				'redirectType'        => $redirect_type,
				'redirectPageId'      => $redirect_page_id,
				'redirectUrl'         => $redirect_url,
				'resolvedRedirectUrl' => $resolved_redirect_url,
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
