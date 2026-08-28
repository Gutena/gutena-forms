<?php
/**
 * Email Notifications admin settings module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Email_Notifications' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Global email notification defaults settings.
	 */
	class Gutena_Forms_Email_Notifications extends Gutena_Forms_Forms_Settings {

		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Email_Notifications
		 */
		private static $instance;

		/**
		 * Saved settings.
		 *
		 * @var array
		 */
		public $settings = array();

		/**
		 * Constructor.
		 */
		public function __construct() {
			$settings       = get_option( Gutena_Forms_Email_Notifications_Helper::OPTION_NAME, array() );
			$this->settings = is_array( $settings ) ? $settings : array();
		}

		/**
		 * Register module.
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['email-notifications'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance.
		 *
		 * @return Gutena_Forms_Email_Notifications
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Settings definition for dashboard UI.
		 *
		 * @return array
		 */
		public function get_settings() {
			$defaults   = Gutena_Forms_Email_Notifications_Helper::get_global_defaults();
			$merge_tags = Gutena_Forms_Email_Notifications_Helper::get_static_merge_tags();

			return array(
				'id'          => 'email-notifications',
				'title'       => __( 'Email Notifications', 'gutena-forms' ),
				'description' => __( 'Configure default settings that apply to newly created forms.', 'gutena-forms' ),
				'fields'      => array(
				array(
					'id'       => 'send_to',
					'type'     => 'text',
					'name'     => __( 'Send Email To', 'gutena-forms' ),
					'required' => true,
					'value'    => empty( $this->settings['send_to'] ) ? $defaults['send_to'] : $this->settings['send_to'],
					'attrs'    => array(
						'merge_tag_field' => true,
						'placeholder'     => __( 'Enter email addresses (comma separated)', 'gutena-forms' ),
					),
				),
				array(
					'id'       => 'subject',
					'type'     => 'text',
					'name'     => __( 'Subject', 'gutena-forms' ),
					'required' => true,
					'value'    => empty( $this->settings['subject'] ) ? $defaults['subject'] : $this->settings['subject'],
					'attrs'    => array(
						'merge_tag_field' => true,
						'placeholder'     => $defaults['subject'],
					),
				),
					array(
						'id'    => 'message',
						'type'  => 'textarea',
						'name'  => __( 'Email Message', 'gutena-forms' ),
						'value' => empty( $this->settings['message'] ) ? $defaults['message'] : $this->settings['message'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['message'],
							'rows'            => 6,
						),
					),
					array(
						'id'    => 'merge_tags',
						'type'  => 'merge-tags',
						'name'  => __( 'Merge Tags', 'gutena-forms' ),
						'attrs' => array(
							'tags' => $merge_tags,
						),
					),
					array(
						'id'    => 'from_name',
						'type'  => 'text',
						'name'  => __( 'From Name', 'gutena-forms' ),
						'value' => empty( $this->settings['from_name'] ) ? $defaults['from_name'] : $this->settings['from_name'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => __( 'Leave empty to use site name', 'gutena-forms' ),
						),
					),
				array(
					'id'       => 'from_email',
					'type'     => 'text',
					'name'     => __( 'From Email', 'gutena-forms' ),
					'required' => true,
					'value'    => empty( $this->settings['from_email'] ) ? $defaults['from_email'] : $this->settings['from_email'],
					'attrs'    => array(
						'merge_tag_field' => true,
						'placeholder'     => __( 'Leave empty to use admin email', 'gutena-forms' ),
						'description'     => __( 'This email must be unique, otherwise it will override the previous one. This is to prevent the email from being marked as spam.', 'gutena-forms' ),
					),
				),
					array(
						'id'    => 'cc',
						'type'  => 'text',
						'name'  => __( 'CC', 'gutena-forms' ),
						'value' => empty( $this->settings['cc'] ) ? $defaults['cc'] : $this->settings['cc'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => __( 'Comma separated email addresses', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'bcc',
						'type'  => 'text',
						'name'  => __( 'BCC', 'gutena-forms' ),
						'value' => empty( $this->settings['bcc'] ) ? $defaults['bcc'] : $this->settings['bcc'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => __( 'Comma separated email addresses', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'reply_to',
						'type'  => 'text',
						'name'  => __( 'Reply To', 'gutena-forms' ),
						'value' => empty( $this->settings['reply_to'] ) ? $defaults['reply_to'] : $this->settings['reply_to'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => __( 'Email address or merge tag', 'gutena-forms' ),
						),
					),
				array(
					'id'    => 'reply_to_first_name',
					'type'  => 'select',
					'name'  => __( 'Reply To Name ( First Name )', 'gutena-forms' ),
					'value' => empty( $this->settings['reply_to_first_name'] ) ? '' : $this->settings['reply_to_first_name'],
					'attrs' => array(
						'description' => __( 'Select first or full name field for reply to address.', 'gutena-forms' ),
						'options'     => array(
							''             => __( 'First Name', 'gutena-forms' ),
							'{first_name}' => '{first_name}',
							'{last_name}'  => '{last_name}',
							'{email}'      => '{email}',
							'{phone}'      => '{phone}',
							'{message}'    => '{message}',
						),
					),
				),
				array(
					'id'    => 'reply_to_last_name',
					'type'  => 'select',
					'name'  => __( 'Reply To Name ( Last Name )', 'gutena-forms' ),
					'value' => empty( $this->settings['reply_to_last_name'] ) ? '' : $this->settings['reply_to_last_name'],
					'attrs' => array(
						'description' => __( 'Select last name field for reply to address.', 'gutena-forms' ),
						'options'     => array(
							''             => __( 'Last Name', 'gutena-forms' ),
							'{first_name}' => '{first_name}',
							'{last_name}'  => '{last_name}',
							'{email}'      => '{email}',
							'{phone}'      => '{phone}',
							'{message}'    => '{message}',
						),
					),
				),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Changes', 'gutena-forms' ),
					),
				),
			);
		}

		/**
		 * Save settings to options table.
		 *
		 * @param array $settings Settings payload.
		 * @return bool
		 */
		public function save_settings( $settings ) {
			$previous_global = get_option( Gutena_Forms_Email_Notifications_Helper::OPTION_NAME, array() );
			$sanitized       = Gutena_Forms_Email_Notifications_Helper::sanitize_global_settings( $settings );

			// Update primary form block with defaultSettings flag.
			$block_settings                    = $sanitized;
			$block_settings['defaultSettings'] = true;
			Gutena_Forms_Settings_Migrator::update_primary_form( 'emailNotifications', $block_settings );

			// Save global option.
			update_option( Gutena_Forms_Email_Notifications_Helper::OPTION_NAME, $sanitized );
			$this->settings = $sanitized;

			// Sync to all forms that use global defaults.
			Gutena_Forms_Settings_Migrator::sync_global_module_to_forms( 'emailNotifications', $previous_global, $sanitized );

			return true;
		}
	}

	Gutena_Forms_Email_Notifications::register_module();
endif;
