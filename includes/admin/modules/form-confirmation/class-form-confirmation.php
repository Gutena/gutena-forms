<?php
/**
 * Global form confirmation defaults admin settings module.
 *
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Confirmation' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Global form confirmation defaults for newly created forms.
	 */
	class Gutena_Forms_Form_Confirmation extends Gutena_Forms_Forms_Settings {

		/**
		 * Singleton instance.
		 *
		 * @var Gutena_Forms_Form_Confirmation
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
			$settings       = get_option( Gutena_Forms_Confirmation_Helper::OPTION_NAME, array() );
			$this->settings = is_array( $settings ) ? $settings : array();
		}

		/**
		 * Register module.
		 */
		public static function register_module() {
			add_filter(
				'gutena_forms__settings',
				function ( $settings ) {
					$settings['form-confirmation'] = __CLASS__;
					return $settings;
				}
			);

			self::get_instance();
		}

		/**
		 * Get singleton instance.
		 *
		 * @return Gutena_Forms_Form_Confirmation
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
			if ( array_key_exists( $key, $this->settings ) ) {
				return $this->settings[ $key ];
			}

			return $defaults[ $key ] ?? '';
		}

		/**
		 * Published page options for the redirect page dropdown.
		 *
		 * @return array
		 */
		private function get_page_options() {
			$options = array(
				'0' => __( 'Select a page', 'gutena-forms' ),
			);

			$pages = get_pages(
				array(
					'post_status' => 'publish',
					'sort_column' => 'post_title',
					'sort_order'  => 'ASC',
				)
			);

			if ( ! empty( $pages ) && is_array( $pages ) ) {
				foreach ( $pages as $page ) {
					$options[ (string) $page->ID ] = $page->post_title;
				}
			}

			return $options;
		}

		/**
		 * Settings definition for dashboard UI.
		 *
		 * @return array
		 */
		public function get_settings() {
			$defaults   = Gutena_Forms_Confirmation_Helper::get_defaults();
			$merge_tags = Gutena_Forms_Confirmation_Helper::get_static_merge_tags();

			return array(
				'id'          => 'form-confirmation',
				'title'       => __( 'Form Confirmation', 'gutena-forms' ),
				'description' => __( 'Configure default settings that apply to newly created forms.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'    => 'confirmation_type',
						'type'  => 'radio-group',
						'name'  => __( 'Confirmation Type', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'confirmation_type', $defaults ),
						'attrs' => array(
							'variant' => 'card',
							'options' => array(
								'message'  => __( 'Success Message', 'gutena-forms' ),
								'redirect' => __( 'Redirect', 'gutena-forms' ),
							),
						),
					),
					array(
						'id'    => 'success_message',
						'type'  => 'html-editor',
						'name'  => __( 'Confirmation Message', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'success_message', $defaults ),
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['success_message'],
						),
					),
					array(
						'id'    => 'error_message',
						'type'  => 'html-editor',
						'name'  => __( 'Error Message', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'error_message', $defaults ),
						'attrs' => array(
							'merge_tag_field' => true,
							'placeholder'     => $defaults['error_message'],
						),
					),
					array(
						'id'    => 'after_submit',
						'type'  => 'radio-group',
						'name'  => __( 'After Form Submission', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'after_submit', $defaults ),
						'attrs' => array(
							'variant' => 'card',
							'options' => array(
								'hide'  => __( 'Hide Form', 'gutena-forms' ),
								'reset' => __( 'Reset Form', 'gutena-forms' ),
							),
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
						'id'    => 'redirect_type',
						'type'  => 'radio-group',
						'name'  => __( 'Redirect to', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'redirect_type', $defaults ),
						'attrs' => array(
							'variant' => 'card',
							'options' => array(
								'page'       => __( 'Page', 'gutena-forms' ),
								'custom_url' => __( 'Custom URL', 'gutena-forms' ),
							),
						),
					),
					array(
						'id'    => 'redirect_page_id',
						'type'  => 'select',
						'name'  => __( 'Page', 'gutena-forms' ),
						'value' => (string) absint( $this->get_setting_value( 'redirect_page_id', $defaults ) ),
						'attrs' => array(
							'options' => $this->get_page_options(),
						),
					),
					array(
						'id'    => 'redirect_url',
						'type'  => 'url',
						'name'  => __( 'Custom URL', 'gutena-forms' ),
						'value' => $this->get_setting_value( 'redirect_url', $defaults ),
						'attrs' => array(
							'placeholder' => 'https://',
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
			$validation = Gutena_Forms_Confirmation_Helper::validate_settings( $settings );
			if ( is_wp_error( $validation ) ) {
				return false;
			}

			$sanitized      = Gutena_Forms_Confirmation_Helper::sanitize_settings( $settings );
			$this->settings = $sanitized;

			return update_option( Gutena_Forms_Confirmation_Helper::OPTION_NAME, $sanitized );
		}
	}

	Gutena_Forms_Form_Confirmation::register_module();
endif;
