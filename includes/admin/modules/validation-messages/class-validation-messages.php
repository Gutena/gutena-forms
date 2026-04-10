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
						'value'   => $this->settings['required_msg'],
						'attrs'   => array(
							'placeholder' => __(  'Please in the field', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'required_msg_select',
						'type'    => 'text',
						'name'    => __( 'Required Select Field', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_select'],
						'attrs'   => array(
							'placeholder' => __(  'Please select an option', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'required_msg_check',
						'type'    => 'text',
						'name'    => __( 'Required Checkbox/Radio Field', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_check'],
						'attrs'   => array(
							'placeholder' => __(  'Please check an option', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'required_msg_optin',
						'type'    => 'text',
						'name'    => __( 'Required Opt-in Field', 'gutena-forms' ),
						'value'   => $this->settings['required_msg_optin'],
						'attrs'   => array(
							'placeholder' => __( 'Please check this checkbox', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'invalid_email_msg',
						'type'    => 'text',
						'name'    => __( 'Invalid Email', 'gutena-forms' ),
						'value'   => $this->settings['invalid_email_msg'],
						'attrs'   => array(
							'placeholder' => __( 'Please enter a valid email address', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'min_value_msg',
						'type'    => 'text',
						'name'    => __( 'Minimum Value', 'gutena-forms' ),
						'value'   => $this->settings['min_value_msg'],
						'attrs'   => array(
							'placeholder' => __( 'Please enter value greater than or equal to (value)', 'gutena-forms' ),
						),
					),
					array(
						'id'      => 'max_value_msg',
						'type'    => 'text',
						'name'    => __( 'Maximum Value', 'gutena-forms' ),
						'value'   => $this->settings['max_value_msg'],
						'attrs'   => array(
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
