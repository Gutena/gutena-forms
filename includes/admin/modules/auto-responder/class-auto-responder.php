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
			$this->settings = Gutena_Forms_Auto_Responder_Helper::get_settings();
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
			$defaults = Gutena_Forms_Auto_Responder_Helper::get_defaults();

			return array(
				'id'          => 'auto-responder',
				'title'       => __( 'Auto Responder', 'gutena-forms' ),
				'description' => __( 'Enable automated email responses using customizable templates and merge tags.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'type' => 'template',
						'name' => 'auto-responder',
					),
				),
				'values'      => array(
					'enable'  => ! empty( $this->settings['enable'] ),
					'subject' => $this->settings['subject'],
					'message' => $this->settings['message'],
					'merge_tags' => Gutena_Forms_Auto_Responder_Helper::get_static_merge_tags(),
					'default_subject' => $defaults['subject'],
					'default_message' => $defaults['message'],
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
