<?php
/**
 * Per-form email notification sending helper.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Notification_Helper' ) ) :
	/**
	 * Sends configured per-form email notifications on submission.
	 */
	class Gutena_Forms_Notification_Helper {

		/**
		 * Send all enabled notifications for a form submission.
		 *
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Full form schema.
		 * @param array  $field_schema     Field schema.
		 * @param string $default_body     Legacy auto-generated HTML body fallback.
		 * @return bool True when at least one notification was sent.
		 */
		public static function send_form_notifications( $form_submit_data, $schema, $field_schema, $default_body = '' ) {
			if ( ! self::is_notifications_enabled( $schema ) ) {
				return false;
			}

			list( $notifications, $form_meta, $global_defaults ) = self::resolve_notification_context( $schema );

			if ( empty( $notifications ) || ! is_array( $notifications ) ) {
				return false;
			}

			$sent_any = false;

			foreach ( $notifications as $notification ) {
				if ( empty( $notification ) || ! is_array( $notification ) ) {
					continue;
				}

				if ( isset( $notification['enabled'] ) && ! rest_sanitize_boolean( $notification['enabled'] ) ) {
					continue;
				}

				if ( self::send_single_notification( $notification, $form_submit_data, $schema, $field_schema, $default_body, $form_meta, $global_defaults ) ) {
					$sent_any = true;
				}
			}

			return $sent_any;
		}

		/**
		 * Whether form-level email notifications are enabled.
		 *
		 * @param array $schema Form schema.
		 * @return bool
		 */
		private static function is_notifications_enabled( $schema ) {
			$attrs          = isset( $schema['form_attrs'] ) && is_array( $schema['form_attrs'] ) ? $schema['form_attrs'] : array();
			$email_settings = isset( $attrs['settings']['emailNotifications'] ) && is_array( $attrs['settings']['emailNotifications'] )
				? $attrs['settings']['emailNotifications']
				: array();

			if ( array_key_exists( 'enabled', $email_settings ) ) {
				return rest_sanitize_boolean( $email_settings['enabled'] );
			}

			$email_notify = isset( $attrs['emailNotifyAdmin'] ) ? $attrs['emailNotifyAdmin'] : true;

			return ! ( '' === $email_notify || false === $email_notify || '0' === $email_notify || 0 === $email_notify );
		}

		/**
		 * Resolve notifications list and fallback defaults.
		 *
		 * @param array $schema Form schema.
		 * @return array
		 */
		private static function resolve_notification_context( $schema ) {
			$attrs          = isset( $schema['form_attrs'] ) && is_array( $schema['form_attrs'] ) ? $schema['form_attrs'] : array();
			$settings       = isset( $attrs['settings'] ) && is_array( $attrs['settings'] ) ? $attrs['settings'] : array();
			$email_settings = isset( $settings['emailNotifications'] ) && is_array( $settings['emailNotifications'] )
				? $settings['emailNotifications']
				: array();
			$global_defaults = Gutena_Forms_Auto_Responder_Helper::get_form_defaults_for_editor();
			$form_defaults   = self::get_form_notification_defaults( $attrs, $global_defaults );
			$form_meta       = array(
				'from_email' => isset( $email_settings['from_email'] ) ? (string) $email_settings['from_email'] : (string) ( $form_defaults['from_email'] ?? '' ),
				'cc'         => isset( $email_settings['cc'] ) ? (string) $email_settings['cc'] : (string) ( $form_defaults['cc'] ?? '' ),
				'bcc'        => isset( $email_settings['bcc'] ) ? (string) $email_settings['bcc'] : (string) ( $form_defaults['bcc'] ?? '' ),
				'reply_to'   => isset( $email_settings['reply_to'] ) ? (string) $email_settings['reply_to'] : (string) ( $form_defaults['reply_to'] ?? '' ),
			);

			if ( ! empty( $email_settings['hasSavedConfig'] ) ) {
				$notifications = isset( $email_settings['notifications'] ) && is_array( $email_settings['notifications'] )
					? $email_settings['notifications']
					: array();

				return array( $notifications, $form_meta, $global_defaults );
			}

			if ( self::is_legacy_email_notification_form( $attrs ) ) {
				return array(
					array( self::build_legacy_admin_notification( $attrs, $form_defaults ) ),
					$form_meta,
					$global_defaults,
				);
			}

			$notifications = isset( $email_settings['notifications'] ) && is_array( $email_settings['notifications'] )
				? $email_settings['notifications']
				: array();

			return array( $notifications, $form_meta, $global_defaults );
		}

		/**
		 * Build editor-equivalent defaults for a saved form schema.
		 *
		 * @param array $attrs            Form attributes.
		 * @param array $global_defaults  Global defaults.
		 * @return array
		 */
		private static function get_form_notification_defaults( $attrs, $global_defaults ) {
			$email_settings = isset( $attrs['settings']['emailNotifications'] ) && is_array( $attrs['settings']['emailNotifications'] )
				? $attrs['settings']['emailNotifications']
				: array();

			return array(
				'send_email_to'      => ! empty( $attrs['adminEmails'] ) ? (string) $attrs['adminEmails'] : (string) ( $global_defaults['send_email_to'] ?? '' ),
				'subject'            => ! empty( $attrs['adminEmailSubject'] ) ? (string) $attrs['adminEmailSubject'] : (string) ( $global_defaults['subject'] ?? '' ),
				'message'            => ! empty( $attrs['adminEmailTemplate'] ) ? (string) $attrs['adminEmailTemplate'] : (string) ( $global_defaults['message'] ?? '' ),
				'from_name'          => ! empty( $attrs['emailFromName'] ) ? (string) $attrs['emailFromName'] : (string) ( $global_defaults['from_name'] ?? '' ),
				'from_email'         => ! empty( $email_settings['from_email'] ) ? (string) $email_settings['from_email'] : (string) ( $global_defaults['from_email'] ?? '' ),
				'cc'                 => ! empty( $email_settings['cc'] ) ? (string) $email_settings['cc'] : (string) ( $global_defaults['cc'] ?? '' ),
				'bcc'                => ! empty( $email_settings['bcc'] ) ? (string) $email_settings['bcc'] : (string) ( $global_defaults['bcc'] ?? '' ),
				'reply_to'           => ! empty( $email_settings['reply_to'] ) ? (string) $email_settings['reply_to'] : (string) ( $global_defaults['reply_to'] ?? '' ),
				'reply_to_name'      => ! empty( $attrs['replyToName'] ) ? (string) $attrs['replyToName'] : '',
				'reply_to_last_name' => ! empty( $attrs['replyToLastName'] ) ? (string) $attrs['replyToLastName'] : '',
			);
		}

		/**
		 * Detect legacy forms that have not saved the new notification config.
		 *
		 * @param array $attrs Form attributes.
		 * @return bool
		 */
		private static function is_legacy_email_notification_form( $attrs ) {
			$email_settings = isset( $attrs['settings']['emailNotifications'] ) && is_array( $attrs['settings']['emailNotifications'] )
				? $attrs['settings']['emailNotifications']
				: array();

			if ( ! empty( $email_settings['hasSavedConfig'] ) ) {
				return false;
			}

			if ( empty( $attrs['formID'] ) ) {
				return false;
			}

			if (
				! empty( $email_settings ) &&
				(
					array_key_exists( 'from_email', $email_settings ) ||
					array_key_exists( 'cc', $email_settings ) ||
					array_key_exists( 'bcc', $email_settings ) ||
					array_key_exists( 'reply_to', $email_settings )
				)
			) {
				return false;
			}

			return true;
		}

		/**
		 * Build a synthetic legacy admin notification.
		 *
		 * @param array $attrs           Form attributes.
		 * @param array $form_defaults   Form defaults.
		 * @return array
		 */
		private static function build_legacy_admin_notification( $attrs, $form_defaults ) {
			$email_notify = isset( $attrs['emailNotifyAdmin'] ) ? $attrs['emailNotifyAdmin'] : true;

			return array(
				'id'                 => 'legacy-admin-notification',
				'enabled'            => ! ( '' === $email_notify || false === $email_notify || '0' === $email_notify || 0 === $email_notify ),
				'name'               => __( 'Admin Notification Email', 'gutena-forms' ),
				'send_email_to'      => ! empty( $attrs['adminEmails'] ) ? (string) $attrs['adminEmails'] : (string) ( $form_defaults['send_email_to'] ?? '' ),
				'subject'            => ! empty( $attrs['adminEmailSubject'] ) ? (string) $attrs['adminEmailSubject'] : (string) ( $form_defaults['subject'] ?? '' ),
				'message'            => ! empty( $attrs['adminEmailTemplate'] ) ? (string) $attrs['adminEmailTemplate'] : (string) ( $form_defaults['message'] ?? '' ),
				'from_name'          => ! empty( $attrs['emailFromName'] ) ? (string) $attrs['emailFromName'] : (string) ( $form_defaults['from_name'] ?? '' ),
				'from_email'         => (string) ( $form_defaults['from_email'] ?? '' ),
				'cc'                 => (string) ( $form_defaults['cc'] ?? '' ),
				'bcc'                => (string) ( $form_defaults['bcc'] ?? '' ),
				'reply_to'           => (string) ( $form_defaults['reply_to'] ?? '' ),
				'reply_to_name'      => ! empty( $attrs['replyToName'] ) ? (string) $attrs['replyToName'] : (string) ( $form_defaults['reply_to_name'] ?? '' ),
				'reply_to_last_name' => ! empty( $attrs['replyToLastName'] ) ? (string) $attrs['replyToLastName'] : (string) ( $form_defaults['reply_to_last_name'] ?? '' ),
			);
		}

		/**
		 * Send a single notification with failure isolation.
		 *
		 * @param array  $notification     Notification config.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @param string $default_body     Legacy auto-generated body.
		 * @param array  $form_meta        Form-level fallback values.
		 * @param array  $global_defaults  Global fallback values.
		 * @return bool
		 */
		private static function send_single_notification( $notification, $form_submit_data, $schema, $field_schema, $default_body, $form_meta, $global_defaults ) {
			try {
				$send_email_to = self::get_notification_value( $notification, 'send_email_to', $form_meta, $global_defaults, 'send_email_to' );
				$subject       = self::get_notification_value( $notification, 'subject', $form_meta, $global_defaults, 'subject' );
				$message       = self::get_notification_value( $notification, 'message', $form_meta, $global_defaults, 'message' );
				$from_name     = self::get_notification_value( $notification, 'from_name', $form_meta, $global_defaults, 'from_name' );
				$from_email    = self::get_notification_value( $notification, 'from_email', $form_meta, $global_defaults, 'from_email' );
				$cc            = self::get_notification_value( $notification, 'cc', $form_meta, $global_defaults, 'cc' );
				$bcc           = self::get_notification_value( $notification, 'bcc', $form_meta, $global_defaults, 'bcc' );
				$reply_to      = self::get_notification_value( $notification, 'reply_to', $form_meta, $global_defaults, 'reply_to' );

				$to = self::resolve_recipient_list( $send_email_to, $form_submit_data, $schema, $field_schema );
				if ( empty( $to ) ) {
					return false;
				}

				$subject = self::replace_notification_merge_tags( $subject, $form_submit_data, $schema, $field_schema );
				$subject = sanitize_text_field( $subject );
				if ( '' === trim( $subject ) ) {
					return false;
				}

				$from_name = self::replace_notification_merge_tags( $from_name, $form_submit_data, $schema, $field_schema );
				$from_name = sanitize_text_field( $from_name );
				if ( '' === $from_name ) {
					$from_name = sanitize_text_field( get_bloginfo( 'name' ) );
				}

				$from_email = self::resolve_notification_from_email( $from_email, $form_submit_data, $schema, $field_schema );
				$body       = self::resolve_message_body( $message, $form_submit_data, $schema, $field_schema, $default_body );
				$cc_emails  = self::resolve_recipient_list( $cc, $form_submit_data, $schema, $field_schema );
				$bcc_emails = self::resolve_recipient_list( $bcc, $form_submit_data, $schema, $field_schema );
				$reply_to   = self::resolve_legacy_reply_to_email( $reply_to, $form_submit_data, $schema, $field_schema );
				$reply_name = self::resolve_reply_to_name( $notification, $form_submit_data, $schema );

				$headers = self::build_email_headers( $from_name, $from_email, $reply_to, $reply_name, $cc_emails, $bcc_emails );

				$body = apply_filters( 'gutena_forms_submit_admin_notification', $body, $form_submit_data, $notification );

				if ( function_exists( 'is_gutena_forms_pro' ) && ! is_gutena_forms_pro( false ) ) {
					$body .= '<div style="background-color: #fffbeb; width: fit-content; margin-top: 50px; padding: 14px 15px 12px 15px; border-radius: 10px;" > <span style="font-size: 13px; line-height: 1; display: flex;" > <span style="margin-right: 5px;" > </span> <span style="margin-right: 3px;" ><strong>' . esc_html__( 'Exciting News!', 'gutena-forms' ) . ' </strong></span> ' . esc_html__( 'Now, you can view and manage all your form submissions right from the Gutena Forms Dashboard.', 'gutena-forms' ) . '<strong><a href="' . esc_url( admin_url( 'admin.php?page=gutena-forms' ) ) . '" style="color: #E35D3F; margin-left: 1rem;" target="_blank" > ' . esc_html__( 'See all Entries', 'gutena-forms' ) . ' </a></strong></span></div>';
				}

				if ( false === strpos( $body, '<' ) ) {
					$body = wpautop( $body, true );
				}

				$html_body = Gutena_Forms_Auto_Responder_Helper::wrap_email_html_body( $body, $subject );

				return (bool) wp_mail( $to, esc_html( $subject ), $html_body, $headers );
			} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
				return false;
			}
		}

		/**
		 * Resolve a notification field with form/global fallbacks.
		 *
		 * @param array  $notification     Notification config.
		 * @param string $key              Field key.
		 * @param array  $form_meta        Form-level fallback values.
		 * @param array  $global_defaults  Global fallback values.
		 * @param string $global_key       Global defaults key.
		 * @return string
		 */
		private static function get_notification_value( $notification, $key, $form_meta, $global_defaults, $global_key = '' ) {
			$value = isset( $notification[ $key ] ) ? trim( (string) $notification[ $key ] ) : '';
			if ( '' !== $value ) {
				return $value;
			}

			$meta_keys = array( 'from_email', 'cc', 'bcc', 'reply_to' );
			if ( in_array( $key, $meta_keys, true ) && ! empty( $form_meta[ $key ] ) ) {
				return trim( (string) $form_meta[ $key ] );
			}

			$global_key = '' !== $global_key ? $global_key : $key;
			if ( isset( $global_defaults[ $global_key ] ) && '' !== trim( (string) $global_defaults[ $global_key ] ) ) {
				return trim( (string) $global_defaults[ $global_key ] );
			}

			return '';
		}

		/**
		 * Resolve message body with merge tags and legacy fallback.
		 *
		 * @param string $message          Notification message.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @param string $default_body     Auto-generated fallback body.
		 * @return string
		 */
		private static function resolve_message_body( $message, $form_submit_data, $schema, $field_schema, $default_body ) {
			if ( '' === trim( (string) $message ) ) {
				if ( '' !== trim( (string) $default_body ) ) {
					return $default_body;
				}

				return self::build_all_data_html( $form_submit_data );
			}

			$message = self::replace_notification_merge_tags( $message, $form_submit_data, $schema, $field_schema );
			return wp_kses_post( $message );
		}

		/**
		 * Resolve From Email with merge tags and admin fallback.
		 *
		 * @param string $from_email       Raw from email value.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @return string
		 */
		private static function resolve_notification_from_email( $from_email, $form_submit_data, $schema, $field_schema ) {
			$from_email = trim( (string) $from_email );

			if ( '' !== $from_email ) {
				if ( is_email( $from_email ) ) {
					return sanitize_email( $from_email );
				}

				$resolved = self::replace_notification_merge_tags( $from_email, $form_submit_data, $schema, $field_schema, true );
				$resolved = sanitize_email( $resolved );
				if ( is_email( $resolved ) ) {
					return $resolved;
				}
			}

			return sanitize_email( get_option( 'admin_email' ) );
		}

		/**
		 * Resolve legacy reply-to email from configured email field.
		 *
		 * @param string $reply_to         Configured reply-to value.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @return string
		 */
		private static function resolve_legacy_reply_to_email( $reply_to, $form_submit_data, $schema, $field_schema ) {
			$resolved = self::resolve_recipient_list( $reply_to, $form_submit_data, $schema, $field_schema );
			if ( ! empty( $resolved ) ) {
				return $resolved[0];
			}

			$reply_field = empty( $schema['form_attrs']['replyToEmail'] ) ? '' : sanitize_key( $schema['form_attrs']['replyToEmail'] );
			if ( $reply_field && ! empty( $form_submit_data['raw_data'][ $reply_field ]['value'] ) ) {
				$email = sanitize_email( $form_submit_data['raw_data'][ $reply_field ]['value'] );
				if ( is_email( $email ) ) {
					return $email;
				}
			}

			return '';
		}

		/**
		 * Build Reply-To display name from configured text fields.
		 *
		 * @param array $notification     Notification config.
		 * @param array $form_submit_data Submission data.
		 * @param array $schema           Form schema.
		 * @return string
		 */
		private static function resolve_reply_to_name( $notification, $form_submit_data, $schema ) {
			$first_name_field = isset( $notification['reply_to_name'] ) ? sanitize_key( $notification['reply_to_name'] ) : '';
			$last_name_field  = isset( $notification['reply_to_last_name'] ) ? sanitize_key( $notification['reply_to_last_name'] ) : '';

			if ( '' === $first_name_field && ! empty( $schema['form_attrs']['replyToName'] ) ) {
				$first_name_field = sanitize_key( $schema['form_attrs']['replyToName'] );
			}

			if ( '' === $last_name_field && ! empty( $schema['form_attrs']['replyToLastName'] ) ) {
				$last_name_field = sanitize_key( $schema['form_attrs']['replyToLastName'] );
			}

			$parts = array();

			if ( $first_name_field && ! empty( $form_submit_data['raw_data'][ $first_name_field ]['value'] ) ) {
				$parts[] = sanitize_text_field( $form_submit_data['raw_data'][ $first_name_field ]['value'] );
			} elseif ( ! empty( $form_submit_data['replyToFname'] ) ) {
				$parts[] = sanitize_text_field( $form_submit_data['replyToFname'] );
			}

			if ( $last_name_field && ! empty( $form_submit_data['raw_data'][ $last_name_field ]['value'] ) ) {
				$parts[] = sanitize_text_field( $form_submit_data['raw_data'][ $last_name_field ]['value'] );
			} elseif ( ! empty( $form_submit_data['replyToLname'] ) ) {
				$parts[] = sanitize_text_field( $form_submit_data['replyToLname'] );
			}

			return trim( implode( ' ', array_filter( $parts ) ) );
		}

		/**
		 * Resolve comma-separated recipient values after merge tags.
		 *
		 * @param string $raw_value        Raw recipient string.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @return array
		 */
		private static function resolve_recipient_list( $raw_value, $form_submit_data, $schema, $field_schema ) {
			$raw_value = trim( (string) $raw_value );
			if ( '' === $raw_value ) {
				return array();
			}

			$resolved = self::replace_notification_merge_tags( $raw_value, $form_submit_data, $schema, $field_schema, true );
			$parts    = array_map( 'trim', explode( ',', $resolved ) );
			$emails   = array();

			foreach ( $parts as $part ) {
				if ( '' === $part ) {
					continue;
				}

				$email = sanitize_email( $part );
				if ( is_email( $email ) ) {
					$emails[] = $email;
				}
			}

			if ( empty( $emails ) && is_email( sanitize_email( $resolved ) ) ) {
				$emails[] = sanitize_email( $resolved );
			}

			return array_values( array_unique( $emails ) );
		}

		/**
		 * Build email headers.
		 *
		 * @param string $from_name  From name.
		 * @param string $from_email From email.
		 * @param string $reply_to   Reply-to email.
		 * @param string $reply_name Reply-to name.
		 * @param array  $cc_emails  CC recipients.
		 * @param array  $bcc_emails BCC recipients.
		 * @return array
		 */
		private static function build_email_headers( $from_name, $from_email, $reply_to, $reply_name, $cc_emails, $bcc_emails ) {
			$headers = array(
				'Content-Type: text/html; charset=UTF-8',
				'From: ' . $from_name . ' <' . $from_email . '>',
			);

			if ( is_email( $reply_to ) ) {
				$reply_header = '' !== $reply_name
					? $reply_name . ' <' . $reply_to . '>'
					: $reply_to;
				$headers[]    = 'Reply-To: ' . $reply_header;
			}

			foreach ( $cc_emails as $cc_email ) {
				$headers[] = 'Cc: ' . $cc_email;
			}

			foreach ( $bcc_emails as $bcc_email ) {
				$headers[] = 'Bcc: ' . $bcc_email;
			}

			return $headers;
		}

		/**
		 * Replace notification merge tags in text.
		 *
		 * @param string $text             Template text.
		 * @param array  $form_submit_data Submission data.
		 * @param array  $schema           Form schema.
		 * @param array  $field_schema     Field schema.
		 * @param bool   $recipient_mode   Whether unresolved recipient tags should resolve to email values.
		 * @return string
		 */
		public static function replace_notification_merge_tags( $text, $form_submit_data, $schema, $field_schema, $recipient_mode = false ) {
			if ( '' === trim( (string) $text ) ) {
				return '';
			}

			$replacements = self::get_notification_merge_tag_replacements( $form_submit_data, $schema, $field_schema, $recipient_mode );

			return str_replace( array_keys( $replacements ), array_values( $replacements ), $text );
		}

		/**
		 * Build merge tag replacement map for notifications.
		 *
		 * @param array $form_submit_data Submission data.
		 * @param array $schema           Form schema.
		 * @param array $field_schema     Field schema.
		 * @param bool  $recipient_mode   Recipient resolution mode.
		 * @return array
		 */
		private static function get_notification_merge_tag_replacements( $form_submit_data, $schema, $field_schema, $recipient_mode = false ) {
			$replacements = array(
				'{site_name}'   => get_bloginfo( 'name' ),
				'{site_title}'  => get_bloginfo( 'name' ),
				'{site_url}'    => get_site_url(),
				'{form_title}'  => Gutena_Forms_Auto_Responder_Helper::get_form_title( $form_submit_data, $schema ),
				'{form-title}'  => Gutena_Forms_Auto_Responder_Helper::get_form_title( $form_submit_data, $schema ),
				'{admin_email}' => sanitize_email( get_option( 'admin_email' ) ),
				'{user_email}'  => Gutena_Forms_Auto_Responder_Helper::get_submitter_email( $form_submit_data, $field_schema, $schema ),
				'{user_name}'   => self::get_user_display_name( $form_submit_data, $schema ),
				'{first_name}'  => Gutena_Forms_Auto_Responder_Helper::get_submitter_first_name( $form_submit_data, $schema ),
				'{last_name}'   => self::get_submitter_last_name( $form_submit_data, $schema ),
				'{email}'       => self::get_field_value_by_type( $form_submit_data, $field_schema, 'email' ),
				'{message}'     => self::get_field_value_by_type( $form_submit_data, $field_schema, 'textarea' ),
				'{country}'     => self::get_field_value_by_type( $form_submit_data, $field_schema, 'country' ),
				'{state}'       => self::get_field_value_by_type( $form_submit_data, $field_schema, 'state' ),
				'{number}'      => self::get_field_value_by_type( $form_submit_data, $field_schema, 'number' ),
				'{url}'         => self::get_field_value_by_type( $form_submit_data, $field_schema, 'url' ),
				'{textarea}'    => self::get_field_value_by_type( $form_submit_data, $field_schema, 'textarea' ),
				'{phone}'       => self::get_field_value_by_type( $form_submit_data, $field_schema, 'phone' ),
				'{checkbox}'    => self::get_field_value_by_type( $form_submit_data, $field_schema, 'checkbox' ),
				'{all_data}'    => $recipient_mode ? '' : self::build_all_data_html( $form_submit_data ),
			);

			if ( ! empty( $form_submit_data['raw_data'] ) && is_array( $form_submit_data['raw_data'] ) ) {
				$first_email_value = '';

				foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
					$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
					$value = empty( $field_data['value'] ) ? '' : $field_data['value'];

					$replacements[ '{' . $label . '}' ]                    = $value;
					$replacements[ '{' . sanitize_key( $label ) . '}' ]      = $value;
					$replacements[ '{' . $name_attr . '}' ]                  = $value;
					$replacements[ '{' . ucfirst( sanitize_key( $name_attr ) ) . '}' ] = $value;
					$replacements[ '{field:' . $name_attr . '}' ]           = $value;

					$field_type = empty( $field_schema[ $name_attr ]['fieldType'] ) ? 'text' : $field_schema[ $name_attr ]['fieldType'];
					if ( 'email' === $field_type && '' === $first_email_value && is_email( sanitize_email( $value ) ) ) {
						$first_email_value = sanitize_email( $value );
					}
				}

				if ( '' !== $first_email_value ) {
					$replacements['{field:email}'] = $first_email_value;
				}
			}

			$first_name = Gutena_Forms_Auto_Responder_Helper::get_submitter_first_name( $form_submit_data, $schema );
			if ( '' !== $first_name ) {
				$replacements['{Name}'] = $first_name;
				$replacements['{name}'] = $first_name;
			}

			$replacements = apply_filters( 'gutena_forms_auto_responder_merge_tags', $replacements, $form_submit_data, $schema );

			return $replacements;
		}

		/**
		 * Build HTML for all submitted fields.
		 *
		 * @param array $form_submit_data Submission data.
		 * @return string
		 */
		private static function build_all_data_html( $form_submit_data ) {
			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			$html = '';
			foreach ( $form_submit_data['raw_data'] as $field_data ) {
				$label = empty( $field_data['label'] ) ? '' : $field_data['label'];
				$value = empty( $field_data['value'] ) ? '' : $field_data['value'];
				$html .= '<p><strong>' . esc_html( $label ) . '</strong> <br />' . esc_html( $value ) . ' </p>';
			}

			return $html;
		}

		/**
		 * Get first field value for a given field type.
		 *
		 * @param array  $form_submit_data Submission data.
		 * @param array  $field_schema     Field schema.
		 * @param string $field_type       Field type.
		 * @return string
		 */
		private static function get_field_value_by_type( $form_submit_data, $field_schema, $field_type ) {
			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
				$type = empty( $field_schema[ $name_attr ]['fieldType'] ) ? 'text' : $field_schema[ $name_attr ]['fieldType'];
				if ( $field_type === $type ) {
					return empty( $field_data['value'] ) ? '' : (string) $field_data['value'];
				}
			}

			return '';
		}

		/**
		 * Resolve submitter last name.
		 *
		 * @param array $form_submit_data Submission data.
		 * @param array $schema           Form schema.
		 * @return string
		 */
		private static function get_submitter_last_name( $form_submit_data, $schema ) {
			$reply_to_field = empty( $schema['form_attrs']['replyToLastName'] ) ? '' : sanitize_key( $schema['form_attrs']['replyToLastName'] );

			if ( $reply_to_field && ! empty( $form_submit_data['raw_data'][ $reply_to_field ]['value'] ) ) {
				return sanitize_text_field( $form_submit_data['raw_data'][ $reply_to_field ]['value'] );
			}

			if ( ! empty( $form_submit_data['replyToLname'] ) ) {
				return sanitize_text_field( $form_submit_data['replyToLname'] );
			}

			$patterns = array( 'last name', 'lastname', 'last_name', 'lname' );
			if ( empty( $form_submit_data['raw_data'] ) || ! is_array( $form_submit_data['raw_data'] ) ) {
				return '';
			}

			foreach ( $form_submit_data['raw_data'] as $name_attr => $field_data ) {
				$label = empty( $field_data['label'] ) ? $name_attr : $field_data['label'];
				$label = strtolower( trim( str_replace( array( '-', '_' ), ' ', $label ) ) );
				$attr  = strtolower( trim( str_replace( array( '-', '_' ), ' ', $name_attr ) ) );

				if ( in_array( $label, $patterns, true ) || in_array( $attr, $patterns, true ) ) {
					return empty( $field_data['value'] ) ? '' : sanitize_text_field( $field_data['value'] );
				}
			}

			return '';
		}

		/**
		 * Resolve submitter display name.
		 *
		 * @param array $form_submit_data Submission data.
		 * @param array $schema           Form schema.
		 * @return string
		 */
		private static function get_user_display_name( $form_submit_data, $schema ) {
			$parts = array_filter(
				array(
					Gutena_Forms_Auto_Responder_Helper::get_submitter_first_name( $form_submit_data, $schema ),
					self::get_submitter_last_name( $form_submit_data, $schema ),
				)
			);

			return trim( implode( ' ', $parts ) );
		}
	}
endif;
