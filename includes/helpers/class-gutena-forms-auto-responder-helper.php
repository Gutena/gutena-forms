<?php
/**
 * Auto Responder helper: merge tags and user confirmation emails.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Auto_Responder_Helper' ) ) :
	/**
	 * Auto Responder helper class.
	 */
	class Gutena_Forms_Auto_Responder_Helper {

		/**
		 * Option name for global auto-responder settings.
		 */
		const OPTION_NAME = 'gutena_forms__auto_responder';

		/**
		 * Default settings.
		 *
		 * @return array
		 */
		public static function get_defaults() {
			return array(
				'enable'  => false,
				'subject' => __( 'Thankyou for your submission', 'gutena-forms' ),
				'message' => __( "Thankyou for your submission!\n\nDear {Name},\n\nThank you for contacting us through our contact form. We have received your message and will get back to you as soon as possible.", 'gutena-forms' ),
			);
		}

		/**
		 * Get saved auto-responder settings merged with defaults.
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
		 * Whether auto-responder is enabled.
		 *
		 * @return bool
		 */
		public static function is_enabled() {
			$settings = self::get_settings();
			return ! empty( $settings['enable'] );
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
				'{admin_email}',
			);
		}

		/**
		 * Resolve submitter email from form data.
		 *
		 * @param array $form_submit_data Form submission data.
		 * @param array $field_schema     Field schema.
		 * @param array $schema           Full form schema.
		 * @return string
		 */
		public static function get_submitter_email( $form_submit_data, $field_schema, $schema ) {
			$reply_to_field = empty( $schema['form_attrs']['replyToEmail'] ) ? '' : $schema['form_attrs']['replyToEmail'];

			if ( ! empty( $reply_to_field ) && ! empty( $form_submit_data['raw_data'][ $reply_to_field ]['value'] ) ) {
				$email = sanitize_email( $form_submit_data['raw_data'][ $reply_to_field ]['value'] );
				if ( is_email( $email ) ) {
					return $email;
				}
			}

			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
				if ( empty( $field_schema[ $name_attr ] ) ) {
					continue;
				}

				$field_type = empty( $field_schema[ $name_attr ]['fieldType'] ) ? 'text' : $field_schema[ $name_attr ]['fieldType'];
				if ( 'email' !== $field_type ) {
					continue;
				}

				$email = sanitize_email( $field_data['value'] );
				if ( is_email( $email ) ) {
					return $email;
				}
			}

			return '';
		}

		/**
		 * Replace merge tags in subject or message.
		 *
		 * @param string $text             Template text.
		 * @param array  $form_submit_data Form submission data.
		 * @param array  $schema           Full form schema.
		 * @return string
		 */
		public static function replace_merge_tags( $text, $form_submit_data, $schema ) {
			if ( '' === $text ) {
				return '';
			}

			$form_name = self::get_form_title( $form_submit_data, $schema );
			$site_url  = get_site_url();
			$date      = function_exists( 'wp_date' )
				? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
				: gmdate( 'Y-m-d H:i:s' );

			$replacements = array(
				'{site_name}'       => get_bloginfo( 'name' ),
				'{site_url}'        => $site_url,
				'{submission_date}' => $date,
				'{form-title}'      => $form_name,
				'{form_title}'      => $form_name,
				'{admin_email}'     => sanitize_email( get_option( 'admin_email' ) ),
			);

			if ( ! empty( $form_submit_data['raw_data'] ) && is_array( $form_submit_data['raw_data'] ) ) {
				foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
					$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
					$value = empty( $field_data['value'] ) ? '' : $field_data['value'];

					$replacements[ '{' . $label . '}' ]         = $value;
					$replacements[ '{' . sanitize_key( $label ) . '}' ] = $value;
					$replacements[ '{' . $name_attr . '}' ]     = $value;
					$replacements[ '{' . ucfirst( sanitize_key( $name_attr ) ) . '}' ] = $value;
				}
			}

			$first_name = self::get_submitter_first_name( $form_submit_data, $schema );
			if ( '' !== $first_name ) {
				$replacements['{Name}'] = $first_name;
				$replacements['{name}'] = $first_name;
			}

			$replacements = apply_filters( 'gutena_forms_auto_responder_merge_tags', $replacements, $form_submit_data, $schema );

			return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
		}

		/**
		 * Resolve form title for {form-title} merge tag.
		 *
		 * @param array $form_submit_data Form submission data.
		 * @param array $schema           Full form schema.
		 * @return string
		 */
		public static function get_form_title( $form_submit_data, $schema ) {
			$form_id = empty( $form_submit_data['formID'] ) ? '' : $form_submit_data['formID'];

			if ( empty( $form_id ) && ! empty( $schema['form_attrs']['formID'] ) ) {
				$form_id = $schema['form_attrs']['formID'];
			}

			if ( ! empty( $form_id ) ) {
				$posts = get_posts(
					array(
						'post_type'              => 'gutena_forms',
						'meta_key'               => 'gutena_form_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value'             => $form_id, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
						'posts_per_page'         => 1,
						'post_status'            => 'any',
						'fields'                 => 'ids',
						'no_found_rows'          => true,
						'update_post_meta_cache' => false,
						'update_post_term_cache' => false,
						'suppress_filters'       => true,
					)
				);

				if ( ! empty( $posts[0] ) ) {
					$post_title = sanitize_text_field( get_the_title( $posts[0] ) );
					if ( '' !== $post_title ) {
						return $post_title;
					}
				}
			}

			$form_name = empty( $form_submit_data['formName'] ) ? '' : sanitize_text_field( $form_submit_data['formName'] );
			if ( '' !== $form_name && 0 !== strcasecmp( $form_name, 'Contact Form' ) ) {
				return $form_name;
			}

			$form_name = empty( $schema['form_attrs']['formName'] ) ? '' : sanitize_text_field( $schema['form_attrs']['formName'] );
			if ( '' !== $form_name && 0 !== strcasecmp( $form_name, 'Contact Form' ) ) {
				return $form_name;
			}

			return __( 'Contact Form', 'gutena-forms' );
		}

		/**
		 * Resolve submitter first name for {Name} merge tag.
		 *
		 * @param array $form_submit_data Form submission data.
		 * @param array $schema           Full form schema.
		 * @return string
		 */
		public static function get_submitter_first_name( $form_submit_data, $schema ) {
			$reply_to_field = empty( $schema['form_attrs']['replyToName'] ) ? '' : sanitize_key( $schema['form_attrs']['replyToName'] );

			if ( $reply_to_field && ! empty( $form_submit_data['raw_data'][ $reply_to_field ]['value'] ) ) {
				return sanitize_text_field( $form_submit_data['raw_data'][ $reply_to_field ]['value'] );
			}

			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			$first_name_keys = array( 'first name', 'firstname', 'first_name', 'fname' );
			$name_keys       = array( 'name' );

			foreach ( array( $first_name_keys, $name_keys ) as $patterns ) {
				foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
					$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
					$label = strtolower( trim( str_replace( array( '-', '_' ), ' ', $label ) ) );
					$attr  = strtolower( trim( str_replace( array( '-', '_' ), ' ', $name_attr ) ) );

					if ( in_array( $label, $patterns, true ) || in_array( $attr, $patterns, true ) ) {
						return empty( $field_data['value'] ) ? '' : sanitize_text_field( $field_data['value'] );
					}
				}
			}

			return '';
		}

		/**
		 * Send auto-responder email to the form submitter.
		 *
		 * @param array $form_submit_data Form submission data.
		 * @param array $schema           Full form schema.
		 * @param array $field_schema     Field schema.
		 * @return bool
		 */
		public static function send_auto_responder( $form_submit_data, $schema, $field_schema ) {
			if ( ! self::is_enabled() ) {
				return false;
			}

			$settings = self::get_settings();
			$to       = self::get_submitter_email( $form_submit_data, $field_schema, $schema );

			if ( ! is_email( $to ) ) {
				return false;
			}

			$blog_title  = get_bloginfo( 'name' );
			$admin_email = sanitize_email( get_option( 'admin_email' ) );
			$from_name   = empty( $schema['form_attrs']['emailFromName'] ) ? $blog_title : sanitize_text_field( $schema['form_attrs']['emailFromName'] );

			$subject = self::replace_merge_tags( $settings['subject'], $form_submit_data, $schema );
			$body    = self::replace_merge_tags( $settings['message'], $form_submit_data, $schema );

			$subject = sanitize_text_field( $subject );
			$body    = wpautop( wp_kses_post( $body ), true );

			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $from_name . ' <' . $admin_email . '>',
			);

			$body = apply_filters( 'gutena_forms_submit_user_notification', $body, $form_submit_data, $settings );

			$html_body = self::email_html_body( $body, $subject );

			return wp_mail( $to, $subject, $html_body, $headers );
		}

		/**
		 * Wrap email body in HTML structure.
		 *
		 * @param string $body    Email body.
		 * @param string $subject Email subject.
		 * @return string
		 */
		private static function email_html_body( $body, $subject ) {
			$lang = function_exists( 'get_language_attributes' ) ? get_language_attributes( 'html' ) : 'lang="en"';

			return '
			<!DOCTYPE html>
			<html ' . $lang . '>
				<head>
				<meta http-equiv="X-UA-Compatible" content="IE=edge">
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<title>' . esc_html( $subject ) . '</title>
				</head>
				<body style="margin:0;padding:0;background:#ffffff;">
				' . $body . '
				</body>
			</html>
			';
		}

		/**
		 * Sanitize settings before saving.
		 *
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			return array(
				'enable'  => ! empty( $settings['enable'] ),
				'subject' => isset( $settings['subject'] ) ? sanitize_text_field( $settings['subject'] ) : '',
				'message' => isset( $settings['message'] ) ? sanitize_textarea_field( $settings['message'] ) : '',
			);
		}
	}
endif;