<?php
/**
 * Form Confirmation admin settings module.
 *
 * @since 2.4.0
 * @package Gutena Forms
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Gutena_Forms_Form_Confirmation' ) && class_exists( 'Gutena_Forms_Forms_Settings' ) ) :
	/**
	 * Global form confirmation defaults settings.
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
			$settings       = get_option( Gutena_Forms_Form_Confirmation_Helper::OPTION_NAME, array() );
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
		 * Get published pages for the redirect dropdown.
		 *
		 * @return array page_id => title map with a leading empty option.
		 */
		private function get_page_options() {
			$options = array(
				'' => __( 'Select Page', 'gutena-forms' ),
			);

			$pages = get_pages(
				array(
					'sort_column' => 'post_title',
					'sort_order'  => 'ASC',
					'post_status' => 'publish',
				)
			);

			if ( ! empty( $pages ) && is_array( $pages ) ) {
				foreach ( $pages as $page ) {
					if ( ! empty( $page->ID ) ) {
						$options[ (string) $page->ID ] = $page->post_title;
					}
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
			$defaults   = Gutena_Forms_Form_Confirmation_Helper::get_global_defaults();
			$merge_tags = Gutena_Forms_Form_Confirmation_Helper::get_static_merge_tags();
			$saved      = wp_parse_args( $this->settings, $defaults );

			return array(
				'id'          => 'form-confirmation',
				'title'       => __( 'Form Confirmation', 'gutena-forms' ),
				'description' => __( 'Configure default settings that apply to newly created forms.', 'gutena-forms' ),
				'fields'      => array(
					array(
						'id'    => 'type',
						'type'  => 'radio-group',
						'name'  => __( 'Confirmation Type', 'gutena-forms' ),
						'value' => $saved['type'],
						'attrs' => array(
							'options' => array(
								'success'  => __( 'Success Message', 'gutena-forms' ),
								'redirect' => __( 'Redirect', 'gutena-forms' ),
							),
						),
					),
					array(
						'id'    => 'successMessage',
						'type'  => 'rich-text',
						'name'  => __( 'Confirmation Message', 'gutena-forms' ),
						'value' => $saved['successMessage'],
						'attrs' => array(
							'merge_tags'      => $merge_tags,
							'visible_when'    => array(
								array( 'field' => 'type', 'equals' => 'success' ),
							),
						),
					),
					array(
						'id'    => 'errorMessage',
						'type'  => 'rich-text',
						'name'  => __( 'Error Message', 'gutena-forms' ),
						'value' => $saved['errorMessage'],
						'attrs' => array(
							'merge_tags'      => $merge_tags,
							'visible_when'    => array(
								array( 'field' => 'type', 'equals' => 'success' ),
							),
						),
					),
					array(
						'id'    => 'afterSubmit',
						'type'  => 'radio-group',
						'name'  => __( 'After Form Submission', 'gutena-forms' ),
						'value' => $saved['afterSubmit'],
						'attrs' => array(
							'options' => array(
								'hide'  => __( 'Hide Form', 'gutena-forms' ),
								'reset' => __( 'Reset Form', 'gutena-forms' ),
							),
							'visible_when' => array(
								array( 'field' => 'type', 'equals' => 'success' ),
							),
						),
					),
					array(
						'id'    => 'redirectType',
						'type'  => 'radio-group',
						'name'  => __( 'Redirect To', 'gutena-forms' ),
						'value' => $saved['redirectType'],
						'attrs' => array(
							'options' => array(
								'page' => __( 'Page', 'gutena-forms' ),
								'url'  => __( 'Custom URL', 'gutena-forms' ),
							),
							'visible_when' => array(
								array( 'field' => 'type', 'equals' => 'redirect' ),
							),
						),
					),
					array(
						'id'    => 'redirectPage',
						'type'  => 'select',
						'name'  => __( 'Redirect to Page', 'gutena-forms' ),
						'value' => (string) $saved['redirectPage'],
						'attrs' => array(
							'options' => $this->get_page_options(),
							'visible_when' => array(
								array( 'field' => 'type', 'equals' => 'redirect' ),
								array( 'field' => 'redirectType', 'equals' => 'page' ),
							),
						),
					),
					array(
						'id'    => 'redirectUrl',
						'type'  => 'text',
						'name'  => __( 'Redirect to Custom URL', 'gutena-forms' ),
						'value' => $saved['redirectUrl'],
						'attrs' => array(
							'placeholder' => __( 'https://example.com/thank-you', 'gutena-forms' ),
							'visible_when' => array(
								array( 'field' => 'type', 'equals' => 'redirect' ),
								array( 'field' => 'redirectType', 'equals' => 'url' ),
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
		 * Global settings are stored once and inherited live by forms that use
		 * defaults (defaultSettings flag). Existing forms are intentionally
		 * NOT overwritten when global settings change.
		 *
		 * @param array $settings Settings payload.
		 * @return bool
		 */
		public function save_settings( $settings ) {
			$sanitized = Gutena_Forms_Form_Confirmation_Helper::sanitize_global_settings( $settings );

			// Save global option.
			update_option( Gutena_Forms_Form_Confirmation_Helper::OPTION_NAME, $sanitized );
			$this->settings = $sanitized;

			return true;
		}
	}

	Gutena_Forms_Form_Confirmation::register_module();
endif;
