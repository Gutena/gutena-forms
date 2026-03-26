<?php
/**
 * Validation Messages Module
 *
 * @since 1.8.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Validation_Messages' ) || class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	class Gutena_Forms_Validation_Messages extends Gutena_Forms_Forms_Settings {
		private static $instance;

		public $settings = array();

		public function __construct() {
			$this->settings = get_option( 'gutena_forms__form_validation_messages', array() );
			if ( ! is_array( $this->settings ) ) {
				$this->settings = array();
			}

			$this->run();
		}

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

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function get_settings() {
			return array(
				'title'	 	  => __( 'Validation Messages', 'gutena-forms' ),
				'description' => __( 'These messages appear to users in real time as they complete the form.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'      => 'required_msg',
						'type'    => 'text',
						'name'    => __( 'Required Field', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a required field is left empty.', 'gutena-forms' ),
						'default' => __( 'This field is required.', 'gutena-forms' ),
						'value'   => $this->settings['required_msg'],
					),
					array(
						'id'      => 'required_msg_select',
						'type'    => 'text',
						'name'    => __( 'Required Select Field', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a required select field is left unselected.', 'gutena-forms' ),
						'default' => __( 'Please select an option.', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_select'],
					),
					array(
						'id'      => 'required_msg_check',
						'type'    => 'text',
						'name'    => __( 'Required Checkbox/Radio Field', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a required checkbox or radio field is left unchecked.', 'gutena-forms' ),
						'default' => __( 'Please check this field.', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_check'],
					),
					array(
						'id'      => 'required_msg_optin',
						'type'    => 'text',
						'name'    => __( 'Required Opt-in Field', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a required opt-in field is left unchecked. (privacy policy, Terms)', 'gutena-forms' ),
						'default' => __( 'Please check this box to opt in.', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_optin'],
					),
					array(
						'id'      => 'invalid_email_msg',
						'type'    => 'text',
						'name'    => __( 'Invalid Email', 'gutena-forms' ),
						'desc'    => __( 'Message shown when an email field contains an invalid email address.', 'gutena-forms' ),
						'default' => __( 'Please enter a valid email address.', 'gutena-forms' ),
						'value'   => $this->settings['invalid_email_msg'],
					),
					array(
						'id'      => 'min_value_msg',
						'type'    => 'text',
						'name'    => __( 'Minimum Value', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a number field contains a value below the minimum allowed.', 'gutena-forms' ),
						'default' => __( 'Please enter a value greater than or equal to {min}.', 'gutena-forms' ),
						'value'   => $this->settings['min_value_msg'],
					),
					array(
						'id'      => 'max_value_msg',
						'type'    => 'text',
						'name'    => __( 'Maximum Value', 'gutena-forms' ),
						'desc'    => __( 'Message shown when a number field contains a value above the maximum allowed.', 'gutena-forms' ),
						'default' => __( 'Please enter a value less than or equal to {max}.', 'gutena-forms' ),
						'value'   => $this->settings['max_value_msg'],
					),
					array(
						'id'   => 'submit_button',
						'type' => 'submit',
						'name' => __( 'Save Settings', 'gutena-forms' ),
					),
				),
			);
		}

		public function save_settings( $settings ) {

			update_option( 'gutena_forms__form_validation_messages', $settings );
			 return true;
		}

		private function run() {
			add_action( 'gutena_forms__saving_block', array( $this, 'save_messages_to_default_option' ), 10, 3 );
		}

		/**
		 * @param array   $attributes
		 * @param array   $block
		 * @param WP_Post $post
		 */
		public function save_messages_to_default_option( $attributes, $block, $post ) {
			$all_form_ids = get_option( 'gutena_form_ids', array() );
			$current_form_id = $attributes['formID'];

			// Only sync if this is the Primary Form (only one form exists)
			if ( empty( $all_form_ids ) || ( count( $all_form_ids ) === 1 && in_array( $current_form_id, $all_form_ids ) ) ) {
				$messages = $attributes['messages'];
				foreach ( $messages as $mk => $mv ) {
					$this->settings[ $mk ] = $mv;
				}

				$this->save_settings( $this->settings );
			}
		}
	}

	Gutena_Forms_Validation_Messages::register_module();
endif;
