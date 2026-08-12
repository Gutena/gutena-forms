<?php
/**
 * Auto Responder admin settings module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Auto_Responder' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Global auto-responder settings for confirmation emails.
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
			$this->settings = is_array( $settings ) ? $settings : array();
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
		 * Settings definition for dashboard UI.
		 *
		 * @return array
		 */
		public function get_settings() {
			$defaults   = Gutena_Forms_Auto_Responder_Helper::get_defaults();
			$merge_tags = Gutena_Forms_Auto_Responder_Helper::get_static_merge_tags();

			return array(
				'id'          => 'auto-responder',
				'title'       => __( 'Auto Responder', 'gutena-forms' ),
				'description' => __( 'Enable automated email responses using customizable templates and merge tags.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'enable',
						'type'    => 'toggle',
						'name'    => __( 'Enable Auto-Responder', 'gutena-forms' ),
						'desc'    => __( 'Send an automatic reply to users who submit the form.', 'gutena-forms' ),
						'default' => false,
						'value'   => ! empty( $this->settings['enable'] ),
					),
					array(
						'id'    => 'subject',
						'type'  => 'text',
						'name'  => __( 'Subject', 'gutena-forms' ),
						'value' => empty( $this->settings['subject'] ) ? $defaults['subject'] : $this->settings['subject'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['subject'],
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
						'id'    => 'message',
						'type'  => 'textarea',
						'name'  => __( 'Message', 'gutena-forms' ),
						'value' => empty( $this->settings['message'] ) ? $defaults['message'] : $this->settings['message'],
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['message'],
							'rows'            => 6,
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
			$sanitized = Gutena_Forms_Auto_Responder_Helper::sanitize_settings( $settings );
			update_option( Gutena_Forms_Auto_Responder_Helper::OPTION_NAME, $sanitized );
			$this->settings = $sanitized;

			return true;
		}
	}

	Gutena_Forms_Auto_Responder::register_module();
endif;
