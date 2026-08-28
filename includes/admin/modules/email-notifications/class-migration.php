<?php
/**
 * Migration class for upgrading from old auto-responder to new email notifications.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Notifications_Migration' ) ) :
	/**
	 * Handles migration from old auto-responder settings to new email notifications.
	 */
	class Gutena_Forms_Email_Notifications_Migration {

		/**
		 * Migration flag option name.
		 */
		const MIGRATION_FLAG = 'gutena_forms_email_notifications_migrated';

		/**
		 * Run migration if needed.
		 */
		public static function run() {
			if ( get_option( self::MIGRATION_FLAG, false ) ) {
				return;
			}

			self::migrate_global_settings();
			self::migrate_form_schemas();

			update_option( self::MIGRATION_FLAG, true );
		}

		/**
		 * Migrate global auto-responder settings to new format.
		 */
		private static function migrate_global_settings() {
			if ( class_exists( 'Gutena_Forms_Email_Notifications_Helper' ) ) {
				Gutena_Forms_Email_Notifications_Helper::migrate_old_settings();
			}
		}

		/**
		 * Migrate per-form schemas from old attrs to new emailNotifications format.
		 */
		private static function migrate_form_schemas() {
			$form_ids = get_option( 'gutena_form_ids', array() );
			if ( ! is_array( $form_ids ) || empty( $form_ids ) ) {
				return;
			}

			foreach ( $form_ids as $form_id ) {
				$option_key = GUTENA_FORMS_SCHEMA_OPTION_PREFIX . sanitize_key( $form_id );
				$schema     = get_option( $option_key, array() );

				if ( empty( $schema['form_attrs'] ) ) {
					continue;
				}

				$attrs = $schema['form_attrs'];

				// Skip if already migrated.
				if ( ! empty( $attrs['emailNotifications'] ) ) {
					continue;
				}

				$notifications = array();

				// Migrate old admin notification attrs.
				if ( ! empty( $attrs['emailNotifyAdmin'] ) && false !== $attrs['emailNotifyAdmin'] && '0' !== $attrs['emailNotifyAdmin'] ) {
					$notification = array(
						'id'                  => 'notif_admin_' . sanitize_key( $form_id ),
						'enabled'             => true,
						'name'                => __( 'Admin Notification Email', 'gutena-forms' ),
						'send_to'             => empty( $attrs['adminEmails'] ) ? '' : sanitize_text_field( $attrs['adminEmails'] ),
						'subject'             => empty( $attrs['adminEmailSubject'] ) ? 'New Form Submission - {form_title}' : sanitize_text_field( $attrs['adminEmailSubject'] ),
						'message'             => empty( $attrs['adminEmailTemplate'] ) ? '' : sanitize_textarea_field( $attrs['adminEmailTemplate'] ),
						'from_name'           => empty( $attrs['emailFromName'] ) ? '' : sanitize_text_field( $attrs['emailFromName'] ),
						'from_email'          => '',
						'cc'                  => '',
						'bcc'                 => '',
						'reply_to'            => empty( $attrs['replyToEmail'] ) ? '' : sanitize_text_field( $attrs['replyToEmail'] ),
						'reply_to_first_name' => empty( $attrs['replyToName'] ) ? '' : sanitize_text_field( $attrs['replyToName'] ),
						'reply_to_last_name'  => empty( $attrs['replyToLastName'] ) ? '' : sanitize_text_field( $attrs['replyToLastName'] ),
					);

					$notifications[] = $notification;
				}

				// Migrate old user auto-responder attrs.
				if ( ! empty( $attrs['emailNotifyUser'] ) ) {
					$notification = array(
						'id'                  => 'notif_user_' . sanitize_key( $form_id ),
						'enabled'             => true,
						'name'                => __( 'User Confirmation Email', 'gutena-forms' ),
						'send_to'             => '{user_email}',
						'subject'             => empty( $attrs['userEmailSubject'] ) ? 'Thank you for your submission' : sanitize_text_field( $attrs['userEmailSubject'] ),
						'message'             => empty( $attrs['userEmailTemplate'] ) ? '' : sanitize_textarea_field( $attrs['userEmailTemplate'] ),
						'from_name'           => empty( $attrs['emailFromName'] ) ? '' : sanitize_text_field( $attrs['emailFromName'] ),
						'from_email'          => '',
						'cc'                  => '',
						'bcc'                 => '',
						'reply_to'            => '',
						'reply_to_first_name' => '',
						'reply_to_last_name'  => '',
					);

					$notifications[] = $notification;
				}

				// Update the schema with new emailNotifications attr.
				$schema['form_attrs']['emailNotifications'] = array(
					'enabled'         => ! empty( $notifications ),
					'notifications'   => $notifications,
					'defaultSettings' => false,
				);

				update_option( $option_key, $schema );
			}
		}
	}
endif;
