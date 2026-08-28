<?php
/**
 * Email Notifications helper: merge tags, recipient resolution, and email sending.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Notifications_Helper' ) ) :
	/**
	 * Email Notifications helper class.
	 */
	class Gutena_Forms_Email_Notifications_Helper {

		/**
		 * Option name for global email notification defaults.
		 */
		const OPTION_NAME = 'gutena_forms__email_notifications';

		/**
		 * Get global default settings.
		 *
		 * @return array
		 */
		public static function get_global_defaults() {
			return array(
				'send_to'            => '',
				'subject'            => 'New Form Submission - {form_title}',
				'message'            => '',
				'from_name'          => '',
				'from_email'         => '',
				'cc'                 => '',
				'bcc'                => '',
				'reply_to'           => '',
				'reply_to_first_name' => '',
				'reply_to_last_name'  => '',
			);
		}

		/**
		 * Get a single notification defaults array.
		 *
		 * @return array
		 */
		public static function get_notification_defaults() {
			return array(
				'id'                  => '',
				'enabled'             => true,
				'name'                => __( 'Admin Notification Email', 'gutena-forms' ),
				'send_to'             => '',
				'subject'             => 'New Form Submission - {form_title}',
				'message'             => '',
				'from_name'           => '',
				'from_email'          => '',
				'cc'                  => '',
				'bcc'                 => '',
				'reply_to'            => '',
				'reply_to_first_name' => '',
				'reply_to_last_name'  => '',
			);
		}

		/**
		 * Get saved global email notification settings merged with defaults.
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
		 * Get effective notifications for a form (per-form or inherited from global).
		 *
		 * @param array $schema Form schema.
		 * @return array List of notification objects.
		 */
		public static function get_effective_notifications( $schema ) {
			$attrs = is_array( $schema ) && ! empty( $schema['form_attrs'] ) ? $schema['form_attrs'] : array();
			$en    = isset( $attrs['emailNotifications'] ) && is_array( $attrs['emailNotifications'] ) ? $attrs['emailNotifications'] : array();

			// Check if form uses global defaults.
			$use_global = empty( $en ) || ( isset( $en['defaultSettings'] ) && rest_sanitize_boolean( $en['defaultSettings'] ) );

			if ( $use_global ) {
				$global = self::get_global_settings();
				// Build a single notification from global defaults.
				return array(
					wp_parse_args(
						array(
							'id'      => 'global_default',
							'enabled' => true,
							'name'    => __( 'Admin Notification Email', 'gutena-forms' ),
						),
						$global
					),
				);
			}

			$notifications = isset( $en['notifications'] ) && is_array( $en['notifications'] ) ? $en['notifications'] : array();

			// Merge each notification with global defaults for missing fields.
			$global = self::get_global_settings();
			$result = array();
			foreach ( $notifications as $notification ) {
				$merged = wp_parse_args( $notification, $global );
				if ( empty( $merged['id'] ) ) {
					$merged['id'] = 'notif_' . wp_generate_password( 8, false );
				}
				$result[] = $merged;
			}

			return $result;
		}

		/**
		 * Resolve a merge-tag-aware email field value.
		 *
		 * Supports: {admin_email}, {user_email}, {field:FIELD_NAME}
		 *
		 * @param string $value             Raw value.
		 * @param array  $form_submit_data  Form submission data.
		 * @param array  $field_schema      Field schema.
		 * @return string Resolved email.
		 */
		public static function resolve_email_field( $value, $form_submit_data, $field_schema ) {
			$value = trim( $value );
			if ( '' === $value ) {
				return '';
			}

			// Static generic tags.
			if ( '{admin_email}' === $value ) {
				return sanitize_email( get_option( 'admin_email' ) );
			}

			if ( '{user_email}' === $value ) {
				return self::get_submitter_email( $form_submit_data, $field_schema );
			}

			// {field:FIELD_NAME} pattern.
			if ( preg_match( '/^\{field:(.+)\}$/i', $value, $matches ) ) {
				$field_name = $matches[1];
				$value      = self::get_field_value_by_name( $field_name, $form_submit_data, $field_schema );
				return sanitize_email( $value );
			}

			// Check if it contains a merge tag that resolves to an email.
			$resolved = self::replace_merge_tags( $value, $form_submit_data, array() );
			if ( is_email( $resolved ) ) {
				return $resolved;
			}

			// Plain email.
			return sanitize_email( $value );
		}

		/**
		 * Get field value by name attribute or label.
		 *
		 * @param string $field_name        Field name or label.
		 * @param array  $form_submit_data  Form submission data.
		 * @param array  $field_schema      Field schema.
		 * @return string
		 */
		public static function get_field_value_by_name( $field_name, $form_submit_data, $field_schema ) {
			// Try direct name_attr match.
			if ( ! empty( $form_submit_data['raw_data'][ $field_name ]['value'] ) ) {
				return $form_submit_data['raw_data'][ $field_name ]['value'];
			}

			// Try label match.
			if ( ! empty( $form_submit_data['raw_data'] ) && is_array( $form_submit_data['raw_data'] ) ) {
				foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
					$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
					if ( 0 === strcasecmp( $label, $field_name ) || 0 === strcasecmp( $name_attr, $field_name ) ) {
						return empty( $field_data['value'] ) ? '' : $field_data['value'];
					}
				}
			}

			return '';
		}

		/**
		 * Resolve submitter email from form data.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @param array $field_schema      Field schema.
		 * @return string
		 */
		public static function get_submitter_email( $form_submit_data, $field_schema ) {
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
		 * Resolve submitter first name.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @param array $schema            Full form schema.
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
		 * Resolve submitter last name.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @return string
		 */
		public static function get_submitter_last_name( $form_submit_data ) {
			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			$last_name_keys = array( 'last name', 'lastname', 'last_name', 'lname' );

			foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
				$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
				$label = strtolower( trim( str_replace( array( '-', '_' ), ' ', $label ) ) );
				$attr  = strtolower( trim( str_replace( array( '-', '_' ), ' ', $name_attr ) ) );

				if ( in_array( $label, $last_name_keys, true ) || in_array( $attr, $last_name_keys, true ) ) {
					return empty( $field_data['value'] ) ? '' : sanitize_text_field( $field_data['value'] );
				}
			}

			return '';
		}

		/**
		 * Get form title for merge tags.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @param array $schema            Full form schema.
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
		 * Build the {all_data} tag value from submitted form data.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @return string HTML table of all submitted data.
		 */
		public static function build_all_data( $form_submit_data ) {
			if ( empty( $form_submit_data['submit_data'] ) || ! is_array( $form_submit_data['submit_data'] ) ) {
				return '';
			}

			$html = '<table style="width:100%;border-collapse:collapse;">';
			foreach ( $form_submit_data['submit_data'] as $label => $value ) {
				$html .= '<tr>';
				$html .= '<td style="padding:8px;border:1px solid #ddd;font-weight:bold;">' . esc_html( $label ) . '</td>';
				$html .= '<td style="padding:8px;border:1px solid #ddd;">' . esc_html( $value ) . '</td>';
				$html .= '</tr>';
			}
			$html .= '</table>';

			return $html;
		}

		/**
		 * Replace merge tags in subject or message.
		 *
		 * @param string $text               Template text.
		 * @param array  $form_submit_data   Form submission data.
		 * @param array  $schema             Full form schema.
		 * @param array  $notification       Current notification being processed.
		 * @return string
		 */
		public static function replace_merge_tags( $text, $form_submit_data, $schema, $notification = array() ) {
			if ( '' === $text ) {
				return '';
			}

			$form_name = self::get_form_title( $form_submit_data, $schema );
			$site_url  = get_site_url();
			$date      = function_exists( 'wp_date' )
				? wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) )
				: gmdate( 'Y-m-d H:i:s' );

			$first_name = self::get_submitter_first_name( $form_submit_data, $schema );
			$last_name  = self::get_submitter_last_name( $form_submit_data );
			$user_email = self::get_submitter_email( $form_submit_data, ! empty( $schema['form_fields'] ) ? $schema['form_fields'] : array() );

			$replacements = array(
				'{site_name}'        => get_bloginfo( 'name' ),
				'{site_url}'         => $site_url,
				'{submission_date}'  => $date,
				'{form-title}'       => $form_name,
				'{form_title}'       => $form_name,
				'{admin_email}'      => sanitize_email( get_option( 'admin_email' ) ),
				'{user_email}'       => $user_email,
				'{user_name}'        => trim( $first_name . ' ' . $last_name ),
				'{First Name}'       => $first_name,
				'{Last Name}'        => $last_name,
				'{Email}'            => $user_email,
			);

			// All submitted field values by label and name_attr.
			if ( ! empty( $form_submit_data['raw_data'] ) && is_array( $form_submit_data['raw_data'] ) ) {
				foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
					$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
					$value = empty( $field_data['value'] ) ? '' : $field_data['value'];

					$replacements[ '{' . $label . '}' ]                  = $value;
					$replacements[ '{' . sanitize_key( $label ) . '}' ]  = $value;
					$replacements[ '{' . $name_attr . '}' ]              = $value;
					$replacements[ '{' . ucfirst( sanitize_key( $name_attr ) ) . '}' ] = $value;
				}
			}

			// {all_data} tag — only for message body.
			$replacements['{all_data}'] = self::build_all_data( $form_submit_data );

			$replacements = apply_filters( 'gutena_forms_email_notifications_merge_tags', $replacements, $form_submit_data, $schema, $notification );

			return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
		}

		/**
		 * Send all enabled notifications for a form submission.
		 *
		 * @param array $form_submit_data  Form submission data.
		 * @param array $schema            Full form schema.
		 * @param array $field_schema      Field schema.
		 * @return int Number of emails sent successfully.
		 */
		public static function send_notifications( $form_submit_data, $schema, $field_schema ) {
			$notifications = self::get_effective_notifications( $schema );
			$sent          = 0;

			foreach ( $notifications as $notification ) {
				if ( empty( $notification['enabled'] ) ) {
					continue;
				}

				if ( self::send_single_notification( $notification, $form_submit_data, $schema, $field_schema ) ) {
					++$sent;
				}
			}

			return $sent;
		}

		/**
		 * Send a single notification email.
		 *
		 * @param array $notification       Notification config.
		 * @param array $form_submit_data   Form submission data.
		 * @param array $schema             Full form schema.
		 * @param array $field_schema       Field schema.
		 * @return bool
		 */
		private static function send_single_notification( $notification, $form_submit_data, $schema, $field_schema ) {
			$to = self::resolve_email_field( $notification['send_to'], $form_submit_data, $field_schema );

			if ( ! is_email( $to ) ) {
				return false;
			}

			$blog_title  = get_bloginfo( 'name' );
			$admin_email = sanitize_email( get_option( 'admin_email' ) );

			// From name.
			$from_name = empty( $notification['from_name'] ) ? $blog_title : $notification['from_name'];
			$from_name = self::replace_merge_tags( $from_name, $form_submit_data, $schema, $notification );
			$from_name = sanitize_text_field( $from_name );

			// From email.
			$from_email = self::resolve_email_field( $notification['from_email'], $form_submit_data, $field_schema );
			if ( ! is_email( $from_email ) ) {
				$from_email = $admin_email;
			}

			// Subject.
			$subject = self::replace_merge_tags( $notification['subject'], $form_submit_data, $schema, $notification );
			$subject = sanitize_text_field( $subject );

			// Body.
			$body = self::replace_merge_tags( $notification['message'], $form_submit_data, $schema, $notification );
			$body = wpautop( wp_kses_post( $body ), true );

			// Headers.
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $from_name . ' <' . $from_email . '>',
			);

			// CC.
			if ( ! empty( $notification['cc'] ) ) {
				$cc = self::resolve_email_field( $notification['cc'], $form_submit_data, $field_schema );
				if ( '' !== $cc ) {
					$headers[] = 'Cc: ' . $cc;
				}
			}

			// BCC.
			if ( ! empty( $notification['bcc'] ) ) {
				$bcc = self::resolve_email_field( $notification['bcc'], $form_submit_data, $field_schema );
				if ( '' !== $bcc ) {
					$headers[] = 'Bcc: ' . $bcc;
				}
			}

			// Reply-To.
			$reply_to_email = '';
			if ( ! empty( $notification['reply_to'] ) ) {
				$reply_to_email = self::resolve_email_field( $notification['reply_to'], $form_submit_data, $field_schema );
			}

			$reply_to_name = '';
			if ( ! empty( $notification['reply_to_first_name'] ) ) {
				$reply_to_name = self::get_field_value_by_name( $notification['reply_to_first_name'], $form_submit_data, $field_schema );
				$reply_to_name = sanitize_text_field( $reply_to_name );
			}
			if ( ! empty( $notification['reply_to_last_name'] ) ) {
				$last = self::get_field_value_by_name( $notification['reply_to_last_name'], $form_submit_data, $field_schema );
				$last = sanitize_text_field( $last );
				$reply_to_name = trim( $reply_to_name . ' ' . $last );
			}

			if ( is_email( $reply_to_email ) ) {
				$rt = ! empty( $reply_to_name ) ? $reply_to_name . ' <' . $reply_to_email . '>' : $reply_to_email;
				$headers[] = 'Reply-To: ' . $rt;
			}

			$body = apply_filters( 'gutena_forms_email_notifications_body', $body, $form_submit_data, $notification );

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
		 * Get static merge tags for admin UI.
		 *
		 * @return array
		 */
		public static function get_static_merge_tags() {
			return array(
				'{site_name}',
				'{site_url}',
				'{submission_date}',
				'{form_title}',
				'{admin_email}',
				'{user_email}',
				'{user_name}',
			);
		}

		/**
		 * Get merge tags available for email address fields.
		 *
		 * @return array
		 */
		public static function get_email_field_merge_tags() {
			return array(
				'{admin_email}',
				'{user_email}',
			);
		}

		/**
		 * Get merge tags available for subject/message/name fields.
		 *
		 * @param array $field_schema Field schema.
		 * @return array
		 */
		public static function get_content_merge_tags( $field_schema = array() ) {
			$tags = array(
				'{site_name}',
				'{site_url}',
				'{submission_date}',
				'{form_title}',
				'{admin_email}',
				'{user_email}',
				'{user_name}',
				'{First Name}',
				'{Last Name}',
				'{Email}',
			);

			// Add per-field tags.
			if ( ! empty( $field_schema ) && is_array( $field_schema ) ) {
				foreach ( $field_schema as $name_attr => $field ) {
					$label = empty( $field['fieldName'] ) ? $name_attr : $field['fieldName'];
					$tags[] = '{' . $label . '}';
				}
			}

			$tags[] = '{all_data}';

			return $tags;
		}

		/**
		 * Sanitize a single notification.
		 *
		 * @param array $notification Raw notification.
		 * @return array
		 */
		public static function sanitize_notification( $notification ) {
			$notification = is_array( $notification ) ? $notification : array();
			$defaults     = self::get_notification_defaults();

			return array(
				'id'                  => isset( $notification['id'] ) ? sanitize_key( $notification['id'] ) : $defaults['id'],
				'enabled'             => ! empty( $notification['enabled'] ),
				'name'                => isset( $notification['name'] ) ? sanitize_text_field( $notification['name'] ) : $defaults['name'],
				'send_to'             => isset( $notification['send_to'] ) ? sanitize_text_field( $notification['send_to'] ) : $defaults['send_to'],
				'subject'             => isset( $notification['subject'] ) ? sanitize_text_field( $notification['subject'] ) : $defaults['subject'],
				'message'             => isset( $notification['message'] ) ? sanitize_textarea_field( $notification['message'] ) : $defaults['message'],
				'from_name'           => isset( $notification['from_name'] ) ? sanitize_text_field( $notification['from_name'] ) : $defaults['from_name'],
				'from_email'          => isset( $notification['from_email'] ) ? sanitize_text_field( $notification['from_email'] ) : $defaults['from_email'],
				'cc'                  => isset( $notification['cc'] ) ? sanitize_text_field( $notification['cc'] ) : $defaults['cc'],
				'bcc'                 => isset( $notification['bcc'] ) ? sanitize_text_field( $notification['bcc'] ) : $defaults['bcc'],
				'reply_to'            => isset( $notification['reply_to'] ) ? sanitize_text_field( $notification['reply_to'] ) : $defaults['reply_to'],
				'reply_to_first_name' => isset( $notification['reply_to_first_name'] ) ? sanitize_text_field( $notification['reply_to_first_name'] ) : $defaults['reply_to_first_name'],
				'reply_to_last_name'  => isset( $notification['reply_to_last_name'] ) ? sanitize_text_field( $notification['reply_to_last_name'] ) : $defaults['reply_to_last_name'],
			);
		}

		/**
		 * Sanitize global settings before saving.
		 *
		 * @param array $settings Raw settings.
		 * @return array
		 */
		public static function sanitize_global_settings( $settings ) {
			$settings = is_array( $settings ) ? $settings : array();

			return array(
				'send_to'            => isset( $settings['send_to'] ) ? sanitize_text_field( $settings['send_to'] ) : '',
				'subject'            => isset( $settings['subject'] ) ? sanitize_text_field( $settings['subject'] ) : '',
				'message'            => isset( $settings['message'] ) ? sanitize_textarea_field( $settings['message'] ) : '',
				'from_name'          => isset( $settings['from_name'] ) ? sanitize_text_field( $settings['from_name'] ) : '',
				'from_email'         => isset( $settings['from_email'] ) ? sanitize_text_field( $settings['from_email'] ) : '',
				'cc'                 => isset( $settings['cc'] ) ? sanitize_text_field( $settings['cc'] ) : '',
				'bcc'                => isset( $settings['bcc'] ) ? sanitize_text_field( $settings['bcc'] ) : '',
				'reply_to'           => isset( $settings['reply_to'] ) ? sanitize_text_field( $settings['reply_to'] ) : '',
				'reply_to_first_name' => isset( $settings['reply_to_first_name'] ) ? sanitize_text_field( $settings['reply_to_first_name'] ) : '',
				'reply_to_last_name'  => isset( $settings['reply_to_last_name'] ) ? sanitize_text_field( $settings['reply_to_last_name'] ) : '',
			);
		}

		/**
		 * Migrate old auto-responder settings to new format.
		 *
		 * @return bool True if migration happened.
		 */
		public static function migrate_old_settings() {
			$old = get_option( 'gutena_forms__auto_responder', null );
			if ( null === $old || ! is_array( $old ) ) {
				return false;
			}

			$new = self::get_global_defaults();
			if ( ! empty( $old['subject'] ) ) {
				$new['subject'] = sanitize_text_field( $old['subject'] );
			}
			if ( ! empty( $old['message'] ) ) {
				$new['message'] = sanitize_textarea_field( $old['message'] );
			}

			update_option( self::OPTION_NAME, $new );
			delete_option( 'gutena_forms__auto_responder' );

			return true;
		}
	}
endif;
