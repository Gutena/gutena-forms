<?php
/**
 * Validation Messages Module
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Validation_Messages' ) || class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Form validation message settings
	 *
	 * @since 1.8.0
	 */
	class Gutena_Forms_Validation_Messages extends Gutena_Forms_Forms_Settings {
		/**
		 * The instance of the class
		 *
		 * @since 1.8.0
		 * @var Gutena_Forms_Validation_Messages $instance The instance of the class.
		 */
		private static $instance;

		/**
		 * The settings for validation messages.
		 *
		 * @since 1.8.0
		 * @var array $settings The settings for validation messages.
		 */
		public $settings = array();

		/**
		 * Validation messages constructor
		 *
		 * @since 1.8.0
		 */
		public function __construct() {
			$this->settings = get_option( 'gutena_forms__form_validation_messages', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}

			$this->run();
		}

		/**
		 * Registering the module
		 *
		 * @since 1.8.0
		 */
		public static function register_module() {

			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['validation-messages'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Getting the instance of the class
		 *
		 * @since 1.8.0
		 * @return Gutena_Forms_Validation_Messages
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Getting settings for the module
		 *
		 * @since 1.8.0
		 * @return array
		 */
		public function get_settings() {
			return array(
				'title'       => __( 'Validation Messages', 'gutena-forms' ),
				'description' => __( 'These messages appear to users in real time as they complete the form.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'    => 'required_msg',
						'type'  => 'text',
						'name'  => __( 'Required Field', 'gutena-forms' ),
						'value' => $this->settings['required_msg'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please fill in the field', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'required_msg_select',
						'type'  => 'text',
						'name'  => __( 'Required Select Field', 'gutena-forms' ),
						'value' => $this->settings['required_msg_select'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please select an option', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'required_msg_check',
						'type'  => 'text',
						'name'  => __( 'Required Checkbox/Radio Field', 'gutena-forms' ),
						'value' => $this->settings['required_msg_check'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please check an option', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'required_msg_optin',
						'type'  => 'text',
						'name'  => __( 'Required Opt-in Field', 'gutena-forms' ),
						'value' => $this->settings['required_msg_optin'] ?? '',
						'desc'  => __( 'Privacy policy, Terms', 'gutena-forms' ),
						'attrs' => array(
							'placeholder' => __( 'Please check this checkbox', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'invalid_email_msg',
						'type'  => 'text',
						'name'  => __( 'Invalid Email', 'gutena-forms' ),
						'value' => $this->settings['invalid_email_msg'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please enter a valid email address', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'min_value_msg',
						'type'  => 'text',
						'name'  => __( 'Minimum Value', 'gutena-forms' ),
						'value' => $this->settings['min_value_msg'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please enter value greater than or equal to (value)', 'gutena-forms' ),
						),
					),
					array(
						'id'    => 'max_value_msg',
						'type'  => 'text',
						'name'  => __( 'Maximum Value', 'gutena-forms' ),
						'value' => $this->settings['max_value_msg'] ?? '',
						'attrs' => array(
							'placeholder' => __( 'Please enter a value less than or equal to (value)', 'gutena-forms' ),
						),
					),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);
		}

		/**
		 * Saving the settings
		 *
		 * @since 1.8.0
		 * @param array $settings Settings to save.
		 *
		 * @return true
		 */
		public function save_settings( $settings ) {
			$previous_global = get_option( 'gutena_forms__form_validation_messages', array() );
			$settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $settings );

			$block_settings                    = $settings;
			$block_settings['defaultSettings'] = true;

			Gutena_Forms_Settings_Migrator::update_primary_form( 'messages', $block_settings );
			update_option( 'gutena_forms__form_validation_messages', $settings );
			Gutena_Forms_Settings_Migrator::sync_global_module_to_forms( 'messages', $previous_global, $settings );
			return true;
		}

		/**
		 * Running the module
		 *
		 * @since 1.8.0
		 */
		private function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_messages_to_default_option' ), 10, 3 );
		}

		/**
		 * Saving the settings global settings.
		 *
		 * @since 1.8.0
		 * @param array   $attributes Block attributes.
		 * @param array   $block bocks.
		 * @param WP_Post $post Current post object.
		 */
		public function save_messages_to_default_option( $attributes, $block, $post ) {
			$messages = isset( $attributes['messages'] ) && is_array( $attributes['messages'] ) ? $attributes['messages'] : array();

			if ( ! Gutena_Forms_Settings_Migrator::is_single_form_site() || empty( $messages ) ) {
				return;
			}

			$this->settings = Gutena_Forms_Settings_Migrator::sanitize_settings_for_option( $messages );
			update_option( 'gutena_forms__form_validation_messages', $this->settings );
		}
	}

	Gutena_Forms_Validation_Messages::register_module();
endif;
