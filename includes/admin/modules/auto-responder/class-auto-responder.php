<?php
/**
 * Global email notification defaults admin settings module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Auto_Responder' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Global email notification defaults for newly created forms.
	 */
	class Gutena_Forms_Auto_Responder extends Gutena_Forms_Forms_Settings {

		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Auto_Responder
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
			// Raw option only — avoid __() via get_defaults() before init (WP 6.7+ notice).
			$settings       = get_option( Gutena_Forms_Auto_Responder_Helper::OPTION_NAME, array() );
			$this->settings = Gutena_Forms_Auto_Responder_Helper::normalize_legacy_settings(
				is_array( $settings ) ? $settings : array()
			);
		}

		/**
		 * Register module.
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['auto-responder'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance.
		 *
		 * @return Gutena_Forms_Auto_Responder
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Resolve a stored setting value with defaults.
		 *
		 * @param string $key Setting key.
		 * @param array  $defaults Default values.
		 * @return mixed
		 */
		private function get_setting_value( $key, $defaults ) {
			if ( isset( $this->settings[ $key ] ) && '' !== $this->settings[ $key ] ) {
				return $this->settings[ $key ];
			}

			return $defaults[ $key ] ?? '';
		}

		/**
		 * Settings definition for dashboard UI.
		 *
		 * @return array
		 */
		public function get_settings() {
			$defaults   = Gutena_Forms_Auto_Responder_Helper::get_defaults();
			$merge_tags = Gutena_Forms_Auto_Responder_Helper::get_static_merge_tags();

			return array(
				'id'          => 'auto-responder',
				'title'       => __( 'Email Notifications', 'gutena-forms' ),
				'description' => __( 'Configure default settings that apply to newly created forms.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'    => 'send_email_to',
						'type'  => 'email',
						'name'  => __( 'Send Email To', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'send_email_to', $defaults ),
						'attrs' => array(
							'required' => true,
							'multiple' => true,
						),
					),
					array(
						'id'    => 'subject',
						'type'  => 'text',
						'name'  => __( 'Subject', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'subject', $defaults ),
						'attrs' => array(
							'required'        => true,
							'merge_tag_field' => true,
							'placeholder'     => $defaults['subject'],
						),
					),
					array(
						'id'    => 'message',
						'type'  => 'html-editor',
						'name'  => __( 'Email Message', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'message', $defaults ),
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['message'],
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
						'value' => $this->get_setting_value( 'from_name', $defaults ),
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['from_name'],
						),
					),
					array(
						'id'    => 'from_email',
						'type'  => 'email',
						'name'  => __( 'From Email', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'from_email', $defaults ),
						'attrs' => array(
							'allow_merge_tags' => true,
							'merge_tags'       => Gutena_Forms_Auto_Responder_Helper::get_from_email_merge_tags(),
							'merge_tag_field'  => true,
						),
					),
					array(
						'id'    => 'cc',
						'type'  => 'email',
						'name'  => __( 'CC', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'cc', $defaults ),
						'attrs' => array(
							'multiple' => true,
						),
					),
					array(
						'id'    => 'bcc',
						'type'  => 'email',
						'name'  => __( 'BCC', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'bcc', $defaults ),
						'attrs' => array(
							'multiple' => true,
						),
					),
					array(
						'id'    => 'reply_to',
						'type'  => 'email',
						'name'  => __( 'Reply To', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'reply_to', $defaults ),
						'attrs' => array(
							'multiple' => true,
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
			$validation = Gutena_Forms_Auto_Responder_Helper::validate_settings( $settings );
			if ( is_wp_error( $validation ) ) {
				return false;
			}

			$sanitized = Gutena_Forms_Auto_Responder_Helper::sanitize_settings( $settings );
			update_option( Gutena_Forms_Auto_Responder_Helper::OPTION_NAME, $sanitized );
			$this->settings = $sanitized;

			return true;
		}
	}

	Gutena_Forms_Auto_Responder::register_module();
endif;
